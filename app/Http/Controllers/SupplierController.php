<?php

namespace App\Http\Controllers;

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
        $suppliers = Supplier::query();

        return DataTables::of($suppliers)
            ->addIndexColumn()
            ->addColumn('action', function ($supplier) {
                $deleteUrl = route('suppliers.destroy', $supplier->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $supplier->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSupplier($request);
        Supplier::create($validated);
        return response()->json(['success' => 'Supplier created successfully.']);
    }

    public function edit(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validateSupplier($request, $supplier->id);
        $supplier->update($validated);
        return response()->json(['success' => 'Supplier updated successfully.']);
    }

    public function destroy(Supplier $supplier)
    {
        // Nanti bisa ditambahkan pengecekan apakah supplier terikat dengan transaksi pembelian
        $supplier->delete();
        return response()->json(['success' => 'Supplier has been deleted successfully.']);
    }

    private function validateSupplier(Request $request, $supplierId = null)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
        ];

        return $request->validate($rules);
    }
}