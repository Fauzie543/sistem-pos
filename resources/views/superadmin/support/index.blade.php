@extends('layouts.app')

@section('header', 'Daftar Tiket Bantuan')

@section('content')
<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
        <thead class="bg-gray-100 text-gray-700 text-sm">
            <tr>
                <th class="px-4 py-2 text-left">Perusahaan</th>
                <th class="px-4 py-2">User</th>
                <th class="px-4 py-2">Subjek</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Tanggal</th>
                <th class="px-4 py-2">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-gray-700">
            @forelse ($tickets as $ticket)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $ticket->company->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $ticket->user->name }}</td>
                    <td class="px-4 py-2">{{ $ticket->subject }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs rounded 
                            @if($ticket->status=='open') bg-yellow-100 text-yellow-800
                            @elseif($ticket->status=='in_progress') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-sm">{{ $ticket->created_at->format('d M Y H:i') }}</td>
                    <td class="px-4 py-2 text-center">
                        <a href="{{ route('superadmin.support.show', $ticket->id) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">
                           Lihat
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-gray-500">Belum ada tiket.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
