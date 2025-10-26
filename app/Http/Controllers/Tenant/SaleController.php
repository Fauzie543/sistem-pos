<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\SalesExport;
use Maatwebsite\Excel\Facades\Excel;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;

class SaleController extends Controller
{
    private function setupTenantMidtransConfig(): bool
    {
        $company = auth()->user()->company;
        if (!$company || $company->payment_gateway_provider !== 'midtrans' || empty($company->payment_gateway_keys['server_key'])) {
            return false;
        }

        Config::$serverKey = $company->payment_gateway_keys['server_key'];
        Config::$isProduction = $company->payment_gateway_is_production;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        return true;
    }

    // === HALAMAN POS ===
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        // Ambil kategori yang punya produk/jasa dalam outlet ini
        $categories = Category::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
            ->where(function ($query) {
                $query->whereHas('products')->orWhereHas('services');
            })
            ->orderBy('name')->get();

        // Produk hanya dari outlet aktif
        $products = Product::where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        // Jasa bisa global (tanpa outlet_id) atau milik outlet ini
        $services = Service::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
            ->orderBy('name')->get();

        $items = $products->map(function ($p) {
            return (object) [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->selling_price,
                'type' => 'product',
                'category_id' => $p->category_id
            ];
        })->concat(
            $services->map(function ($s) {
                return (object) [
                    'id' => $s->id,
                    'name' => $s->name,
                    'price' => $s->price,
                    'type' => 'service',
                    'category_id' => $s->category_id
                ];
            })
        );

        return view('sales.pos', compact('categories', 'items'));
    }

    // === PROSES PENYIMPANAN TRANSAKSI PENJUALAN ===
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $request->validate([
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('company_id', $companyId)],
            'payment_method' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.type' => ['required', 'in:product,service'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            // 1️⃣ Cek stok produk di outlet aktif
            foreach ($request->items as $item) {
                if ($item['type'] === 'product') {
                    $product = Product::where('company_id', $companyId)
                        ->where('outlet_id', $outletId)
                        ->find($item['id']);
                    if (!$product || $product->stock < $item['quantity']) {
                        return response()->json(['error' => "Stok produk {$product->name} tidak cukup."], 422);
                    }
                }
            }

            // 2️⃣ Hitung total belanja
            $totalAmount = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price']);

            // 3️⃣ Simpan transaksi utama
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number ?? 'INV-' . time(),
                'customer_id' => $request->customer_id,
                'vehicle_id' => $request->vehicle_id,
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'lunas',
                'company_id' => $companyId,
                'outlet_id' => $outletId, // ✅ outlet aktif
            ]);

            // 4️⃣ Simpan detail transaksi
            foreach ($request->items as $item) {
                $sale->details()->create([
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                    'company_id' => $companyId,
                    'outlet_id' => $outletId, // ✅ outlet aktif
                ]);

                // 5️⃣ Kurangi stok produk
                if ($item['type'] === 'product') {
                    Product::where('company_id', $companyId)
                        ->where('outlet_id', $outletId)
                        ->find($item['id'])
                        ?->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Penjualan berhasil disimpan.',
                'sale_id' => $sale->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // === RIWAYAT PENJUALAN ===
    public function historyIndex()
    {
        return view('sales.index');
    }

    public function historyData()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');

        $sales = Sale::with(['customer:id,name', 'user:id,name'])
            ->where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->select('sales.*');

        return DataTables::of($sales)
            ->addIndexColumn()
            ->editColumn('created_at', fn($s) => $s->created_at->format('d F Y H:i'))
            ->editColumn('customer.name', fn($s) => $s->customer->name ?? '-')
            ->editColumn('user.name', fn($s) => $s->user->name ?? '-')
            ->editColumn('total_amount', fn($s) => 'Rp ' . number_format($s->total_amount, 0, ',', '.'))
            ->addColumn('action', fn($s) =>
                '<a href="' . route('sales.history.show', $s->id) . '" 
                class="bg-blue-500 text-white font-bold py-1 px-2 rounded text-xs">Detail</a>'
            )
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show(Sale $sale)
    {
        $outletId = config('app.active_outlet_id');
        if ($sale->outlet_id !== $outletId) {
            abort(403, 'Tidak dapat melihat transaksi dari outlet lain.');
        }

        $sale->load(['customer', 'user', 'vehicle', 'details.product', 'details.service']);
        $company = auth()->user()->company;
        return view('sales.show', compact('sale', 'company'));
    }

    public function print(Sale $sale)
    {
        $outletId = config('app.active_outlet_id');
        if ($sale->outlet_id !== $outletId) {
            abort(403, 'Tidak dapat mencetak transaksi dari outlet lain.');
        }

        $sale->load(['customer', 'user', 'details.product', 'details.service']);
        $company = auth()->user()->company;
        return view('sales.print', compact('sale', 'company'));
    }

    // === PENCARIAN ===
    public function searchCustomers(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $term = $request->input('term');

        $customers = Customer::where('company_id', $companyId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('phone_number', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->with('vehicles')
            ->get();

        return response()->json($customers);
    }

    public function searchItems(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');
        $term = $request->input('term');

        $products = Product::where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('sku', 'LIKE', "%{$term}%");
            })
            ->where('stock', '>', 0)
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => "[P] {$p->name}",
                'price' => (float) $p->selling_price,
                'type' => 'product',
            ]);

        $services = Service::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
            ->where('name', 'LIKE', "%{$term}%")
            ->limit(5)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => "[J] {$s->name}",
                'price' => (float) $s->price,
                'type' => 'service',
            ]);

        return response()->json($products->concat($services));
    }

    // === QRIS ===
    public function generateQris(Request $request)
    {
        $request->validate(['amount' => ['required', 'numeric', 'min:1']]);

        if (!$this->setupTenantMidtransConfig()) {
            return response()->json(['error' => 'Konfigurasi QRIS tidak ditemukan.'], 500);
        }

        $orderId = 'INV-' . auth()->user()->company_id . '-' . time();
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
        ];

        try {
            $response = CoreApi::charge($params);

            return response()->json([
                'order_id' => $orderId,
                'qr_code_url' => $response->actions[0]->url,
                'expiry_time' => $response->expiry_time,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkQrisStatus($orderId)
    {
        if (!$this->setupTenantMidtransConfig()) {
            return response()->json(['error' => 'Konfigurasi pembayaran tidak ditemukan.'], 500);
        }

        try {
            $status = Transaction::status($orderId);
            return response()->json(['transaction_status' => $status->transaction_status]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Transaksi tidak ditemukan.'], 404);
        }
    }

    public function showReceipt(Sale $sale)
    {
        $outletId = config('app.active_outlet_id');
        if ($sale->outlet_id !== $outletId) {
            abort(403, 'Tidak dapat melihat struk dari outlet lain.');
        }

        $sale->load(['user', 'details.product', 'details.service']);
        return view('sales.receipt', compact('sale'));
    }

    // === EXPORT ===
    public function exportExcel(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        $query = Sale::with(['customer', 'user'])
            ->where('company_id', $companyId)
            ->where('outlet_id', $outletId);

        if ($bulan) $query->whereMonth('created_at', $bulan);
        if ($tahun) $query->whereYear('created_at', $tahun);

        $sales = $query->get();
        return Excel::download(new SalesExport($sales), 'sales-export.xlsx');
    }
}