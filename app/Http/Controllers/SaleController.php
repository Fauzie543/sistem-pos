<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
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
    public function index()
    {
        return view('sales.pos');
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
            
        // Logika untuk menambahkan opsi "Tambah Baru"
        // Opsi ini hanya muncul jika tidak ada hasil yang sama persis
        $exactMatch = $customers->first(function ($customer) use ($term) {
            return strtolower($customer->name) === strtolower($term);
        });

        if (!$exactMatch && !empty($term)) {
            $customers->prepend((object)[
                'id' => 'new:' . $term, // Kirim ID khusus untuk menandakan user baru
                'name' => '➕ Tambah Pelanggan Baru: "' . $term . '"',
                'phone_number' => null,
                'vehicles' => [],
            ]);
        }
        
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
        // Validasi input
        $request->validate(['amount' => ['required', 'numeric', 'min:1']]);

        // 1. Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION'));
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // 2. Buat parameter untuk dikirim ke Midtrans
        $orderId = 'BENGKEL-' . uniqid(); // Buat ID Order yang unik
        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
        ];

        try {
            // 3. Panggil API Midtrans untuk membuat transaksi
            $response = CoreApi::charge($params);

            // 4. Kirim kembali data yang dibutuhkan ke frontend
            return response()->json([
                'order_id' => $orderId,
                'qr_code_url' => $response->actions[0]->url, // URL gambar QR code
                'expiry_time' => $response->expiry_time,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkQrisStatus($orderId)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        Config::$isProduction = config('services.midtrans.is_production', env('MIDTRANS_IS_PRODUCTION'));

        try {
            // Panggil API Midtrans untuk mendapatkan status
            $status = Transaction::status($orderId);

            // Kirim status kembali ke frontend
            return response()->json(['transaction_status' => $status->transaction_status]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Transaction not found or error.'], 404);
        }
    }

    public function showReceipt(Sale $sale)
    {
        $sale->load(['user', 'details.product', 'details.service']);
        return view('sales.receipt', compact('sale'));
    }
}