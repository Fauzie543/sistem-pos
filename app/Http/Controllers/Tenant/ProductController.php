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
        $outletId = config('app.active_outlet_id');

        $products = Product::with(['category','promos' => fn($q)=>$q->active()])
            ->where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->select('products.*');

        return DataTables::of($products)
            ->addIndexColumn()
            ->editColumn('category.name', fn($p) => $p->category->name ?? '-')
            ->editColumn('selling_price', function($p) {
                $price = $p->selling_price;
                $promo = $p->promos->first();

                if ($promo) {
                    $discount = $promo->type === 'percent'
                        ? $price * ($promo->value / 100)
                        : $promo->value;
                    $price -= $discount;
                    return '<span class="line-through text-gray-400">Rp '.number_format($p->selling_price,0,',','.').'</span><br><span class="text-green-600 font-semibold">Rp '.number_format($price,0,',','.').'</span>';
                }

                return 'Rp '.number_format($price,0,',','.');
            })
            ->addColumn('action', function ($product) {
                $deleteUrl = route('products.destroy', $product->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $product->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action','selling_price'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);
        $validated['company_id'] = auth()->user()->company_id;
        $validated['outlet_id'] = config('app.active_outlet_id');
        Product::create($validated);
        return response()->json(['success' => 'Product created successfully.']);
    }

    public function edit(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // outlet aktif dari middleware

        // Validasi data (pastikan SKU unik dalam satu company + outlet)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
                    }),
            ],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'sku' => [
                'nullable', 'string', 'max:50',
                Rule::unique('products')
                    ->where('company_id', $companyId)
                    ->where('outlet_id', $outletId)
                    ->ignore($product->id),
            ],
            'unit' => ['required', 'string', 'max:20'],
            'storage_location' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        // Pastikan produk yang diupdate milik outlet aktif
        if ($product->outlet_id !== $outletId) {
            return response()->json(['error' => 'Tidak dapat mengubah produk dari outlet lain.'], 403);
        }

        $product->update($validated);

        return response()->json(['success' => 'Produk berhasil diperbarui.']);
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