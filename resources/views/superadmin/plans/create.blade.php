@extends('layouts.app')
@section('title', 'Tambah Paket Baru')
@section('header', 'Tambah Paket Baru')

@section('content')
    <form action="{{ route('superadmin.plans.store') }}" method="POST">
        @csrf
        @include('superadmin.plans._form', ['plan' => new \App\Models\Plan()])
    </form>
@endsection