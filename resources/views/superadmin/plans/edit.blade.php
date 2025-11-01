@extends('layouts.app')
@section('title', 'Edit Paket')
@section('header', 'Edit Paket: ' . $plan->name)

@section('content')
    <form action="{{ route('superadmin.plans.update', $plan) }}" method="POST">
        @csrf
        @method('PUT')
        @include('superadmin.plans._form')
    </form>
    @push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    @if (session('error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
    @endif
});
</script>
@endpush

@endsection