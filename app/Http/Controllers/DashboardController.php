<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Data user dan company yang login
        $user = Auth::user();
        $company = $user->company;

        // Jika karena suatu alasan company tidak ada, tampilkan dashboard kosong
        if (!$company) {
            abort(404, 'Company not found for this user.');
        }
        $companyId = $company->id; // Ambil ID untuk digunakan di query

        // Tanggal awal dan akhir bulan ini
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // ====== CARD STATISTIK (DENGAN FILTER company_id) ======
        $customersThisMonth = Customer::where('company_id', $companyId) // <-- FILTER
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $productsSoldThisMonth = SaleDetail::where('company_id', $companyId) // <-- FILTER
            ->whereNotNull('product_id')
            ->whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]))
            ->sum('quantity');

        $revenueThisMonth = Sale::where('company_id', $companyId) // <-- FILTER
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');

        // ====== GRAFIK PENJUALAN 7 HARI TERAKHIR (DENGAN FILTER company_id) ======
        $salesData = Sale::where('company_id', $companyId) // <-- FILTER
            ->where('created_at', '>=', now()->subDays(6))
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

        // ====== PRODUK TERLARIS (DENGAN FILTER company_id) ======
        $topProducts = SaleDetail::where('company_id', $companyId) // <-- FILTER
            ->whereNotNull('product_id')
            ->with('product')
            ->whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]))
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
            
        // ====== AKTIVITAS TRANSAKSI TERKINI (DENGAN FILTER company_id) ======
        $recentSales = Sale::where('company_id', $companyId)->with('customer')->latest()->limit(5)->get(); // <-- FILTER

        // ====== LOGIKA KONDISIONAL BARU (DENGAN FILTER company_id) ======
        $topServices = collect();
        $topCategories = collect();

        if ($company->featureEnabled('service_management')) {
            $topServices = SaleDetail::where('company_id', $companyId) // <-- FILTER
                ->whereNotNull('service_id')
                ->with('service')
                ->whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]))
                ->select('service_id', DB::raw('COUNT(service_id) as total_used'))
                ->groupBy('service_id')
                ->orderByDesc('total_used')
                ->limit(5)
                ->get();
        } else {
            $topCategories = SaleDetail::where('company_id', $companyId) // <-- FILTER
                ->whereNotNull('product_id')
                ->with('product.category')
                ->whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startOfMonth, $endOfMonth]))
                ->get()
                ->groupBy('product.category.name')
                ->map(fn($group) => $group->sum('quantity'))
                ->sortDesc()
                ->take(5);
        }

        // Mengirim semua data ke view
        return view('dashboard', [
            'user' => $user,
            'customersThisMonth' => $customersThisMonth,
            'productsSoldThisMonth' => $productsSoldThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'chartLabels' => $dates,
            'chartData' => $salesChartData,
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
            'topServices' => $topServices,
            'topCategories' => $topCategories,
        ]);
    }
}