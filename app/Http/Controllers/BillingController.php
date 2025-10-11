<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plan;

class BillingController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)->with('tiers')->get();
        return view('billing.index', compact('plans'));
    }

    // Nanti, kita akan buat logika untuk memproses pembayaran di sini
    public function processSubscription(Request $request)
    {
        // Fungsi ini akan menangani logika saat user memilih paket
        // Contoh: membuat checkout session di Midtrans
        dd($request->all()); // Untuk testing: Tampilkan data yang dipilih
    }
}