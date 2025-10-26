<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index');
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $suppliers = Supplier::where('company_id', $companyId)
            ->where(function ($q) use ($outletId) {
                $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
            });

        return DataTables::of($suppliers)
            ->addIndexColumn()
            ->addColumn('action', function ($supplier) {
                $deleteUrl = route('suppliers.destroy', $supplier->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $supplier->id . '" 
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
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $validated = $this->validateSupplier($request);
        $validated['company_id'] = $companyId;
        $validated['outlet_id']  = $outletId; // ✅ simpan outlet aktif

        Supplier::create($validated);

        return response()->json(['success' => 'Supplier berhasil ditambahkan untuk outlet aktif.']);
    }

    public function edit(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $outletId = config('app.active_outlet_id');

        // 🔒 Cegah update supplier outlet lain
        if ($supplier->outlet_id !== $outletId && $supplier->outlet_id !== null) {
            return response()->json(['error' => 'Tidak dapat mengubah supplier dari outlet lain.'], 403);
        }

        $validated = $this->validateSupplier($request, $supplier->id);
        $supplier->update($validated);

        return response()->json(['success' => 'Supplier berhasil diperbarui.']);
    }

    public function destroy(Supplier $supplier)
    {
        $outletId = config('app.active_outlet_id');

        // 🔒 Cegah hapus supplier outlet lain
        if ($supplier->outlet_id !== $outletId && $supplier->outlet_id !== null) {
            return response()->json(['error' => 'Tidak dapat menghapus supplier dari outlet lain.'], 403);
        }

        $supplier->delete();
        return response()->json(['success' => 'Supplier berhasil dihapus.']);
    }

    private function validateSupplier(Request $request, $supplierId = null)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');

        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('suppliers')
                    ->where('company_id', $companyId)
                    ->where(function ($q) use ($outletId) {
                        $q->whereNull('outlet_id')->orWhere('outlet_id', $outletId);
                    })
                    ->ignore($supplierId),
            ],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ]);
    }
}