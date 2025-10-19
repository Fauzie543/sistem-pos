<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Category;
use App\Models\Product;
use App\Models\Company;
use App\Models\Service;
use App\Models\Sale;
use App\Models\SaleDetail;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    private function setupTenantMidtransConfig(): bool
    {
        $company = auth()->user()->company;

        // Cek apakah perusahaan memiliki konfigurasi Midtrans yang valid
        if (
            !$company ||
            $company->payment_gateway_provider !== 'midtrans' ||
            empty($company->payment_gateway_keys['server_key'])
        ) {
            return false; // Konfigurasi tidak ditemukan atau tidak lengkap
        }

        // Terapkan konfigurasi Midtrans milik tenant
        Config::$serverKey = $company->payment_gateway_keys['server_key'];
        Config::$isProduction = $company->payment_gateway_is_production;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        return true; // Konfigurasi berhasil diterapkan
    }
    
    public function index()
    {
        // Ambil semua kategori yang memiliki produk atau jasa
        $categories = Category::where(function ($query) {
            $query->whereHas('products')->orWhereHas('services');
        })->orderBy('name')->get();

        // Ambil semua produk dan jasa untuk ditampilkan di awal
        $products = Product::where('stock', '>', 0)->orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        // Gabungkan produk dan jasa ke dalam satu koleksi
        $items = $products->map(function ($p) {
            return (object) ['id' => $p->id, 'name' => $p->name, 'price' => $p->selling_price, 'type' => 'product', 'category_id' => $p->category_id];
        })->concat($services->map(function ($s) {
            return (object) ['id' => $s->id, 'name' => $s->name, 'price' => $s->price, 'type' => 'service', 'category_id' => $s->category_id];
        }));

        return view('sales.pos', compact('categories', 'items'));
    }

    // === PROSES PENYIMPANAN TRANSAKSI PENJUALAN ===
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $request->validate([
            // PERBAIKAN: Pastikan customer_id ada di dalam company ini
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            // PERBAIKAN: Jika vehicle_id ada, pastikan juga ada di dalam company ini
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('company_id', $companyId)],
            'payment_method' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.type' => ['required', 'in:product,service'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Validasi Stok Produk sebelum melanjutkan
            foreach ($request->items as $item) {
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    if ($product->stock < $item['quantity']) {
                        // Batalkan transaksi jika stok tidak cukup
                        return response()->json(['error' => 'Stock for product ' . $product->name . ' is not sufficient.'], 422);
                    }
                }
            }
            
            // 2. Hitung Total Belanja
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // 3. Buat record utama di tabel 'sales'
            $sale = Sale::create([
                'invoice_number' => $request->invoice_number ?? 'INV-' . time(),
                'customer_id' => $request->customer_id,
                'vehicle_id' => $request->vehicle_id,
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'lunas',
                'company_id' => $companyId, // <-- PENTING
            ]);

            // 4. Loop dan simpan detail, lalu kurangi stok
            foreach ($request->items as $item) {
                $sale->details()->create([
                    'product_id' => ($item['type'] === 'product') ? $item['id'] : null,
                    'service_id' => ($item['type'] === 'service') ? $item['id'] : null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                    'company_id' => $companyId,
                ]);

                // 5. Kurangi stok HANYA jika item adalah produk
                if ($item['type'] === 'product') {
                    Product::find($item['id'])->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => 'Sale recorded successfully.',
                'sale_id' => $sale->id // Kirim ID penjualan untuk cetak struk/melihat detail
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    // === RIWAYAT PENJUALAN ===
    public function historyIndex()
    {
        return view('sales.index');
    }

    public function historyData()
    {
        $sales = Sale::with(['customer', 'user'])->select('sales.*');
        return DataTables::of($sales)
            ->addIndexColumn()
            ->editColumn('created_at', fn($s) => $s->created_at->format('d F Y H:i'))
            ->editColumn('total_amount', fn($s) => 'Rp ' . number_format($s->total_amount, 0, ',', '.'))
            ->addColumn('action', fn($s) => '<a href="'.route('sales.history.show', $s->id).'" class="bg-blue-500 text-white font-bold py-1 px-2 rounded text-xs">View Details</a>')
            ->rawColumns(['action'])
            ->make(true);
    }
    
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'vehicle', 'details.product', 'details.service']);
        return view('sales.show', compact('sale'));
    }

    // === ENDPOINT AJAX UNTUK PENCARIAN ===
    public function searchCustomers(Request $request)
    {
        $term = $request->input('term');
        
        $customers = Customer::with('vehicles')
            ->where('name', 'LIKE', "%{$term}%")
            ->orWhere('phone_number', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get();
        
        // Langsung kembalikan hasil pencarian dari database
        return response()->json($customers);
    }

    public function searchItems(Request $request)
    {
        $term = $request->input('term');
        $products = Product::where('name', 'LIKE', "%{$term}%")
                            ->orWhere('sku', 'LIKE', "%{$term}%")
                            ->where('stock', '>', 0)
                            ->limit(5)->get()->map(fn($p) => [
                                'id' => $p->id, 
                                'name' => "[P] {$p->name}", 
                                'price' => (float) $p->selling_price, // Pastikan ini adalah angka
                                'type' => 'product'
                            ]);

        $services = Service::where('name', 'LIKE', "%{$term}%")
                            ->limit(5)->get()->map(fn($s) => [
                                'id' => $s->id, 
                                'name' => "[J] {$s->name}", 
                                'price' => (int) $s->price, // Pastikan ini adalah angka
                                'type' => 'service'
                            ]);
        
        return response()->json($products->concat($services));
    }

    
    public function generateQris(Request $request)
    {
        $request->validate(['amount' => ['required', 'numeric', 'min:1']]);

        // 1. Panggil helper untuk mengatur konfigurasi Midtrans
        if (!$this->setupTenantMidtransConfig()) {
            return response()->json(['error' => 'Konfigurasi pembayaran QRIS tidak ditemukan atau tidak lengkap.'], 500);
        }

        // 2. Buat parameter untuk dikirim ke Midtrans
        $orderId = 'INV-' . auth()->user()->company_id . '-' . time();
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
        ];

        try {
            // 3. Panggil API Midtrans
            $response = CoreApi::charge($params);

            // 4. Kirim kembali data yang dibutuhkan ke frontend
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
        // 1. Panggil helper untuk mengatur konfigurasi Midtrans
        if (!$this->setupTenantMidtransConfig()) {
            return response()->json(['error' => 'Konfigurasi pembayaran tidak ditemukan.'], 500);
        }

        try {
            // 2. Panggil API Midtrans untuk mendapatkan status
            $status = Transaction::status($orderId);

            return response()->json(['transaction_status' => $status->transaction_status]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Transaksi tidak ditemukan atau terjadi kesalahan.'], 404);
        }
    }

    public function showReceipt(Sale $sale)
    {
        $sale->load(['user', 'details.product', 'details.service']);
        return view('sales.receipt', compact('sale'));
    }
}