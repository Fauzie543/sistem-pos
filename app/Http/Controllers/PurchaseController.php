<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    /**
     * Menampilkan halaman daftar transaksi pembelian.
     */
    public function index()
    {
        return view('purchases.index');
    }

    /**
     * Menyediakan data untuk DataTables di halaman index.
     */
    public function data()
    {
        $purchases = Purchase::with(['supplier', 'user'])->select('purchases.*');

        return DataTables::of($purchases)
            ->addIndexColumn()
            ->editColumn('purchase_date', fn($p) => \Carbon\Carbon::parse($p->purchase_date)->format('d F Y'))
            ->editColumn('total_amount', fn($p) => 'Rp ' . number_format($p->total_amount, 0, ',', '.'))
            ->addColumn('action', function ($purchase) {
                return '<a href="'.route('purchases.show', $purchase->id).'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs">View Details</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Menampilkan form untuk membuat transaksi pembelian baru.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchases.create', compact('suppliers'));
    }

    /**
     * Menyimpan transaksi pembelian baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            foreach ($request->products as $product) {
                $totalAmount += $product['quantity'] * $product['price'];
            }
            
            // 1. Buat record utama di tabel 'purchases'
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'invoice_number' => $request->invoice_number,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'status' => 'diterima',
            ]);

            // 2. Loop dan simpan detail produk, lalu update stok
            foreach ($request->products as $productData) {
                // Simpan ke 'purchase_details'
                $purchase->details()->create([
                    'product_id' => $productData['id'],
                    'quantity' => $productData['quantity'],
                    'price' => $productData['price'],
                    'subtotal' => $productData['quantity'] * $productData['price'],
                ]);

                // Update stok di tabel 'products'
                $product = Product::find($productData['id']);
                $product->increment('stock', $productData['quantity']);
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
     */
    public function show(Purchase $purchase)
    {
        // Load relasi agar efisien
        $purchase->load(['supplier', 'user', 'details.product']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Endpoint AJAX untuk mencari produk di form create.
     */
    public function searchProducts(Request $request)
    {
        $term = $request->input('term');
        $products = Product::where('name', 'LIKE', "%{$term}%")
                           ->orWhere('sku', 'LIKE', "%{$term}%")
                           ->limit(10)
                           ->get(['id', 'name', 'sku', 'purchase_price']);

        return response()->json($products);
    }
}