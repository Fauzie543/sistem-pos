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
                <div class="item-card cursor-pointer border rounded-lg p-3 flex flex-col h-24 hover:border-blue-500 hover:shadow-lg transition-all" 
                     data-item-id="{{ $item->type }}-{{ $item->id }}" 
                     data-category-id="{{ $item->category_id }}"
                     data-name="{{ strtolower($item->name) }}">
                    <div>
                        <p class="font-semibold text-sm truncate">{{ $item->name }}</p>
                        <span class="text-xs {{ $item->type == 'product' ? 'text-green-600' : 'text-purple-600' }}">{{ $item->type == 'product' ? 'Produk' : 'Jasa' }}</span>
                    </div>
                    <p class="text-right font-bold text-gray-800 mt-2">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
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
            <label for="customer_search" class="block text-sm font-medium text-gray-700">Pelanggan</label>
            <select id="customer_search" class="mt-1 block w-full"></select>
            @if(auth()->user()->company && auth()->user()->company->featureEnabled('services'))
                <div id="vehicle_section" class="mt-2 hidden">
                    <div class="flex justify-between items-center">
                        <label for="vehicle_id" class="block text-sm font-medium text-gray-700">Kendaraan</label>
                        <button id="addVehicleBtn" class="text-blue-600 hover:underline text-xs font-bold">+ Tambah</button>
                    </div>
                    <select name="vehicle_id" id="vehicle_id" class="mt-1 text-sm block w-full border-gray-300 rounded-md shadow-sm"></select>
                </div>
            @endif
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

<iframe id="receipt-iframe" style="display:none;"></iframe>



@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    let customer = null;
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
    
    // ===================================================
    // FUNGSI PELANGGAN & KENDARAAN
    // ===================================================
    $('#customer_search').select2({
        placeholder: 'Pilih pelanggan atau ketik untuk mencari...',
        minimumInputLength: 3,
        ajax: {
            url: '{{ route("pos.customers.search") }}',
            dataType: 'json',
            delay: 250,
            processResults: (data) => ({
                results: $.map(data, (item) => ({
                    text: item.name + (item.phone_number ? ` (${item.phone_number})` : ''),
                    id: item.id,
                    'data-customer': item 
                }))
            }),
        },
        tags: true, // Izinkan inputan bebas
        createTag: function (params) {
            var term = $.trim(params.term);
            if (term === '') return null;
            return {
                id: 'new:' + term,
                text: `➕ Tambah Pelanggan Baru: "${term}"`,
            };
        }
    });

    $('#customer_search').on('select2:select', function (e) {
        var customerData = e.params.data['data-customer'] || { id: e.params.data.id };

        if (String(customerData.id).startsWith('new:')) {
            const newName = String(customerData.id).split(':')[1];
            
            Swal.fire({
                title: 'Tambah Pelanggan Baru',
                html: `<input id="swal-name" class="swal2-input" value="${newName}" placeholder="Nama Pelanggan"><input id="swal-phone" class="swal2-input" placeholder="Nomor Telepon (Opsional)">`,
                confirmButtonText: 'Simpan',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                showCancelButton: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded',
                    cancelButton: 'bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2'
                },
                preConfirm: () => {
                    const name = $('#swal-name').val();
                    if (!name) Swal.showValidationMessage(`Nama tidak boleh kosong`);
                    return { name: name, phone_number: $('#swal-phone').val() };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('{{ route('customers.store') }}', {
                        _token: '{{ csrf_token() }}',
                        ...result.value
                    }, function(newCustomer) {
                        newCustomer.vehicles = [];
                        setCustomer(newCustomer);
                        var option = new Option(newCustomer.name + (newCustomer.phone_number ? ` (${newCustomer.phone_number})` : ''), newCustomer.id, true, true);
                        $('#customer_search').append(option).trigger('change');
                        
                        @if(auth()->user()->company && auth()->user()->company->featureEnabled('services'))
                            showAddVehicleModal();
                        @endif
                    })
                    .fail(() => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menyimpan pelanggan baru.',
                            icon: 'error',
                            buttonsStyling: false, // <-- Matikan style default
                            customClass: { // <-- Terapkan kelas Tailwind Anda
                                confirmButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded'
                            }
                        });
                    })
                } else {
                    $('#customer_search').val(null).trigger('change');
                    $('#vehicle_section').hide();
                    customer = null;
                }
            });
        } else {
            setCustomer(customerData);
        }
    });
    
    function setCustomer(data) {
        customer = data;
        @if(auth()->user()->company && auth()->user()->company->featureEnabled('services'))
            $('#vehicle_section').show();
            $('#vehicle_id').html('<option value="">-- Pilih Kendaraan --</option>');
            if(customer.vehicles && customer.vehicles.length > 0) {
                customer.vehicles.forEach(v => $('#vehicle_id').append(`<option value="${v.id}">${v.license_plate} (${v.brand} ${v.model})</option>`));
            }
        @endif
    }

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
            Swal.fire('Error', 'Silakan pilih pelanggan terlebih dahulu.', 'error');
            return;
        }
        if (cart.length === 0) {
            Swal.fire('Error', 'Keranjang tidak boleh kosong.', 'error');
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
                Swal.fire('Error!', xhr.responseJSON.error || 'Terjadi kesalahan.', 'error');
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
</script>
@endpush