<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Sale;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Landlord;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Landlord::execute(function () {
            return [
                'totalRevenue' => Sale::sum('total_amount'),
                'revenueThisMonth' => Sale::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->sum('total_amount'),
            ];
        });

        $totalCompanies = Company::count();
        $recentCompanies = Company::latest()->limit(5)->get();

        return view('superadmin.dashboard', [
            'totalRevenue' => $stats['totalRevenue'],
            'revenueThisMonth' => $stats['revenueThisMonth'],
            'totalCompanies' => $totalCompanies,
            'recentCompanies' => $recentCompanies,
        ]);
    }
}