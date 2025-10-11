@extends('layouts.app')

@section('title', 'Manajemen Fitur')
@section('header', 'Manajemen Fitur Klien')

@section('content')
    <div class="bg-white p-6 rounded-md shadow-sm">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif
        
        <form action="{{ route('superadmin.features.update') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 px-4 font-semibold">Perusahaan</th>
                            @foreach($features as $key => $name)
                                <th class="text-center py-2 px-4 font-semibold">{{ $name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($companies as $company)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $company->name }}</td>
                                @foreach($features as $key => $name)
                                    <td class="py-3 px-4 text-center">
                                        <input type="checkbox" 
                                               name="features[{{ $company->id }}][{{ $key }}]"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                               @checked($company->featureEnabled($key))>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($features) + 1 }}" class="text-center py-8 text-gray-500">
                                    Belum ada perusahaan yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@endsection