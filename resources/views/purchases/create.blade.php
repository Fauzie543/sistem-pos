@extends('layouts.app')

@section('header', 'Tambah Pembelian')

@section('content')
<form action="{{ route('purchases.store') }}" method="POST" class="bg-white p-6 rounded-md shadow-sm">
    @csrf
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
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
<script>
$(function () {
    let products = [];

    // Search Product
    $('#product_search').on('keyup', function() {
        var term = $(this).val();
        if (term.length < 2) {
            $('#search_results').hide();
            return;
        }
        $.get('{{ route('purchases.products.search') }}', { term: term }, function(data) {
            $('#search_results').html('').show();
            if (data.length > 0) {
                data.forEach(function(product) {
                    $('#search_results').append(
                        `<div class="p-2 hover:bg-gray-100 cursor-pointer search-item" data-id="${product.id}" data-name="${product.name}" data-price="${product.purchase_price}">
                            ${product.name} (${product.sku || 'N/A'})
                        </div>`
                    );
                });
            } else {
                $('#search_results').html('<div class="p-2">No products found.</div>');
            }
        });
    });

    // Add Product to List
    $(document).on('click', '.search-item', function() {
        const product = {
            id: $(this).data('id'),
            name: $(this).data('name'),
            price: $(this).data('price'),
            quantity: 1,
        };

        const existing = products.find(p => p.id === product.id);
        if (!existing) {
            products.push(product);
        } else {
            existing.quantity++;
        }
        
        $('#product_search').val('');
        $('#search_results').hide();
        renderTable();
    });

    // Handle input changes in the table
    $(document).on('change', '.quantity, .price', function() {
        const id = $(this).closest('tr').data('id');
        const product = products.find(p => p.id === id);
        if (product) {
            product.quantity = parseInt($(this).closest('tr').find('.quantity').val());
            product.price = parseFloat($(this).closest('tr').find('.price').val());
        }
        renderTable();
    });

    // Remove product from list
    $(document).on('click', '.remove-btn', function() {
        const id = $(this).closest('tr').data('id');
        products = products.filter(p => p.id !== id);
        renderTable();
    });

    // Render table rows and calculate total
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
                    <td><input type="number" name="products[${index}][quantity]" value="${p.quantity}" class="quantity w-24 border-gray-300 rounded-md shadow-sm"></td>
                    <td><input type="number" name="products[${index}][price]" value="${p.price}" class="price w-40 border-gray-300 rounded-md shadow-sm"></td>
                    <td class="text-right">Rp ${subtotal.toLocaleString('id-ID')}</td>
                    <td class="text-center"><button type="button" class="remove-btn text-red-500 hover:text-red-700">&times;</button></td>
                </tr>
            `);
        });

        $('#grand_total').text('Rp ' + grandTotal.toLocaleString('id-ID'));
    }
});
</script>
@endpush