@extends('layouts.app')
@section('title', 'Tambah Paket Baru')
@section('header', 'Tambah Paket Baru')

@section('content')
    <form action="{{ route('superadmin.plans.store') }}" method="POST">
        @csrf
        @include('superadmin.plans._form', ['plan' => new \App\Models\Plan()])
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