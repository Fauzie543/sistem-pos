<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $categories = Category::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                // tampilkan kategori global (tanpa outlet_id) dan milik outlet aktif
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            });

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('action', function ($category) {
                $deleteUrl = route('categories.destroy', $category->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $category->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ ambil outlet aktif

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->where('outlet_id', $outletId)->orWhereNull('outlet_id');
                    }),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $validated['company_id'] = $companyId;
        $validated['outlet_id'] = $outletId; // ✅ simpan outlet aktif

        Category::create($validated);

        return response()->json(['success' => 'Kategori berhasil ditambahkan.']);
    }

    public function edit(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        // 🔒 Pastikan kategori milik outlet aktif
        if ($category->outlet_id !== $outletId && $category->outlet_id !== null) {
            return response()->json(['error' => 'Tidak dapat mengubah kategori dari outlet lain.'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->where('outlet_id', $outletId)->orWhereNull('outlet_id');
                    })
                    ->ignore($category->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);

        return response()->json(['success' => 'Kategori berhasil diperbarui.']);
    }

    public function destroy(Category $category)
    {
        // 🔒 Jangan hapus kategori dari outlet lain
        $outletId = config('app.active_outlet_id');
        if ($category->outlet_id !== $outletId && $category->outlet_id !== null) {
            return response()->json(['error' => 'Tidak dapat menghapus kategori dari outlet lain.'], 403);
        }

        if ($category->products()->exists() || $category->services()->exists()) {
            return response()->json(['error' => 'Kategori masih digunakan oleh produk atau layanan.'], 403);
        }

        $category->delete();
        return response()->json(['success' => 'Kategori berhasil dihapus.']);
    }
}