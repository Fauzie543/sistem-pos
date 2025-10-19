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
            // Validasi untuk memastikan semua kunci yang dibutuhkan ada jika salah satu diisi
            'keys.merchant_id' => 'nullable|string',
            'keys.client_key' => 'nullable|string',
            'keys.server_key' => 'nullable|string',
            'payment_gateway_is_production' => 'boolean',
        ]);

        $keys = $request->keys;

        // Jika semua kunci kosong, anggap user ingin menonaktifkan
        $isAllKeysEmpty = empty($keys['merchant_id']) && empty($keys['client_key']) && empty($keys['server_key']);

        $company->update([
            'payment_gateway_provider' => $isAllKeysEmpty ? null : 'midtrans',
            'payment_gateway_is_production' => $request->payment_gateway_is_production,
            'payment_gateway_keys' => $keys,
        ]);

        return redirect()->route('settings.payment.index')->with('success', 'Pengaturan gateway pembayaran berhasil diperbarui.');
    }
}