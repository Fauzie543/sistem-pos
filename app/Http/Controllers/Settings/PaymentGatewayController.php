<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        // Ambil data company dari user yang sedang login
        $company = auth()->user()->company;
        return view('settings.payment', compact('company'));
    }

    public function update(Request $request)
    {
        $company = auth()->user()->company;

        $request->validate([
            'payment_gateway_provider' => 'nullable|in:midtrans,gopay',
            'payment_gateway_is_production' => 'boolean',
            'keys' => 'nullable|array',
        ]);

        $company->update([
            'payment_gateway_provider' => $request->payment_gateway_provider,
            'payment_gateway_is_production' => $request->payment_gateway_is_production,
            'payment_gateway_keys' => $request->keys, // Simpan semua kunci sebagai JSON
        ]);

        return redirect()->route('settings.payment.index')->with('success', 'Pengaturan gateway pembayaran berhasil diperbarui.');
    }
}