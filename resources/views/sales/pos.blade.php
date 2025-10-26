@extends('layouts.app')
@section('title', 'Kasir')
@section('header', 'Point of Sale')

@push('styles')
<style>
    /* Styling agar scrollbar lebih tipis dan modern */
    #item-list::-webkit-scrollbar, #cart::-webkit-scrollbar { width: 6px; }
    #item-list::-webkit-scrollbar-track, #cart::-webkit-scrollbar-track { background: #f1f1f1; }
    #item-list::-webkit-scrollbar-thumb, #cart::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
    #item-list::-webkit-scrollbar-thumb:hover, #cart::-webkit-scrollbar-thumb:hover { background: #555; }
    
    /* Custom style untuk Select2 agar sesuai dengan tema Tailwind */
    .select2-container .select2-selection--single { height: 2.5rem !important; border-color: #d1d5db !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 2.5rem !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 2.5rem !important; }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-12rem)]">
    
    {{-- KOLOM KIRI: DAFTAR PRODUK & JASA --}}
    {{-- TAMBAHKAN min-h-0 DI SINI --}}
    <div class="lg:col-span-2 bg-white rounded-md shadow-sm flex flex-col min-h-0">
        {{-- Header: Filter & Pencarian --}}
        <div class="p-4 border-b">
            <div class="flex flex-col md:flex-row gap-4">
                <input type="text" id="item_search_input" class="w-full md:w-1/3 border-gray-300 rounded-md shadow-sm" placeholder="Cari item...">
                <select id="category_filter" class="w-full md:w-1/3 border-gray-300 rounded-md shadow-sm">
                    <option value="all">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Grid Daftar Item --}}
        <div id="item-list" class="flex-grow p-4 overflow-y-auto grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @forelse($items as $item)
                <div class="item-card cursor-pointer border rounded-lg p-3 flex flex-col justify-between h-28 hover:border-blue-500 hover:shadow-lg transition-all"
                    data-item-id="{{ $item->type }}-{{ $item->id }}"
                    data-category-id="{{ $item->category_id }}"
                    data-name="{{ strtolower($item->name) }}">

                    <div>
                        <p class="font-semibold text-sm truncate">{{ $item->name }}</p>
                        <span class="text-xs {{ $item->type == 'product' ? 'text-green-600' : 'text-purple-600' }}">
                            {{ $item->type == 'product' ? 'Produk' : 'Jasa' }}
                        </span>
                    </div>

                    {{-- HARGA --}}
                    <div class="text-right mt-auto">
                        @if($item->price < $item->original_price)
                            <p class="text-gray-400 line-through text-xs mb-0.5">
                                Rp {{ number_format($item->original_price, 0, ',', '.') }}
                            </p>
                            <p class="font-bold text-green-600 text-sm leading-none">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        @else
                            <p class="font-bold text-gray-800 text-sm leading-none">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        @endif
                    </div>

                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 py-10">
                    <p>Tidak ada produk atau jasa yang ditemukan.</p>
                    <p class="text-sm">Silakan tambahkan data di menu Produk atau Jasa terlebih dahulu.</p>
                </div>
            @endforelse
        </div>

    </div>

    {{-- KOLOM KANAN: KERANJANG & PEMBAYARAN --}}
    {{-- TAMBAHKAN min-h-0 DI SINI --}}
    <div class="bg-white rounded-md shadow-sm flex flex-col min-h-0">
        {{-- Info Pelanggan --}}
        <div class="p-4 border-b">
            <label for="customer_input" class="block text-sm font-medium text-gray-700">Pelanggan</label>
            <input type="text" id="customer_input" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Ketik nama pelanggan...">
            <div id="customer_suggestions" class="border border-gray-300 rounded-md mt-1 hidden bg-white shadow-md max-h-40 overflow-y-auto"></div>

        </div>

        {{-- Keranjang Belanja --}}
        <div class="flex-grow p-4 overflow-y-auto" id="cart">
            <p class="text-gray-500 text-center">Keranjang kosong.</p>
        </div>

        {{-- Ringkasan & Pembayaran --}}
        <div class="p-4 border-t space-y-4">
            <div class="flex justify-between font-semibold text-lg">
                <span>Grand Total</span>
                <span id="grand_total">Rp 0</span>
            </div>
            
            <div id="cash_payment_details" class="space-y-2" style="display: none;">
                <div>
                    <label for="amount_paid" class="block text-sm font-medium text-gray-700">Uang Tunai (Rp)</label>
                    <input type="number" id="amount_paid" placeholder="Masukkan jumlah uang" class="mt-1 block w-full text-right border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="flex justify-between font-medium text-base">
                    <span>Kembalian</span>
                    <span id="change_due">Rp 0</span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                 <select id="payment_method" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="cash" selected>Cash</option>
                    <option value="qris">QRIS</option>
                </select>
                <button id="process_sale" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded">
                    Bayar
                </button>
            </div>
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
        <div id="qris-container" class="my-4">
            <div id="qris-spinner" class="flex justify-center items-center h-48">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-gray-900"></div>
            </div>
            <img id="qris-image" src="" alt="QRIS Code" class="mx-auto hidden">
        </div>
        <p class="font-bold text-2xl" id="qris-amount"></p>
        <p class="text-sm text-gray-500" id="qris-expiry"></p>
    </div>
</div>

<!-- Modal Tambah Pelanggan (tanpa background hitam) -->
<div id="addCustomerModal"
     class="hidden fixed top-20 left-1/2 transform -translate-x-1/2 bg-white border border-gray-300 shadow-2xl rounded-lg w-full max-w-md z-[9999]">
  <div class="p-6">
    <h2 class="text-lg font-bold mb-4 border-b pb-2">Tambah Pelanggan Baru</h2>
    <div class="space-y-3">
      <div>
        <label class="block text-sm font-medium">Nama Pelanggan</label>
        <input type="text" id="new_customer_name"
               class="w-full border-gray-300 rounded-md shadow-sm mt-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Nama pelanggan">
      </div>
      <div>
        <label class="block text-sm font-medium">Nomor Telepon</label>
        <input type="text" id="new_customer_phone"
               class="w-full border-gray-300 rounded-md shadow-sm mt-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="Nomor telepon (opsional)">
      </div>
    </div>
    <div class="flex justify-end mt-5 space-x-2 border-t pt-4">
      <button id="cancelAddCustomer"
              class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded font-semibold">
        Batal
      </button>
      <button id="saveNewCustomer"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">
        Simpan
      </button>
    </div>
  </div>
</div>


<iframe id="receipt-iframe" style="display:none;"></iframe>



@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    let vehicle_id = null;
    let cart = [];
    const allItems = @json($items);
    let qrisPollingInterval = null;

    // ===================================================
    // LOGIKA TAMPILAN BARU
    // ===================================================

    $('.item-card').on('click', function() {
        const itemId = $(this).data('item-id');
        const [type, id] = itemId.split('-');
        const itemData = allItems.find(item => item.id == id && item.type == type);
        if (itemData) {
            addItemToCart(itemData);
        }
    });

    $('#category_filter').on('change', () => filterItems());
    $('#item_search_input').on('keyup', () => filterItems());

    function filterItems() {
        const categoryId = $('#category_filter').val();
        const searchTerm = $('#item_search_input').val().toLowerCase();

        $('.item-card').each(function() {
            const card = $(this);
            const cardCategoryId = card.data('category-id').toString();
            const cardName = card.data('name');
            const categoryMatch = (categoryId === 'all' || cardCategoryId === categoryId);
            const searchMatch = (searchTerm === '' || cardName.includes(searchTerm));
            
            if (categoryMatch && searchMatch) card.show();
            else card.hide();
        });
    }

    // ===================================================
    // FUNGSI KERANJANG (CART)
    // ===================================================
    function addItemToCart(item) {
        const existingItem = cart.find(cartItem => cartItem.id === item.id && cartItem.type === item.type);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.push({ ...item, quantity: 1 });
        }
        renderCart();
    }

    function renderCart() {
        if (cart.length === 0) {
            $('#cart').html('<p class="text-gray-500 text-center">Keranjang kosong.</p>');
            updateTotals();
            return;
        }

        $('#cart').html('');
        cart.forEach((item, index) => {
            const subtotal = item.quantity * item.price;
            $('#cart').append(`
                <div class="py-2 border-b">
                    <div class="flex justify-between items-start">
                        <p class="font-semibold text-sm pr-2">${item.name}</p>
                        <p class="font-semibold text-sm whitespace-nowrap">Rp ${subtotal.toLocaleString('id-ID')}</p>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-xs text-gray-600">@ Rp ${item.price.toLocaleString('id-ID')}</p>
                        <div class="flex items-center gap-2">
                            <button class="qty-change text-lg font-bold px-1" data-index="${index}" data-amount="-1">-</button>
                            <input type="number" value="${item.quantity}" class="quantity-input w-12 text-center border-gray-300 rounded-md text-sm" data-index="${index}">
                            <button class="qty-change text-lg font-bold px-1" data-index="${index}" data-amount="1">+</button>
                            <button class="remove-item text-red-500 text-xl font-bold" data-index="${index}">&times;</button>
                        </div>
                    </div>
                    <div class="mt-2">
                        <input type="text" 
                            class="item-note w-full border-gray-300 rounded-md text-xs p-1" 
                            placeholder="Tambahkan keterangan..." 
                            data-index="${index}"
                            value="${item.note || ''}">
                    </div>
                </div>
            `);
        });
        updateTotals();
    }

    function updateTotals() {
        let grandTotal = cart.reduce((total, item) => total + (item.quantity * item.price), 0);
        $('#grand_total').text('Rp ' + grandTotal.toLocaleString('id-ID'));
        calculateChange();
    }
    
    $('#cart').on('click', '.qty-change', function() {
        const index = $(this).data('index');
        const amount = $(this).data('amount');
        cart[index].quantity += amount;
        if (cart[index].quantity < 1) cart[index].quantity = 1;
        renderCart();
    });

    $('#cart').on('input', '.quantity-input', function() {
        const index = $(this).data('index');
        let newQty = parseInt($(this).val());
        if (isNaN(newQty) || newQty < 1) newQty = 1;
        cart[index].quantity = newQty;
        updateTotals(); // Lebih efisien daripada renderCart() penuh
    });
    
    $('#cart').on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        renderCart();
    });

    $('#cart').on('input', '.item-note', function() {
        const index = $(this).data('index');
        cart[index].note = $(this).val();
    });
    

    $('#addVehicleBtn').on('click', function() {
        if (!customer) return;
        showAddVehicleModal();
    });

    function showAddVehicleModal() {
        Swal.fire({
            title: 'Tambah Kendaraan Baru',
            html: `
                <input id="swal-license_plate" class="swal2-input" placeholder="Nomor Plat (Contoh: L 1234 AB)">
                <input id="swal-brand" class="swal2-input" placeholder="Merek (Contoh: Honda)">
                <input id="swal-model" class="swal2-input" placeholder="Model (Contoh: Vario 125)">
                <input id="swal-color" class="swal2-input" placeholder="Warna (Contoh: Merah)">`,
            confirmButtonText: 'Simpan Kendaraan',
            showCancelButton: true,
            preConfirm: () => {
                const license_plate = $('#swal-license_plate').val();
                if (!license_plate) Swal.showValidationMessage(`Nomor Plat tidak boleh kosong`);
                return {
                    customer_id: customer.id,
                    license_plate: license_plate,
                    brand: $('#swal-brand').val(),
                    model: $('#swal-model').val(),
                    color: $('#swal-color').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route('vehicles.store') }}', {
                    _token: '{{ csrf_token() }}',
                    ...result.value
                }, function(newVehicle) {
                    var displayText = `${newVehicle.license_plate} (${newVehicle.brand || ''} ${newVehicle.model || ''})`.trim();
                    var option = new Option(displayText, newVehicle.id, true, true);
                    $('#vehicle_id').append(option).trigger('change');
                    Swal.fire('Berhasil!', 'Kendaraan baru berhasil ditambahkan.', 'success');
                }).fail(() => Swal.fire('Error!', 'Gagal menyimpan kendaraan baru.', 'error'));
            }
        });
    }

    $('#vehicle_id').on('change', function() { vehicle_id = $(this).val(); });
    
    // ===================================================
    // FUNGSI PEMBAYARAN
    // ===================================================

    $('#payment_method').on('change', function() {
        if ($(this).val() === 'cash') $('#cash_payment_details').slideDown();
        else $('#cash_payment_details').slideUp();
    }).trigger('change');
    
    $('#amount_paid').on('input', calculateChange);

    function calculateChange() {
        const grandTotal = cart.reduce((total, item) => total + item.quantity * item.price, 0);
        const amountPaid = parseFloat($('#amount_paid').val()) || 0;
        let change = amountPaid - grandTotal;
        if (change < 0 || !amountPaid) change = 0;
        $('#change_due').text('Rp ' + change.toLocaleString('id-ID'));
    }

    $('#process_sale').on('click', function() {
        const grandTotal = cart.reduce((total, item) => total + (item.quantity * item.price), 0);
        const button = $(this);

        if (!customer) {
            Swal.fire({
                    title: 'Error!',
                    text: 'Silakan pilih pelanggan terlebih dahulu.',
                    icon: 'error',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
                    },
                    buttonsStyling: false
                });
            return;
        }
        if (cart.length === 0) {
            Swal.fire({
                    title: 'Error!',
                    text: 'Keranjang tidak boleh kosong.',
                    icon: 'error',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
                    },
                    buttonsStyling: false
                });
            return;
        }

        const paymentMethod = $('#payment_method').val();
        if (paymentMethod === 'cash') {
            const amountPaid = parseFloat($('#amount_paid').val()) || 0;
            if (amountPaid < grandTotal) {
                Swal.fire('Error', 'Uang tunai yang dibayarkan kurang dari total belanja.', 'error');
                return;
            }
            processAjaxSale(grandTotal);
        } else if (paymentMethod === 'qris') {
            generateQrCode(grandTotal);
        }
    });

    function generateQrCode(grandTotal) { // 1. Terima argumen 'grandTotal'
        if (grandTotal <= 0) { // 2. Gunakan 'grandTotal'
            Swal.fire('Error', 'Tidak bisa membuat QRIS untuk keranjang kosong.', 'error');
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
                amount: grandTotal // 3. Gunakan 'grandTotal'
            },
            success: function(response) {
                // Sembunyikan spinner dan tampilkan QR
                $('#qris-spinner').hide();
                $('#qris-image').attr('src', response.qr_code_url).show();
                $('#qris-amount').text('Rp ' + grandTotal.toLocaleString('id-ID')); // 4. Gunakan 'grandTotal'
                $('#qris-expiry').text('Berlaku hingga: ' + response.expiry_time);
                
                // SIMPAN ORDER ID DAN MULAI POLLING SETELAH DAPAT RESPON
                let currentQrisOrderId = response.order_id; 
                startQrisPolling(currentQrisOrderId);
            },
            error: function(xhr) { // Tambahkan 'xhr' untuk pesan error lebih detail
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.error : 'Gagal membuat kode QRIS. Silakan coba lagi.';
                Swal.fire('Error', errorMsg, 'error');
                $('#qrisModal').addClass('hidden');
                $('#payment_method').val('cash').trigger('change');
            }
        });
    }

    function startQrisPolling(orderId) {
        let isQrisPaid = false; 
        
        // Hentikan polling sebelumnya jika ada
        if (qrisPollingInterval) {
            clearInterval(qrisPollingInterval);
        }

        // Mulai polling baru setiap 3 detik
        qrisPollingInterval = setInterval(function() {

            // Jika sudah lunas, hentikan interval dan jangan lakukan apa-apa lagi
            if (isQrisPaid) { 
                stopQrisPolling();
                return;
            }

            $.get(`/pos/qris/status/${orderId}`, function(data) {
                // Jika pembayaran berhasil (settlement) dan belum diproses
                if (data.transaction_status === 'settlement' && !isQrisPaid) {
                    isQrisPaid = true; // Tandai sudah lunas untuk mencegah eksekusi ganda
                    
                    stopQrisPolling(); // Hentikan pengecekan
                    $('#qrisModal').addClass('hidden'); // Tutup modal QR

                    // --- INI PERUBAHAN UTAMANYA ---
                    // Langsung panggil fungsi untuk menyimpan data ke database,
                    // bukan mengklik tombol 'Bayar' lagi.
                    const grandTotal = cart.reduce((total, item) => total + (item.quantity * item.price), 0);
                    processAjaxSale(grandTotal, orderId); // Kirim orderId sebagai nomor invoice
                }
            }).fail(function() {
                // Hentikan polling jika transaksi tidak ditemukan (misal, kedaluwarsa)
                stopQrisPolling();
                // Opsional: Beri tahu kasir jika QR sudah kedaluwarsa
                Swal.fire('Info', 'Sesi pembayaran QRIS telah berakhir atau tidak ditemukan.', 'info');
                $('#qrisModal').addClass('hidden');
            });
        }, 3000); // Cek setiap 3 detik
    }

    function stopQrisPolling() {
        if (qrisPollingInterval) {
            clearInterval(qrisPollingInterval);
            qrisPollingInterval = null;
        }
    }

    function processAjaxSale(grandTotal, qrisOrderId = null) {
        $('#process_sale').prop('disabled', true).text('Processing...');
        
        const saleData = {
            customer_id: customer.id,
            vehicle_id: vehicle_id,
            payment_method: $('#payment_method').val(),
            items: cart.map(item => ({
                id: item.id,
                type: item.type,
                name: item.name,
                price: item.price,
                quantity: item.quantity,
                note: item.note || ''
            })),
            invoice_number: qrisOrderId || 'INV-' + Date.now(),
        };

        $.ajax({
            url: '{{ route("pos.store") }}',
            method: 'POST',
            data: JSON.stringify(saleData),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                Swal.fire({
                    title: 'Transaksi Berhasil!',
                    text: response.success,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Cetak Struk',
                    cancelButtonText: 'Transaksi Baru',
                    customClass: {
                        confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded',
                        cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        const receiptUrl = `/sales/${response.sale_id}/receipt`;
                        $('#receipt-iframe').attr('src', receiptUrl);
                        $('#receipt-iframe').on('load', function() {
                            this.contentWindow.print();
                        });
                    }
                    resetTransaction();
                });
            },
            error: function(xhr) {
                 Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.error || 'Terjadi kesalahan.',
                    icon: 'error',
                    confirmButtonText: 'Tutup',
                    customClass: {
                        confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded',
                    },
                    buttonsStyling: false
                });
                $('#process_sale').prop('disabled', false).text('Bayar');
            }
        });
    }

    function resetTransaction() {
        customer = null;
        vehicle_id = null;
        cart = [];
        $('#customer_search').val(null).trigger('change');
        $('#vehicle_section').hide();
        renderCart();
        $('#payment_method').val('cash').trigger('change');
        $('#amount_paid').val('');
        $('#process_sale').prop('disabled', false).text('Bayar');
    }
});

// ===================================================
// AUTOCOMPLETE PELANGGAN + MODAL SWEETALERT FIX
// ===================================================
let customer = null;
let typingTimeout = null;

$('#customer_input').on('input', function () {
    const query = $(this).val().trim();
    clearTimeout(typingTimeout);

    if (query.length < 1) {
        $('#customer_suggestions').hide().empty();
        return;
    }

    typingTimeout = setTimeout(() => {
        $.ajax({
            url: '{{ route("pos.customers.search") }}',
            data: { q: query },
            success: function (data) {
                const results = (data.length ? data : []).filter(item => 
                    item.name && item.name.toLowerCase().includes(query.toLowerCase())
                );
                const suggestionBox = $('#customer_suggestions').empty();

                if (results.length > 0) {
                    results.forEach(item => {
                        suggestionBox.append(`
                            <div class="p-2 hover:bg-blue-100 cursor-pointer text-sm select-customer" 
                                 data-id="${item.id}" 
                                 data-name="${item.name}" 
                                 data-phone="${item.phone_number || ''}">
                                 ${item.name} ${item.phone_number ? '(' + item.phone_number + ')' : ''}
                            </div>
                        `);
                    });
                }

                // Tombol tambah pelanggan baru
                suggestionBox.append(`
                    <div id="add_new_customer" 
                         class="p-2 bg-green-50 hover:bg-green-100 cursor-pointer text-sm text-green-700 font-semibold border-t">
                        ➕ Tambah pelanggan baru: "<span class="italic">${query}</span>"
                    </div>
                `);

                suggestionBox.show();
            }
        });
    }, 300);
});

// Klik pelanggan lama
$(document).on('click', '.select-customer', function () {
    const name = $(this).data('name');
    const id = $(this).data('id');
    const phone = $(this).data('phone');
    $('#customer_input').val(name);
    $('#customer_suggestions').hide();
    customer = { id, name, phone };
});

// Klik “Tambah pelanggan baru”
$(document).on('click', '#add_new_customer', function () {
    const typedName = $('#customer_input').val().trim();
    $('#new_customer_name').val(typedName);
    $('#new_customer_phone').val('');
    $('#addCustomerModal').removeClass('hidden');
    $('#customer_suggestions').hide();
});

// Tutup modal
$('#cancelAddCustomer').on('click', function () {
    $('#addCustomerModal').addClass('hidden');
});

// Simpan pelanggan baru
$('#saveNewCustomer').on('click', function () {
    const name = $('#new_customer_name').val().trim();
    const phone = $('#new_customer_phone').val().trim();

    if (!name) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama wajib diisi!',
            text: 'Silakan isi nama pelanggan terlebih dahulu.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    $.post('{{ route("customers.store") }}', {
        _token: '{{ csrf_token() }}',
        name: name,
        phone_number: phone
    })
    .done(function (newCustomer) {
        $('#addCustomerModal').addClass('hidden');
        $('#customer_input').val(newCustomer.name);
        customer = newCustomer;
        Swal.fire({
            icon: 'success',
            title: 'Pelanggan Berhasil Disimpan',
            text: `${newCustomer.name} telah ditambahkan ke daftar pelanggan.`,
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
    })
    .fail(function () {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menyimpan pelanggan baru.',
            confirmButtonColor: '#dc2626'
        });
    });
});

// Tutup suggestion saat klik di luar
$(document).on('click', function (e) {
    if (!$(e.target).closest('#customer_input, #customer_suggestions').length) {
        $('#customer_suggestions').hide();
    }
});

</script>
@endpush