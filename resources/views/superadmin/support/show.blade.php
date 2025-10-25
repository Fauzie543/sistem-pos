@extends('layouts.app')

@section('header', 'Detail Tiket Bantuan')

@section('content')
<div class="bg-white rounded-lg shadow-sm p-4 border">
    <h3 class="text-lg font-semibold mb-2">{{ $ticket->subject }}</h3>
    <p class="text-sm text-gray-600 mb-4">
        Dari: <strong>{{ $ticket->user->name }}</strong> ({{ $ticket->company->name ?? 'Tidak diketahui' }})<br>
        Status: <span class="px-2 py-1 text-xs rounded 
            @if($ticket->status=='open') bg-yellow-100 text-yellow-800
            @elseif($ticket->status=='in_progress') bg-blue-100 text-blue-800
            @else bg-green-100 text-green-800 @endif">
            {{ ucfirst($ticket->status) }}
        </span>
    </p>

    <div class="border p-3 rounded mb-4 bg-gray-50">
        <p class="text-gray-700">{{ $ticket->message }}</p>
        <small class="text-gray-400 text-xs">Dikirim: {{ $ticket->created_at->diffForHumans() }}</small>
    </div>

    <div class="space-y-3 mb-6">
        @foreach($ticket->replies as $reply)
            <div class="border rounded p-3 
                {{ $reply->user->role->name == 'superadmin' ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200' }}">
                <strong>{{ $reply->user->name }}</strong>:
                <p class="text-sm text-gray-700 mt-1">{{ $reply->message }}</p>
                <small class="text-xs text-gray-400">{{ $reply->created_at->diffForHumans() }}</small>
            </div>
        @endforeach
    </div>

    <form action="{{ route('superadmin.support.reply', $ticket->id) }}" method="POST" class="space-y-2">
        @csrf
        <textarea name="message" rows="3" class="w-full border rounded p-2 text-sm" placeholder="Tulis balasan..."></textarea>
        
        <div class="flex justify-end gap-2 mt-2">
            @if($ticket->status !== 'resolved')
                <button type="submit" 
                    class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">
                    Kirim Balasan
                </button>
            @endif
        </div>
    </form>

    @if($ticket->status !== 'resolved')
    <form action="{{ route('superadmin.support.resolve', $ticket->id) }}" method="POST" class="mt-2">
        @csrf
        <button 
            class="bg-green-600 text-white text-sm px-4 py-2 rounded hover:bg-green-700">
            Tandai Selesai
        </button>
    </form>
    @endif
</div>
@endsection
