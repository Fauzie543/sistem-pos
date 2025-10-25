<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;

        $categories = Category::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('services.index', compact('categories'));
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;

        $services = Service::with('category')
            ->where('company_id', $companyId)
            ->select('services.*');

        return DataTables::of($services)
            ->addIndexColumn()
            ->editColumn('category.name', fn($s) => $s->category->name ?? '-')
            ->editColumn('price', fn($s) => 'Rp ' . number_format($s->price, 0, ',', '.'))
            ->addColumn('action', function ($service) {
                $deleteUrl = route('services.destroy', $service->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $service->id . '" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" 
                       class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateService($request);
        $validated['company_id'] = auth()->user()->company_id;
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
        $companyId = auth()->user()->company_id;
        $rules = [
            // PERBAIKAN: unik hanya di dalam company ini
            'name' => ['required', 'string', 'max:255', Rule::unique('services')->where('company_id', $companyId)->ignore($serviceId)],
            // PERBAIKAN: kategori harus ada di dalam company ini
            'category_id' => ['required', Rule::exists('categories', 'id')->where('company_id', $companyId)],
            'price' => ['required', 'numeric', 'min:0'],
        ];

        return $request->validate($rules);
    }
}