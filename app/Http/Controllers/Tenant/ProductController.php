<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $categories = Category::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('products.index', compact('categories'));
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;

        $products = Product::with('category')
            ->where('company_id', $companyId)
            ->select('products.*');

        return DataTables::of($products)
            ->addIndexColumn()
            ->editColumn('category.name', fn($p) => $p->category->name ?? '-')
            ->editColumn('selling_price', fn($p) => 'Rp ' . number_format($p->selling_price, 0, ',', '.'))
            ->addColumn('action', function ($product) {
                $deleteUrl = route('products.destroy', $product->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $product->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $validated['company_id'] = auth()->user()->company_id;
        Product::create($validated);
        return response()->json(['success' => 'Product created successfully.']);
    }

    public function edit(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);
        $product->update($validated);
        return response()->json(['success' => 'Product updated successfully.']);
    }

    public function destroy(Product $product)
    {
        // Nanti bisa ditambahkan pengecekan apakah produk terikat dengan transaksi
        $product->delete();
        return response()->json(['success' => 'Product has been deleted successfully.']);
    }

    // Fungsi helper untuk validasi agar tidak duplikat kode
    private function validateProduct(Request $request, $productId = null)
    {
        $companyId = auth()->user()->company_id;
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // PERBAIKAN: Pastikan category_id milik company yang sama
            'category_id' => ['required', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            // PERBAIKAN: Pastikan SKU unik hanya di dalam company yang sama
            'sku' => ['nullable', 'string', 'max:50', Rule::unique('products')->where('company_id', $companyId)->ignore($productId)],
            'unit' => ['required', 'string', 'max:20'],
            'storage_location' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ];

        return $request->validate($rules);
    }
}