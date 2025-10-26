@extends('layouts.app')
@section('title', 'Produk')
@section('header', 'Manajemen Produk')

@push('styles')
<style>
    div.dt-container div.dt-search input { width: 15rem; }
</style>
@endpush

@section('content')
<div class="bg-white p-6 rounded-md shadow-sm">
    <button id="addProductBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4 inline-block">
        Tambah Produk
    </button>

    <table id="products-table" class="w-full">
        <thead>
            <tr>
                <th class="w-10">No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga Jual</th>
                <th class="w-16">Stock</th>
                <th class="w-32">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@include('products.modal')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    var table = $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: '{{ route('products.data') }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'name', name: 'name' },
            { data: 'category.name', name: 'category.name' },
            { data: 'selling_price', name: 'selling_price', className: 'text-right' },
            { data: 'stock', name: 'stock', className: 'text-center' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
    });

    const rupiahFormatter = new Intl.NumberFormat('id-ID');

    function formatRupiahInput(selector) {
        $(document).on('input', selector, function(e) {
            let value = e.target.value.replace(/[^\d]/g, ''); // hanya angka
            if (value) {
                $(this).val(rupiahFormatter.format(value));
            } else {
                $(this).val('');
            }
        });
    }

    // Terapkan ke dua input harga
    formatRupiahInput('#purchase_price');
    formatRupiahInput('#selling_price');

    // Saat form disubmit, ubah ke angka murni tanpa titik/koma
    $('#productForm').on('submit', function() {
        $('#purchase_price, #selling_price').each(function() {
            $(this).val($(this).val().replace(/\./g, '').replace(/,/g, ''));
        });
    });

    $('#addProductBtn').on('click', function () {
        $('#productForm')[0].reset();
        $('.error-message').text('');
        $('#modal_title').text('Tambah Produk');
        $('#submitBtn').text('Simpan');
        $('#form_method').val('POST');
        $('#productForm').attr('action', '{{ route('products.store') }}');
        $('#productModal').removeClass('hidden');
    });

    $('#products-table').on('click', '.edit-btn', function () {
        var productId = $(this).data('id');
        var url = `/products/${productId}/edit`;

        $('#productForm')[0].reset();
        $('.error-message').text('');

        $.get(url, function(data) {
            $('#modal_title').text('Edit Produk');
            $('#submitBtn').text('Simpan Perubahan');
            $('#form_method').val('PUT');
            $('#productForm').attr('action', `/products/${productId}`);

            // Isi semua field form
            $.each(data, function(key, value) {
                $('#' + key).val(value);
            });

            $('#productModal').removeClass('hidden');
        }).fail(function() {
            Swal.fire('Error', 'Could not fetch product data.', 'error');
        });
    });

    $('#cancelBtn, .close-modal').on('click', function () {
        $('#productModal').addClass('hidden');
    });

    $('#productForm').on('submit', function (e) {
        e.preventDefault();
        $('.error-message').text('');
        var url = $(this).attr('action');
        var formData = $(this).serialize();

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#productModal').addClass('hidden');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: response.success, showConfirmButton: false, timer: 3000 });
                table.ajax.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, value) {
                        $('#' + key + '_error').text(value[0]);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menyimpan produk baru.',
                        confirmButtonText: 'Tutup',
                        customClass: {
                            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
                        },
                        buttonsStyling: false
                    });
                }
            }
        });
    });
    
    $('#products-table').on('click', '.delete-btn', function (e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
                cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.success,
                            icon: 'success',
                            confirmButtonText: 'Tutup',
                            customClass: {
                                confirmButton: 'bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded'
                            },
                            buttonsStyling: false
                        });
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal!',
                            text: xhr.responseJSON?.error || 'Terjadi kesalahan saat menghapus data.',
                            icon: 'error',
                            confirmButtonText: 'Tutup',
                            customClass: {
                                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });


});
</script>
@endpush