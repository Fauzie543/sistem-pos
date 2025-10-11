@extends('layouts.app')

@section('title', 'Super Admin Dashboard')
@section('header', 'Super Admin Dashboard')

@section('content')
    {{-- Card Statistik Utama --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pendapatan (Semua Klien)</h5>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pendapatan Bulan Ini (Semua Klien)</h5>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h5 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah Klien Aktif</h5>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                {{ $totalCompanies }}
            </p>
        </div>
    </div>

    {{-- Tabel Klien Baru --}}
    <div class="mt-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
         <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Klien yang Baru Bergabung</h3>
         <div class="overflow-x-auto">
             <table class="w-full">
                 <thead>
                     <tr class="border-b">
                         <th class="text-left py-2 px-4 font-semibold">Nama Perusahaan</th>
                         <th class="text-left py-2 px-4 font-semibold">Tanggal Bergabung</th>
                         <th class="text-left py-2 px-4 font-semibold">Status Trial</th>
                     </tr>
                 </thead>
                 <tbody>
                    @forelse($recentCompanies as $company)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $company->name }}</td>
                            <td class="py-3 px-4">{{ $company->created_at->format('d F Y') }}</td>
                            <td class="py-3 px-4">
                                @if($company->trial_ends_at && $company->trial_ends_at->isFuture())
                                    <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-green-600 bg-green-200">
                                        Trial Aktif
                                    </span>
                                @else
                                     <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-red-600 bg-red-200">
                                        Trial Berakhir
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-8 text-gray-500">
                                Belum ada perusahaan yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                 </tbody>
             </table>
         </div>
    </div>
@endsection