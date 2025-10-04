<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index()
    {
        // Ambil data kategori untuk dropdown di modal
        $categories = Category::orderBy('name')->get();
        return view('services.index', compact('categories'));
    }

    public function data()
    {
        // Ambil data jasa dengan relasi kategori
        $services = Service::with('category')->select('services.*');

        return DataTables::of($services)
            ->addIndexColumn()
            ->editColumn('category.name', fn($s) => $s->category->name ?? '-')
            ->editColumn('price', fn($s) => 'Rp ' . number_format($s->price, 0, ',', '.'))
            ->addColumn('action', function ($service) {
                $deleteUrl = route('services.destroy', $service->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $service->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateService($request);
        Service::create($validated);
        return response()->json(['success' => 'Service created successfully.']);
    }

    public function edit(Service $service)
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $this->validateService($request, $service->id);
        $service->update($validated);
        return response()->json(['success' => 'Service updated successfully.']);
    }

    public function destroy(Service $service)
    {
        // Nanti bisa dicek apakah jasa terikat dengan transaksi penjualan
        $service->delete();
        return response()->json(['success' => 'Service has been deleted successfully.']);
    }

    // Helper validasi
    private function validateService(Request $request, $serviceId = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('services')->ignore($serviceId)],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
        ];

        return $request->validate($rules);
    }
}