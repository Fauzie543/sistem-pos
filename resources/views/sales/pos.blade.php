@extends('layouts.app')

@section('header', 'Point of Sale (POS)')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Kolom Kiri: Info Pelanggan & Pencarian Item --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-md shadow-sm">
        {{-- Customer --}}
        <div>
            <label for="customer_search" class="block text-sm font-medium text-gray-700">Search Customer (Name/Phone)</label>
            <input type="text" id="customer_search" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Start typing...">
            <div id="customer_search_results" class="absolute z-20 w-full bg-white border rounded-md shadow-lg hidden"></div>
            
            <div id="customer_details" class="mt-4 p-4 border rounded-md bg-gray-50 hidden">
                <h3 class="font-semibold" id="customer_name"></h3>
                <p class="text-sm text-gray-600" id="customer_phone"></p>
                <select name="vehicle_id" id="vehicle_id" class="mt-2 text-sm block w-full border-gray-300 rounded-md shadow-sm"></select>
            </div>
        </div>

        {{-- Item --}}
        <div class="mt-6">
            <label for="item_search" class="block text-sm font-medium text-gray-700">Search Product [P] or Service [J]</label>
            <input type="text" id="item_search" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Start typing...">
            <div id="item_search_results" class="absolute z-10 w-full bg-white border rounded-md shadow-lg hidden"></div>
        </div>
    </div>

    {{-- Kolom Kanan: Keranjang & Pembayaran --}}
    <div class="bg-white p-6 rounded-md shadow-sm">
        <h2 class="text-xl font-bold border-b pb-2">Order Details</h2>
        <div id="cart" class="my-4 space-y-2">
            {{-- Cart items will be here --}}
            <p class="text-gray-500 text-center">Cart is empty.</p>
        </div>
        
        <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between font-semibold">
                <span>Grand Total</span>
                <span id="grand_total">Rp 0</span>
            </div>
            
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                <select id="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="debit">Debit Card</option>
                </select>
            </div>

            <button id="process_sale" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded mt-4">
                Process Sale
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    let customer = null;
    let vehicle_id = null;
    let cart = [];

    // CUSTOMER SEARCH
    $('#customer_search').on('keyup', function() {
        let term = $(this).val();
        if (term.length < 2) { $('#customer_search_results').hide(); return; }
        $.get('{{ route("pos.customers.search") }}', {term: term}, function(data) {
            $('#customer_search_results').html('').show();
            data.forEach(c => $('#customer_search_results').append(`<div class="p-2 hover:bg-gray-100 cursor-pointer customer-item" data-customer='${JSON.stringify(c)}'>${c.name} (${c.phone_number})</div>`));
        });
    });

    $(document).on('click', '.customer-item', function() {
        customer = $(this).data('customer');
        $('#customer_name').text(customer.name);
        $('#customer_phone').text(customer.phone_number);
        $('#vehicle_id').html('<option value="">-- Select Vehicle --</option>');
        customer.vehicles.forEach(v => $('#vehicle_id').append(`<option value="${v.id}">${v.license_plate} (${v.brand} ${v.model})</option>`));
        $('#customer_details').show();
        $('#customer_search_results').hide();
        $('#customer_search').val(customer.name);
    });
    
    $('#vehicle_id').on('change', function() { vehicle_id = $(this).val(); });

    // ITEM SEARCH
    $('#item_search').on('keyup', function() {
        let term = $(this).val();
        if (term.length < 2) { $('#item_search_results').hide(); return; }
        $.get('{{ route("pos.items.search") }}', {term: term}, function(data) {
            $('#item_search_results').html('').show();
            data.forEach(i => $('#item_search_results').append(`<div class="p-2 hover:bg-gray-100 cursor-pointer item-item" data-item='${JSON.stringify(i)}'>${i.name}</div>`));
        });
    });

    $(document).on('click', '.item-item', function() {
        const item = $(this).data('item');
        const existing = cart.find(i => i.id === item.id && i.type === item.type);
        if (existing) {
            existing.quantity++;
        } else {
            cart.push({...item, quantity: 1});
        }
        $('#item_search_results').hide();
        $('#item_search').val('');
        renderCart();
    });

    // RENDER CART
    function renderCart() {
        if (cart.length === 0) {
            $('#cart').html('<p class="text-gray-500 text-center">Cart is empty.</p>');
        } else {
            $('#cart').html('');
            let grandTotal = 0;
            cart.forEach((item, index) => {
                const subtotal = item.quantity * item.price;
                grandTotal += subtotal;
                $('#cart').append(`
                    <div class="flex justify-between items-center text-sm" data-index="${index}">
                        <div>
                            <p class="font-semibold">${item.name}</p>
                            <p class="text-gray-600">Rp ${item.price.toLocaleString('id-ID')}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" value="${item.quantity}" class="quantity-input w-16 border-gray-300 rounded-md shadow-sm text-sm">
                            <span class="w-24 text-right">Rp ${subtotal.toLocaleString('id-ID')}</span>
                            <button class="remove-item text-red-500 hover:text-red-700">&times;</button>
                        </div>
                    </div>
                `);
            });
            $('#grand_total').text('Rp ' + grandTotal.toLocaleString('id-ID'));
        }
    }

    $(document).on('change', '.quantity-input', function() {
        const index = $(this).closest('.flex').data('index');
        cart[index].quantity = parseInt($(this).val());
        renderCart();
    });

    $(document).on('click', '.remove-item', function() {
        const index = $(this).closest('.flex').data('index');
        cart.splice(index, 1);
        renderCart();
    });

    // PROCESS SALE
    $('#process_sale').on('click', function() {
        if (!customer) { Swal.fire('Error', 'Please select a customer.', 'error'); return; }
        if (cart.length === 0) { Swal.fire('Error', 'Cart cannot be empty.', 'error'); return; }

        const saleData = {
            customer_id: customer.id,
            vehicle_id: vehicle_id,
            payment_method: $('#payment_method').val(),
            items: cart,
        };

        $.ajax({
            url: '{{ route("pos.store") }}',
            method: 'POST',
            data: JSON.stringify(saleData),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: response.success,
                    icon: 'success',
                    confirmButtonText: 'View Details'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `/sales/${response.sale_id}`;
                    }
                });
                // Reset form
                customer = null; cart = []; vehicle_id = null;
                $('#customer_details').hide();
                $('#customer_search').val('');
                renderCart();
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON.error || 'Something went wrong.', 'error');
            }
        });
    });
});
</script>
@endpush