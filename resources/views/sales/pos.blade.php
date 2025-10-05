@extends('layouts.app')
@section('title', 'Kasir')

@section('header', 'Kasir')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    {{-- Kolom Kiri: Info Pelanggan & Pencarian Item --}}
    <div class="lg:col-span-2 bg-white p-6 rounded-md shadow-sm">
        {{-- Customer --}}
        <div class="relative"> {{-- TAMBAHKAN 'relative' DI SINI --}}
            <label for="customer_search" class="block text-sm font-medium text-gray-700">Search Customer (Name/Phone)</label>
            <input type="text" id="customer_search" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Start typing...">
            {{-- 'w-full' sekarang akan mengikuti lebar input di atas --}}
            <div id="customer_search_results" class="absolute z-20 w-full bg-white border rounded-md shadow-lg hidden"></div>
            
            <div id="customer_details" class="mt-4 p-4 border rounded-md bg-gray-50 hidden">
                <h3 class="font-semibold" id="customer_name"></h3>
                <p class="text-sm text-gray-600" id="customer_phone"></p>
                <select name="vehicle_id" id="vehicle_id" class="mt-2 text-sm block w-full border-gray-300 rounded-md shadow-sm"></select>
            </div>
        </div>

        {{-- Item --}}
        <div class="mt-6 relative"> {{-- TAMBAHKAN CLASS 'relative' DI SINI --}}
            <label for="item_search" class="block text-sm font-medium text-gray-700">Search Product [P] or Service [J]</label>
            <input type="text" id="item_search" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Start typing...">
            {{-- Lebar 'w-full' akan mengikuti parent yang 'relative' --}}
            <div id="item_search_results" class="absolute z-10 w-full bg-white border rounded-md shadow-lg hidden"></div>
        </div>
    </div>

    {{-- Kolom Kanan: Keranjang & Pembayaran --}}
    <div class="bg-white p-6 rounded-md shadow-sm">
        <h2 class="text-xl font-bold border-b pb-2">Order Details</h2>
        <div id="cart" class="my-4 space-y-2">
            <p class="text-gray-500 text-center">Cart is empty.</p>
        </div>
        
        <div class="border-t pt-4 space-y-4"> {{-- Ubah space-y-2 menjadi space-y-4 --}}
            <div class="flex justify-between font-semibold text-lg"> {{-- Buat font lebih besar --}}
                <span>Grand Total</span>
                <span id="grand_total">Rp 0</span>
            </div>
            
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                <select id="payment_method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="cash" selected>Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="debit">Debit Card</option>
                </select>
            </div>

            {{-- BAGIAN BARU UNTUK PEMBAYARAN TUNAI --}}
            <div id="cash_payment_details" class="space-y-4">
                <div>
                    <label for="amount_paid" class="block text-sm font-medium text-gray-700">Amount Paid (Rp)</label>
                    <input type="number" id="amount_paid" placeholder="Enter cash amount" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-lg text-right">
                </div>
                <div class="flex justify-between font-semibold text-lg">
                    <span>Change Due</span>
                    <span id="change_due">Rp 0</span>
                </div>
            </div>
            {{-- AKHIR BAGIAN BARU --}}

            <button id="process_sale" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded mt-4">
                Process Sale
            </button>
        </div>
    </div>
</div>

{{-- MODAL UNTUK MENAMPILKAN QRIS --}}
<div id="qrisModal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm text-center">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 class="text-xl font-semibold">Scan QRIS to Pay</h3>
            <button id="closeQrisModal" class="text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        </div>
        
        {{-- Tempat untuk menampilkan QR Code --}}
        <div id="qris-container" class="my-4">
            {{-- Spinner loading --}}
            <div id="qris-spinner" class="flex justify-center items-center h-48">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-gray-900"></div>
            </div>
            {{-- Gambar QR Code --}}
            <img id="qris-image" src="" alt="QRIS Code" class="mx-auto hidden">
        </div>

        <p class="font-bold text-2xl" id="qris-amount"></p>
        <p class="text-sm text-gray-500" id="qris-expiry"></p>
    </div>
</div>
<iframe id="receipt-iframe" style="display:none;"></iframe>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    let customer = null;
    let vehicle_id = null;
    let cart = [];
    let grandTotalValue = 0;
    let currentQrisOrderId = null; // <-- TAMBAHKAN INI
    let qrisPollingInterval = null;
    let isQrisPaid = false;

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
            $('#grand_total').text('Rp 0');
            grandTotalValue = 0; // Reset nilai total
            calculateChange(); // Hitung ulang kembalian
            return;
        }
        
        $('#cart').html('');
        let currentTotal = 0; // Ganti nama variabel agar tidak konflik
        cart.forEach((item, index) => {
            const quantity = parseInt(item.quantity) || 0;
            const price = parseFloat(item.price) || 0;
            const subtotal = quantity * price;
            currentTotal += subtotal;
            
            $('#cart').append(`
                <div class="flex justify-between items-center text-sm" data-index="${index}">
                    <div>
                        <p class="font-semibold">${item.name}</p>
                        <p class="text-gray-600">Rp ${price.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" value="${quantity}" class="quantity-input w-16 border-gray-300 rounded-md shadow-sm text-sm" data-index="${index}">
                        <span class="w-24 text-right">Rp ${subtotal.toLocaleString('id-ID')}</span>
                        <button class="remove-item text-red-500 hover:text-red-700">&times;</button>
                    </div>
                </div>
            `);
        });
        
        grandTotalValue = currentTotal; // Simpan nilai total mentah
        $('#grand_total').text('Rp ' + grandTotalValue.toLocaleString('id-ID'));
        calculateChange(); // Panggil fungsi kalkulasi kembalian setiap kali keranjang di-render ulang
    }

    function calculateChange() {
        const amountPaid = parseFloat($('#amount_paid').val()) || 0;
        let change = amountPaid - grandTotalValue;

        if (change < 0 || amountPaid === 0) {
            change = 0;
        }
        
        $('#change_due').text('Rp ' + change.toLocaleString('id-ID'));
    }

    // GENERATE QRIS
    function generateQrCode() {
        if (grandTotalValue <= 0) {
            Swal.fire('Error', 'Cannot generate QRIS for empty cart.', 'error');
            $('#payment_method').val('cash').trigger('change'); // Kembalikan ke cash
            return;
        }

        // Tampilkan modal dan spinner
        $('#qrisModal').removeClass('hidden');
        $('#qris-spinner').show();
        $('#qris-image').hide();
        $('#qris-amount').text('');
        $('#qris-expiry').text('');

        $.ajax({
            url: '{{ route("pos.qris.generate") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                amount: grandTotalValue
            },
            success: function(response) {
                // Sembunyikan spinner dan tampilkan QR
                $('#qris-spinner').hide();
                $('#qris-image').attr('src', response.qr_code_url).show();
                $('#qris-amount').text('Rp ' + grandTotalValue.toLocaleString('id-ID'));
                $('#qris-expiry').text('Expires at: ' + response.expiry_time);
                
                // SIMPAN ORDER ID DAN MULAI POLLING SETELAH DAPAT RESPON
                currentQrisOrderId = response.order_id; 
                startQrisPolling(currentQrisOrderId);
            },
            error: function() {
                Swal.fire('Error', 'Failed to generate QRIS code. Please try again.', 'error');
                $('#qrisModal').addClass('hidden');
                $('#payment_method').val('cash').trigger('change');
            }
        });
    }

    function startQrisPolling(orderId) {
        isQrisPaid = false; 
        // Hentikan polling sebelumnya jika ada
        if (qrisPollingInterval) {
            clearInterval(qrisPollingInterval);
        }

        // Mulai polling baru setiap 3 detik
        qrisPollingInterval = setInterval(function() {

            if (isQrisPaid) { 
                stopQrisPolling();
                return;
            }

            $.get(`/pos/qris/status/${orderId}`, function(data) {
                // Jika pembayaran berhasil (settlement)
                if (data.transaction_status === 'settlement' && !isQrisPaid) {
                isQrisPaid = true; // <-- SET FLAG MENJADI TRUE
                
                stopQrisPolling();
                $('#qrisModal').addClass('hidden');
                $('#process_sale').click(); 
            }
            }).fail(function() {
                // Hentikan polling jika transaksi tidak ditemukan (misal, expired)
                stopQrisPolling();
            });
        }, 3000); // Cek setiap 3 detik
    }

    function stopQrisPolling() {
        if (qrisPollingInterval) {
            clearInterval(qrisPollingInterval);
            qrisPollingInterval = null;
        }
    }

    // EVENT LISTENER BARU UNTUK METODE PEMBAYARAN & NOMINAL BAYAR
    $('#payment_method').on('change', function() {
        const method = $(this).val();
        if (method === 'cash') {
            $('#cash_payment_details').slideDown();
            $('#process_sale').text('Process Sale'); // Kembalikan teks tombol
        } else {
            $('#cash_payment_details').slideUp();
            $('#amount_paid').val('');
            calculateChange();

            if (method === 'qris') {
                generateQrCode();
                $('#process_sale').text('Confirm Payment & Process Sale'); // Ubah teks tombol
            } else {
                $('#process_sale').text('Process Sale');
            }
        }
    });

    $('#amount_paid').on('input', function() {
        calculateChange();
    });

    $(document).on('input', '.quantity-input', function() {
        const index = $(this).data('index'); // Langsung ambil dari elemen input itu sendiri
        
        // Tambahkan pengecekan untuk memastikan index valid
        if (typeof index === 'undefined' || !cart[index]) {
            console.error("Invalid index or cart item not found for:", this);
            return; // Hentikan eksekusi jika index tidak valid
        }

        const newQuantity = parseInt($(this).val()) || 0; 

        if (newQuantity > 0) {
            cart[index].quantity = newQuantity;
        } else {
            cart[index].quantity = 1;
            $(this).val(1);
        }
        
        renderCart();
    });

    $('#closeQrisModal').on('click', function() {
        stopQrisPolling(); // <-- HENTIKAN POLLING
        $('#qrisModal').addClass('hidden');
        $('#payment_method').val('cash').trigger('change');
    });

    $(document).on('click', '.remove-item', function() {
        const index = $(this).closest('.flex').data('index');
        cart.splice(index, 1);
        renderCart();
    });

    // PROCESS SALE
    $('#process_sale').on('click', function() {
        $(this).prop('disabled', true).text('Processing...');
        if (!customer) {
            Swal.fire('Error', 'Please select a customer.', 'error');
            $('#process_sale').prop('disabled', false).text('Process Sale'); // Aktifkan kembali jika error
            return;
        }
        if (cart.length === 0) {
            Swal.fire('Error', 'Cart cannot be empty.', 'error');
            $('#process_sale').prop('disabled', false).text('Process Sale'); // Aktifkan kembali jika error
            return;
        }

        const paymentMethod = $('#payment_method').val();
        const amountPaid = parseFloat($('#amount_paid').val()) || 0;
        if (paymentMethod === 'cash' && amountPaid < grandTotalValue) {
            Swal.fire('Error', 'Amount paid is less than the grand total.', 'error');
            $('#process_sale').prop('disabled', false).text('Process Sale'); // Aktifkan kembali jika error
            return;
        }

        const saleData = {
            customer_id: customer.id,
            vehicle_id: vehicle_id,
            payment_method: paymentMethod,
            items: cart,
            // Tambahkan baris ini untuk menyimpan Order ID dari Midtrans
            invoice_number: (paymentMethod === 'qris' && currentQrisOrderId) ? currentQrisOrderId : 'INV-' + Date.now(),
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
                showCancelButton: true,
                confirmButtonText: 'Print Receipt',
                cancelButtonText: 'New Sale',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded',
                    cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded ml-2'
                }
            }).then((result) => {
                // Jika kasir menekan tombol "Print Receipt"
                if (result.isConfirmed) {
                    const receiptUrl = `/sales/${response.sale_id}/receipt`;
                    
                    // Muat konten struk ke iframe dan cetak
                    $('#receipt-iframe').attr('src', receiptUrl);
                    $('#receipt-iframe').on('load', function() {
                        this.contentWindow.print();
                    });
                }
            });

            // Reset form untuk transaksi selanjutnya
            customer = null; cart = []; vehicle_id = null; currentQrisOrderId = null; isQrisPaid = false;
            $('#customer_details').hide();
            $('#customer_search').val('');
            $('#payment_method').val('cash').trigger('change');
            renderCart();

            $('#process_sale').prop('disabled', false).text('Process Sale');
            },
            error: function(xhr) {
                Swal.fire('Error!', xhr.responseJSON.error || 'Something went wrong.', 'error');
                // 3. Aktifkan kembali tombol jika terjadi error
                $('#process_sale').prop('disabled', false).text('Process Sale');
            }
        });
    });
});
</script>
@endpush