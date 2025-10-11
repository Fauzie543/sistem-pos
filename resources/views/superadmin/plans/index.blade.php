@extends('layouts.app')
@section('title', 'Manajemen Paket')
@section('header', 'Manajemen Paket Langganan')

@section('content')
    <div class="bg-white p-6 rounded-md shadow-sm">
        <a href="{{ route('superadmin.plans.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
            Tambah Paket Baru
        </a>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2 px-4">Nama Paket</th>
                        <th class="text-left py-2 px-4">Status</th>
                        <th class="text-left py-2 px-4">Harga</th>
                        <th class="w-40 text-center py-2 px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <p class="font-semibold">{{ $plan->name }}</p>
                                <p class="text-xs text-gray-500">{{ $plan->description }}</p>
                            </td>
                            <td class="py-3 px-4">
                                @if($plan->is_active)
                                    <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-green-600 bg-green-200">Aktif</span>
                                @else
                                    <span class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-red-600 bg-red-200">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm">
                                @foreach($plan->tiers->sortBy('duration_months') as $tier)
                                    <div>{{ $tier->duration_months }} Bulan: <strong>Rp {{ number_format($tier->price) }}</strong></div>
                                @endforeach
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('superadmin.plans.edit', $plan) }}" class="text-blue-600 hover:underline text-sm">Edit</a>
                                <form action="{{ route('superadmin.plans.destroy', $plan) }}" method="POST" class="inline-block ml-4" onsubmit="return confirm('Anda yakin ingin menghapus paket ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline text-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-gray-500">Belum ada paket yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection