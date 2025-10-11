<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index');
    }

    public function data()
    {
        $customers = Customer::query();

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function ($customer) {
                $vehiclesUrl = route('customers.vehicles.index', $customer->id);
                $deleteUrl = route('customers.destroy', $customer->id);
                return '
                    <a href="'.$vehiclesUrl.'" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs">Kendaraan</a>
                    <a href="javascript:void(0)" data-id="' . $customer->id . '" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs edit-btn ml-2">Edit</a>
                    <a href="javascript:void(0)" data-url="' . $deleteUrl . '" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs delete-btn ml-2">Delete</a>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);
        $validated['company_id'] = auth()->user()->company_id;
        Customer::create($validated);
        return response()->json(['success' => 'Customer created successfully.']);
    }

    public function edit(Customer $customer)
    {
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $this->validateCustomer($request, $customer->id);
        $customer->update($validated);
        return response()->json(['success' => 'Customer updated successfully.']);
    }

    public function destroy(Customer $customer)
    {
        if ($customer->vehicles()->exists()) {
            return response()->json(['error' => 'Cannot delete customer with associated vehicles.'], 403);
        }
        $customer->delete();
        return response()->json(['success' => 'Customer has been deleted successfully.']);
    }

    private function validateCustomer(Request $request, $customerId = null)
    {
        $companyId = auth()->user()->company_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // PERBAIKAN: Pastikan phone_number unik hanya di dalam company yang sama
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('customers')->where('company_id', $companyId)->ignore($customerId)],
            'address' => ['nullable', 'string'],
        ]);
    }
}