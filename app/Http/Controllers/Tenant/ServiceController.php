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
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        // Ambil kategori yang global atau milik outlet aktif
        $categories = Category::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
            ->orderBy('name')
            ->get();

        return view('services.index', compact('categories'));
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        // Ambil service untuk outlet aktif atau global
        $services = Service::with('category')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            })
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
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');

        $validated = $this->validateService($request);
        $validated['company_id'] = $companyId;
        $validated['outlet_id']  = $outletId; // ✅ simpan outlet aktif

        Service::create($validated);

        return response()->json(['success' => 'Layanan berhasil ditambahkan untuk outlet aktif.']);
    }

    public function edit(Service $service)
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service)
    {
        $outletId = config('app.active_outlet_id');

        // 🔒 Pastikan hanya bisa edit service outlet aktif
        if ($service->outlet_id !== $outletId && $service->outlet_id !== null) {
            return response()->json(['error' => 'Tidak dapat mengubah layanan dari outlet lain.'], 403);
        }

        $validated = $this->validateService($request, $service->id);
        $service->update($validated);

        return response()->json(['success' => 'Layanan berhasil diperbarui.']);
    }

    public function destroy(Service $service)
    {
        $outletId = config('app.active_outlet_id');

        // 🔒 Cegah hapus antar outlet
        if ($service->outlet_id !== $outletId && $service->outlet_id !== null) {
            return response()->json(['error' => 'Tidak dapat menghapus layanan dari outlet lain.'], 403);
        }

        $service->delete();
        return response()->json(['success' => 'Layanan berhasil dihapus.']);
    }

    private function validateService(Request $request, $serviceId = null)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');

        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('services')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
                    })
                    ->ignore($serviceId),
            ],
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
                    }),
            ],
            'price' => ['required', 'numeric', 'min:0'],
        ]);
    }
}