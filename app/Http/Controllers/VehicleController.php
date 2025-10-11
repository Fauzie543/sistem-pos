<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    public function index(Customer $customer)
    {
        return view('vehicles.index', compact('customer'));
    }

    public function data(Customer $customer)
    {
        $vehicles = Vehicle::where('customer_id', $customer->id);

        return DataTables::of($vehicles)
            ->addIndexColumn()
            ->addColumn('action', function ($vehicle) {
                $deleteUrl = route('vehicles.destroy', $vehicle->id);
                return '
                    <a href="javascript:void(0)" data-id="' . $vehicle->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateVehicle($request);
        // PERBAIKAN: Tambahkan company_id
        $validated['company_id'] = auth()->user()->company_id;
        $vehicle = Vehicle::create($validated);
        // Mengembalikan data lengkap agar bisa digunakan di AJAX
        return response()->json($vehicle);
    }

    public function edit(Vehicle $vehicle)
    {
        return response()->json($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $this->validateVehicle($request, $vehicle->id);
        $vehicle->update($validated);
        return response()->json(['success' => 'Vehicle updated successfully.']);
    }

    public function destroy(Vehicle $vehicle)
    {
        // Nanti bisa dicek apakah kendaraan terikat dengan transaksi
        $vehicle->delete();
        return response()->json(['success' => 'Vehicle has been deleted successfully.']);
    }

    private function validateVehicle(Request $request, $vehicleId = null)
    {
        $companyId = auth()->user()->company_id;
        return $request->validate([
            // PERBAIKAN: Pastikan customer_id ada di dalam company ini
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('company_id', $companyId)],
            // PERBAIKAN: Plat nomor unik per company
            'license_plate' => ['required', 'string', 'max:20', Rule::unique('vehicles')->where('company_id', $companyId)->ignore($vehicleId)],
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:50'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:1900'],
        ]);
    }
}