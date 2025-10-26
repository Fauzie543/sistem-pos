@extends('layouts.app')

@section('header', 'Tambah Pembelian')

@section('content')
<form action="{{ route('purchases.store') }}" method="POST" class="bg-white p-6 rounded-md shadow-sm">
    @csrf
    {{-- Purchase Info --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label for="purchase_date" class="block text-sm font-medium text-gray-700">Tanggal Pembelian</label>
            <input type="date" name="purchase_date" id="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>
        <div>
            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier</label>
            <select name="supplier_id" id="supplier_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="invoice_number" class="block text-sm font-medium text-gray-700">Supplier Invoice No. (Optional)</label>
            <input type="text" name="invoice_number" id="invoice_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        </div>
    </div>

    {{-- Product Search --}}
    <div class="border-t pt-4">
        <label for="product_search" class="block text-sm font-medium text-gray-700">Cari Produk (by Name or SKU)</label>
        <div class="relative">
            <input type="text" id="product_search" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Start typing to search...">
            <div id="search_results" class="absolute z-10 w-full bg-white border rounded-md shadow-lg hidden"></div>
        </div>
    </div>

    {{-- Product List Table --}}
    <div class="mt-6">
        <h3 class="text-lg font-medium">Pembelian Peoduk</h3>
        <table class="w-full mt-2">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-2">Produk</th>
                    <th class="w-32 text-left py-2">Kuantitas</th>
                    <th class="w-48 text-left py-2">Harga Pembelian (Rp)</th>
                    <th class="w-48 text-right py-2">Subtotal</th>
                    <th class="w-16 py-2"></th>
                </tr>
            </thead>
            <tbody id="product_list">
                {{-- Rows will be added by JavaScript --}}
            </tbody>
            <tfoot>
                <tr class="border-t-2 font-bold">
                    <td colspan="3" class="text-right py-3">Grand Total</td>
                    <td id="grand_total" class="text-right py-3">Rp 0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="flex justify-end mt-6">
        <a href="{{ route('purchases.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">Batal</a>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    let products = [];

    // === Format angka ke Rupiah ===
    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // === Ubah string harga (dengan titik) jadi angka murni ===
    function parseRupiah(str) {
        return parseInt(str.replace(/[^\d]/g, '')) || 0;
    }

    // === Re-render tabel produk ===
    function renderTable() {
        $('#product_list').html('');
        let grandTotal = 0;

        products.forEach(function(p, index) {
            const subtotal = p.quantity * p.price;
            grandTotal += subtotal;

            $('#product_list').append(`
                <tr class="border-b" data-id="${p.id}">
                    <td class="py-2">
                        <input type="hidden" name="products[${index}][id]" value="${p.id}">
                        ${p.name}
                    </td>
                    <td>
                        <input type="number" name="products[${index}][quantity]" 
                               value="${p.quantity}" 
                               class="quantity w-24 border-gray-300 rounded-md shadow-sm text-right">
                    </td>
                    <td>
                        <input type="text" name="products[${index}][price]" 
                               value="${formatRupiah(p.price)}" 
                               class="price w-40 border-gray-300 rounded-md shadow-sm text-right">
                    </td>
                    <td class="text-right subtotal">Rp ${formatRupiah(subtotal)}</td>
                    <td class="text-center">
                        <button type="button" class="remove-btn text-red-500 hover:text-red-700">&times;</button>
                    </td>
                </tr>
            `);
        });

        $('#grand_total').text('Rp ' + formatRupiah(grandTotal));
    }

    // === Cari produk (AJAX) ===
    $('#product_search').on('keyup', function() {
        let term = $(this).val();
        if (term.length < 2) return $('#search_results').hide();

        $.get('{{ route('purchases.products.search') }}', { term }, function(data) {
            $('#search_results').html('').show();
            if (data.length > 0) {
                data.forEach(p => {
                    $('#search_results').append(`
                        <div class="p-2 hover:bg-gray-100 cursor-pointer search-item"
                             data-id="${p.id}" data-name="${p.name}" data-price="${p.purchase_price}">
                             ${p.name} (${p.sku || 'N/A'})
                        </div>
                    `);
                });
            } else {
                $('#search_results').html('<div class="p-2">Tidak ada produk ditemukan.</div>');
            }
        });
    });

    // === Tambah produk ke list ===
    $(document).on('click', '.search-item', function() {
        const p = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            price: parseInt($(this).data('price')) || 0,
            quantity: 1,
        };
        const existing = products.find(x => x.id === p.id);
        if (existing) {
            existing.quantity++;
        } else {
            products.push(p);
        }
        $('#product_search').val('');
        $('#search_results').hide();
        renderTable();
    });

    // === Update harga / qty secara langsung (real-time) ===
    $(document).on('input', '.quantity, .price', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const product = products.find(p => p.id === id);

        if (product) {
            const qty = parseInt(row.find('.quantity').val()) || 0;
            const price = parseRupiah(row.find('.price').val());
            product.quantity = qty;
            product.price = price;

            // Update tampilan subtotal baris ini
            const subtotal = qty * price;
            row.find('.subtotal').text('Rp ' + formatRupiah(subtotal));

            // Update grand total keseluruhan
            const total = products.reduce((sum, p) => sum + (p.quantity * p.price), 0);
            $('#grand_total').text('Rp ' + formatRupiah(total));
        }
    });

    // === Format harga saat diketik ===
    $(document).on('input', '.price', function() {
        let raw = $(this).val().replace(/[^\d]/g, '');
        $(this).val(formatRupiah(raw));
    });

    // === Hapus produk dari list ===
    $(document).on('click', '.remove-btn', function() {
        const id = $(this).closest('tr').data('id');
        products = products.filter(p => p.id !== id);
        renderTable();
    });

    // === Saat submit, ubah harga ke angka murni ===
    $('form').on('submit', function() {
        $('.price').each(function() {
            $(this).val($(this).val().replace(/\./g, '').replace(/,/g, ''));
        });
    });

    // === SweetAlert session ===
    // === SweetAlert session & validasi ===
@if ($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Gagal Menyimpan!',
        html: `
            <ul style="text-align:left; margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        `,
        confirmButtonText: 'Tutup',
        customClass: {
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
        },
        buttonsStyling: false
    });
@endif

@if (session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: "{{ session('error') }}",
        confirmButtonText: 'Tutup',
        customClass: {
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
        },
        buttonsStyling: false
    });
@endif

@if (session('success'))
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: "{{ session('success') }}",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: '#f0fdf4',
        color: '#065f46',
        iconColor: '#16a34a'
    });
@endif

});
</script>
@endpush
