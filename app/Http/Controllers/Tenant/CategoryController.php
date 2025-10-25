<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('categories.index');
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;

        $categories = Category::where('company_id', $companyId);

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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where('company_id', $companyId)],
            'description' => ['nullable', 'string'],
        ]);
        $validated['company_id'] = $companyId;

        Category::create($validated);
        return response()->json(['success' => 'Category created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $companyId = auth()->user()->company_id;

        $validated = $request->validate([
            // PERBAIKAN: Rule 'unique' sekarang hanya berlaku di dalam company yang sama
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where('company_id', $companyId)->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($validated);
        return response()->json(['success' => 'Category updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Pencegahan: jangan hapus kategori jika masih digunakan oleh produk atau jasa.
        if ($category->products()->exists() || $category->services()->exists()) {
            return response()->json(['error' => 'Cannot delete category with associated products or services.'], 403);
        }

        $category->delete(); // Ini akan melakukan soft delete
        return response()->json(['success' => 'Category has been deleted successfully.']);
    }
}