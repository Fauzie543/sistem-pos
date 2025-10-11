@extends('layouts.app')
@section('title', 'Edit Paket')
@section('header', 'Edit Paket: ' . $plan->name)

@section('content')
    <form action="{{ route('superadmin.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        @include('superadmin.plans._form')
    </form>
@endsection