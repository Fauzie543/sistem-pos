@extends('layouts.app')
@section('title', 'System Health')
@section('header', 'System Health & Maintenance')

@section('content')

{{-- 🌐 System Overview (Realtime) --}}
<div id="system-overview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="stat-card col-span-4 text-gray-400 text-center py-6" id="loading-status">
        Memuat status sistem...
    </div>
</div>

{{-- ⚙️ CPU & Memory Usage --}}
<div id="system-stats" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-center">
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 transition-all">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">CPU Usage</h3>
        <p id="cpuValue" class="text-5xl font-bold text-blue-600 mb-2">--%</p>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div id="cpuBar" class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 transition-all">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Memory Usage</h3>
        <p id="memValue" class="text-5xl font-bold text-green-600 mb-2">-- MB</p>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div id="memBar" class="bg-green-500 h-3 rounded-full transition-all duration-500" style="width:0%"></div>
        </div>
        <p id="memLimit" class="text-xs text-gray-500 mt-2">/ --</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- 🧹 Maintenance --}}
    <div class="card">
        <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
            Maintenance Tools
        </h2>

        <div class="space-y-4">
            <form id="clearCacheForm" method="POST" action="{{ route('superadmin.system.clearCache') }}">
                @csrf
                <button id="clearCacheBtn" type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
                    <span>Bersihkan Cache (route, config, view)</span>
                </button>
            </form>

            <form method="POST" action="{{ route('superadmin.system.clearLog') }}">
                @csrf
                <button class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded">
                    Rotate & Bersihkan Log ({{ $logSize }} MB)
                </button>
            </form>
        </div>

        <p class="text-xs text-gray-500 mt-4">
            Gunakan jika sistem mulai lambat, atau file log sudah terlalu besar.
        </p>
    </div>

    {{-- 📦 Storage Usage --}}
    <div class="card">
        <h2 class="text-lg font-semibold mb-4">Storage Usage per Tenant</h2>
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
            <div class="lg:w-1/2">
                <table class="w-full text-sm border rounded-md">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left py-2 px-3">Company</th>
                            <th class="text-right py-2 px-3">Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($tenantsUsage as $tenant)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-3">{{ $tenant['name'] }} 
                                <span class="text-xs text-gray-400">(#{{ $tenant['id'] }})</span>
                            </td>
                            <td class="py-2 px-3 text-right font-semibold">
                                {{ $tenant['usage_mb'] }} MB
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <p class="text-xs text-gray-500 mt-3">
                    Gunakan data ini untuk kontrol limit paket (misal paket Trial maksimal 100 MB).
                </p>
            </div>

            {{-- Chart --}}
            <div class="lg:w-1/2 mt-6 lg:mt-0 flex flex-col items-center justify-center text-sm text-gray-600">
                <div class="relative w-[180px] h-[180px] mb-4">
                    <canvas id="storagePieChart"></canvas>
                </div>

                <div id="system-extra" class="text-center space-y-1">
                    <p id="updateInfo" class="text-xs text-gray-400">Last updated: --:--:--</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
<script>
Swal.fire({
    toast: true,
    icon: 'success',
    title: @json(session('success')),
    position: 'top-end',
    timer: 2500,
    showConfirmButton: false
});
</script>
@endif

{{-- 📊 Pie Chart --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenants = @json($tenantsUsage);
    const labels = tenants.map(t => t.name);
    let data = tenants.map(t => parseFloat(t.usage_mb));
    if (data.every(v => v === 0)) data = data.map(() => 1);

    const colors = tenants.map(() =>
        `hsl(${Math.floor(Math.random() * 360)}, 70%, 60%)`
    );

    const ctx = document.getElementById('storagePieChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels.length ? labels : ['No Data'],
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 10 },
                        color: '#374151'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => `${ctx.label}: ${ctx.formattedValue} MB`
                    }
                }
            }
        }
    });
});
</script>

{{-- ⚡ Realtime System Overview + CPU/MEM Refresh --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('system-overview');
    const loading = document.getElementById('loading-status');

    function getColor(value) {
        if (value < 50) return 'bg-green-500';
        if (value < 80) return 'bg-yellow-500';
        return 'bg-red-500';
    }

    function loadStatus() {
        fetch("{{ route('superadmin.system.status') }}")
            .then(res => res.json())
            .then(data => {
                loading?.remove();

                // === SYSTEM CARDS ===
                container.innerHTML = `
                    <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
                        <h5 class="mb-1 text-base font-bold text-gray-900">Database</h5>
                        <p class="font-semibold ${data.dbStatus === 'Online' ? 'text-green-600' : 'text-red-600'}">
                            ${data.dbStatus}
                        </p>
                        <p class="text-xs text-gray-500">${data.dbLatency ?? '-'} ms</p>
                    </a>

                    <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
                        <h5 class="mb-1 text-base font-bold text-gray-900">Queue</h5>
                        <p class="font-semibold ${
                            data.queueStatus === 'Error'
                                ? 'text-red-600'
                                : (data.queueStatus === 'Processing'
                                    ? 'text-yellow-600'
                                    : 'text-green-600')
                        }">
                            ${data.queueStatus}
                        </p>
                        <p class="text-xs text-gray-500">${data.pendingJobs} pending / ${data.failedJobs} failed</p>
                        <p class="text-xs text-gray-400">Delay: ${data.queueDelay}s</p>
                    </a>

                    <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
                        <h5 class="mb-1 text-base font-bold text-gray-900">Log File</h5>
                        <p class="font-semibold text-gray-700">{{ $logSize }} MB</p>
                        <p class="text-xs text-gray-500">Ukuran file log</p>
                    </a>

                    <a href="#" class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50">
                        <h5 class="mb-1 text-base font-bold text-gray-900">Storage</h5>
                        <p class="font-semibold text-gray-700">{{ $tenantsUsage->sum('usage_mb') }} MB</p>
                        <p class="text-xs text-gray-500">Total semua tenant</p>
                    </a>
                `;

                // === UPDATE CPU & MEMORY ===
                const cpu = data.cpuLoad?.toFixed(2) ?? 0;
                const mem = data.memoryUsage ?? 0;
                const limit = data.phpMemLimit ?? '512M';

                // CPU
                document.getElementById('cpuValue').textContent = `${cpu}%`;
                const cpuBar = document.getElementById('cpuBar');
                cpuBar.style.width = `${Math.min(cpu, 100)}%`;
                cpuBar.className = `h-3 rounded-full transition-all duration-500 ${getColor(cpu)}`;

                // Memory
                document.getElementById('memValue').textContent = `${mem} MB`;
                const memBar = document.getElementById('memBar');
                const memPercent = Math.min((mem / 512) * 100, 100);
                memBar.style.width = `${memPercent}%`;
                memBar.className = `h-3 rounded-full transition-all duration-500 ${getColor(memPercent)}`;
                document.getElementById('memLimit').textContent = `/ ${limit}`;

                // Update time
                document.getElementById('updateInfo').textContent = `Last updated: ${data.lastUpdated}`;
            })
            .catch(() => {
                container.innerHTML = `<div class="col-span-4 text-center text-red-500">Gagal memuat status sistem.</div>`;
            });
    }

    loadStatus();
    setInterval(loadStatus, 10000);
});
</script>
@endpush
