<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OutletController extends Controller
{
    public function index()
    {
        return view('outlets.index');
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;

        $outlets = Outlet::where('company_id', $companyId)
            ->select('id', 'name', 'code', 'address', 'phone');

        return DataTables::of($outlets)
            ->addIndexColumn()
            ->addColumn('action', function ($outlet) {
                $deleteUrl = route('outlets.destroy', $outlet->id);
                return '
                    <a href="javascript:void(0)" data-id="'.$outlet->id.'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="'.$deleteUrl.'" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Hapus</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:outlets,code',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        Outlet::create($validated);

        return response()->json(['success' => 'Outlet berhasil ditambahkan.']);
    }

    public function edit(Outlet $outlet)
    {
        return response()->json($outlet);
    }

    public function update(Request $request, Outlet $outlet)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:outlets,code,'.$outlet->id,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $outlet->update($validated);

        return response()->json(['success' => 'Outlet berhasil diperbarui.']);
    }

    public function destroy(Outlet $outlet)
    {
        $outlet->delete();
        return response()->json(['success' => 'Outlet berhasil dihapus.']);
    }
}