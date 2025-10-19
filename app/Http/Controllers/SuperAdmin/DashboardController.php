<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Transaction; // <-- PERUBAHAN: Gunakan model Transaction
use Spatie\Multitenancy\Landlord;

class DashboardController extends Controller
{
    public function index()
    {
        // Landlord::execute() digunakan untuk menjalankan query di luar scope tenant
        $subscriptionStats = Landlord::execute(function () {
            // Definisikan status pembayaran yang dianggap berhasil
            $successfulStatuses = ['settlement', 'capture'];

            return [
                // Hitung total pendapatan dari semua transaksi langganan yang berhasil
                'totalRevenue' => Transaction::whereIn('status', $successfulStatuses)->sum('amount'), // <-- PERUBAHAN

                // Hitung pendapatan bulan ini dari transaksi langganan yang berhasil
                'revenueThisMonth' => Transaction::whereIn('status', $successfulStatuses) // <-- PERUBAHAN
                                        ->whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->sum('amount'),
            ];
        });

        $totalActiveCompanies = Company::where(function ($query) {
            $query->where('subscription_ends_at', '>=', now()); // Kondisi 1: Berlangganan aktif
        })->orWhere(function ($query) {
            $query->where('trial_ends_at', '>=', now()); // Kondisi 2: Trial aktif
        })->count();
        
        // Ambil data klien terbaru untuk ditampilkan di tabel
        $recentCompanies = Company::latest()->limit(10)->get();

        return view('superadmin.dashboard', [
            'totalRevenue' => $subscriptionStats['totalRevenue'],
            'revenueThisMonth' => $subscriptionStats['revenueThisMonth'],
            'totalCompanies' => $totalActiveCompanies,
            'recentCompanies' => $recentCompanies,
        ]);
    }
}