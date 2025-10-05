<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Data user yang login
        $user = Auth::user()->load('role');

        // ====== CARD STATISTIK ======
        $customersThisMonth = Customer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $totalCustomers = Customer::count();
        $productsSoldThisMonth = SaleDetail::whereNotNull('product_id')->whereHas('sale', fn($q) => $q->whereMonth('created_at', now()->month))->sum('quantity');
        $totalProductsSold = SaleDetail::whereNotNull('product_id')->sum('quantity');
        $revenueThisMonth = Sale::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_amount');
        $totalRevenue = Sale::sum('total_amount');

        // ====== GRAFIK PENJUALAN 7 HARI TERAKHIR ======
        $salesData = Sale::where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            ])
            ->pluck('total', 'date');

        $dates = [];
        $salesChartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('d M');
            $salesChartData[] = $salesData->get($date, 0);
        }

        // ====== PRODUK & JASA TERLARIS (BULAN INI) ======
        $topProducts = SaleDetail::whereNotNull('product_id')
            ->with('product')
            ->whereHas('sale', fn($q) => $q->whereMonth('created_at', now()->month))
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $topServices = SaleDetail::whereNotNull('service_id')
            ->with('service')
            ->whereHas('sale', fn($q) => $q->whereMonth('created_at', now()->month))
            ->select('service_id', DB::raw('COUNT(service_id) as total_used'))
            ->groupBy('service_id')
            ->orderByDesc('total_used')
            ->limit(5)
            ->get();
            
        // ====== AKTIVITAS TRANSAKSI TERKINI ======
        $recentSales = Sale::with('customer')->latest()->limit(5)->get();

        // Mengirim semua data ke view
        return view('dashboard', [
            'user' => $user,
            'customersThisMonth' => $customersThisMonth,
            'totalCustomers' => $totalCustomers,
            'productsSoldThisMonth' => $productsSoldThisMonth,
            'totalProductsSold' => $totalProductsSold,
            'revenueThisMonth' => $revenueThisMonth,
            'totalRevenue' => $totalRevenue,
            'chartLabels' => $dates,
            'chartData' => $salesChartData,
            'topProducts' => $topProducts,
            'topServices' => $topServices,
            'recentSales' => $recentSales,
        ]);
    }
}