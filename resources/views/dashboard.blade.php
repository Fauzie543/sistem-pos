@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    {{-- Card Statistik Utama --}}
    <div id="dashboard-grid" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
        {{-- ... (kode card statistik Anda tidak berubah) ... --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan Bulan Ini</h5>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pelanggan Baru</h5>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                {{ $customersThisMonth }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produk Terjual</h5>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                {{ $productsSoldThisMonth }}
            </p>
        </div>
    </div>

    {{-- Layout untuk Grafik dan Tabel --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Tambahkan flex-col agar div di dalamnya bisa tumbuh --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tren Penjualan 7 Hari Terakhir</h3>
            
            {{-- =============================================== --}}
            {{-- PERUBAHAN DI SINI --}}
            {{-- =============================================== --}}
            {{-- 1. Tambahkan div pembungkus dengan kelas 'relative' dan 'flex-grow' --}}
            <div class="relative flex-grow">
                {{-- 2. Hapus style height dari canvas --}}
                <canvas id="salesChart"></canvas>
            </div>
            
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            {{-- ... (kode aktivitas terkini tidak berubah) ... --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Terkini</h3>
            <div class="space-y-4">
                @forelse($recentSales as $sale)
                    <div class="flex justify-between items-center text-sm">
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $sale->customer->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $sale->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ... (sisa kode dashboard Anda tidak berubah) ... --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Produk Terlaris (Bulan Ini)</h3>
            <ul class="space-y-2">
                @forelse($topProducts as $item)
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">{{ $item->product->name ?? 'Produk Dihapus' }}</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $item->total_sold }} terjual</span>
                    </li>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada produk yang terjual bulan ini.</p>
                @endforelse
            </ul>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Jasa Terlaris (Bulan Ini)</h3>
            <ul class="space-y-2">
                 @forelse($topServices as $item)
                    <li class="flex justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">{{ $item->service->name ?? 'Jasa Dihapus' }}</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $item->total_used }} kali</span>
                    </li>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada jasa yang digunakan bulan ini.</p>
                @endforelse
            </ul>
        </div>
    </div>
@endsection


@push('scripts')
{{-- CDN untuk Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart');

        new Chart(ctx, {
            type: 'line',
            data: { /* ... (data tidak berubah) ... */
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Pendapatan',
                    data: @json($chartData),
                    fill: true,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                // ===============================================
                // PERUBAHAN DI SINI
                // ===============================================
                // 3. Tambahkan maintainAspectRatio: false
                maintainAspectRatio: false,
                scales: { /* ... (scales tidak berubah) ... */
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: { /* ... (plugins tidak berubah) ... */
                    legend: {
                        display: false
                    },
                    tooltip: {
                         callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush