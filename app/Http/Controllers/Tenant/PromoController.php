<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Promo;
use App\Models\Product;
use Yajra\DataTables\Facades\DataTables;

class PromoController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        // Tampilkan hanya produk dari outlet aktif
        $products = Product::where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->get();

        return view('promos.index', compact('products'));
    }

    public function data()
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id');

        $promos = Promo::where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->with('products');

        return DataTables::of($promos)
            ->addIndexColumn()
            ->addColumn('products', fn($promo) => $promo->products->pluck('name')->join(', '))
            // 💡 kirim value mentah, jangan diformat di backend
            ->make(true);
    }

    public function store(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $outletId  = config('app.active_outlet_id'); // ✅ outlet aktif

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:percent,fixed',
            'value'       => 'required|numeric|min:0',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after:start_date',
            'products'    => 'required|array',
        ]);

        $validated['company_id'] = $companyId;
        $validated['outlet_id']  = $outletId; // ✅ simpan outlet aktif

        $promo = Promo::create($validated);

        // Pastikan produk yang dipilih hanya dari outlet aktif
        $validProducts = Product::where('company_id', $companyId)
            ->where('outlet_id', $outletId)
            ->whereIn('id', $request->products)
            ->pluck('id')
            ->toArray();

        $promo->products()->sync($validProducts);

        return response()->json(['success' => 'Promo berhasil ditambahkan untuk outlet aktif.']);
    }
}