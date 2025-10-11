<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule; // <-- PENTING: Import Rule

class PurchaseController extends Controller
{
    /**
     * Menampilkan halaman daftar transaksi pembelian.
     * (Tidak ada perubahan, view akan memanggil 'data()')
     */
    public function index()
    {
        return view('purchases.index');
    }

    /**
     * Menyediakan data untuk DataTables.
     * (Tidak perlu diubah, Global Scope akan otomatis memfilter data)
     */
    public function data()
    {
        // Global scope dari spatie/laravel-multitenancy akan otomatis menambahkan ->where('company_id', ...)
        $purchases = Purchase::with(['supplier', 'user'])->select('purchases.*');

        return DataTables::of($purchases)
            ->addIndexColumn()
            ->editColumn('purchase_date', fn($p) => \Carbon\Carbon::parse($p->purchase_date)->format('d F Y'))
            ->editColumn('total_amount', fn($p) => 'Rp ' . number_format($p->total_amount, 0, ',', '.'))
            ->addColumn('action', function ($purchase) {
                // Route model binding juga otomatis aman karena Global Scope
                return '<a href="'.route('purchases.show', $purchase->id).'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">View Details</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Menampilkan form untuk membuat transaksi pembelian baru.
     * (Tidak perlu diubah, Global Scope akan otomatis memfilter supplier)
     */
    public function create()
    {
        // Global scope akan memastikan hanya supplier dari company ini yang ditampilkan
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchases.create', compact('suppliers'));
    }

    /**
     * Menyimpan transaksi pembelian baru ke database.
     * (PERUBAHAN PENTING DI SINI)
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $request->validate([
            // Validasi supplier_id hanya di dalam company yang sama (untuk keamanan)
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')->where('company_id', $companyId)],
            'purchase_date' => ['required', 'date'],
            'products' => ['required', 'array', 'min:1'],
            // Validasi product_id hanya di dalam company yang sama
            'products.*.id' => ['required', Rule::exists('products', 'id')->where('company_id', $companyId)],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->products as $product) {
                $totalAmount += $product['quantity'] * $product['price'];
            }
            
            // 1. Buat record utama dan tambahkan company_id
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'status' => 'diterima',
                'company_id' => $companyId, // <-- TAMBAHKAN INI
            ]);

            // 2. Loop dan simpan detail produk, lalu update stok
            foreach ($request->products as $productData) {
                // PurchaseDetail tidak perlu company_id karena sudah terhubung ke Purchase
                $purchase->details()->create([
                    'product_id' => $productData['id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'subtotal' => $productData['quantity'] * $productData['price'],
                ]);

                // Update stok di tabel 'products'
                // Global Scope memastikan kita mengupdate produk yang benar di company ini
                $product = Product::find($productData['id']);
                if ($product) {
                    $product->increment('stock', $productData['quantity']);
                }
            }
            
            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully and stock has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create purchase. Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Menampilkan detail dari satu transaksi pembelian.
     * (Tidak ada perubahan, Route Model Binding sudah aman berkat Global Scope)
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'details.product']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Endpoint AJAX untuk mencari produk di form create.
     * (Tidak ada perubahan, Global Scope akan otomatis memfilter produk)
     */
    public function searchProducts(Request $request)
    {
        // Global scope akan otomatis menambahkan ->where('company_id', ...)
        $term = $request->input('term');
        $products = Product::where('name', 'LIKE', "%{$term}%")
                            ->orWhere('sku', 'LIKE', "%{$term}%")
                            ->limit(10)
                            ->get(['id', 'name', 'sku', 'purchase_price']);

        return response()->json($products);
    }
}