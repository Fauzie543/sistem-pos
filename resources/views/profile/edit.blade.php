@extends('layouts.app')

@section('title', 'Profile')
@section('header', 'Profile Settings')

@section('content')
    <div class="space-y-8">
        {{-- Bagian untuk Update Profile Information --}}
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Garis Pemisah --}}
        <hr class="border-gray-200 dark:border-gray-700">

        {{-- Bagian untuk Update Password --}}
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>

        {{-- Garis Pemisah --}}
        <hr class="border-gray-200 dark:border-gray-700">
        
        {{-- Bagian untuk Delete User --}}
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection