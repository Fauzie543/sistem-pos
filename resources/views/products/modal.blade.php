<div id="productModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-3xl">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 id="modal_title" class="text-xl font-semibold">Add New Product</h3>
            <button class="text-gray-500 hover:text-gray-800 text-2xl close-modal">&times;</button>
        </div>

        <form id="productForm">
            @csrf
            <input type="hidden" name="_method" id="form_method" value="POST">

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Produk</label>
                        <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="name_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <span id="category_id_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="purchase_price" class="block text-sm font-medium text-gray-700">Harga Beli (Rp)</label>
                        <input type="number" name="purchase_price" id="purchase_price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="purchase_price_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                    <div>
                        <label for="selling_price" class="block text-sm font-medium text-gray-700">Harga Jual (Rp)</label>
                        <input type="number" name="selling_price" id="selling_price" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="selling_price_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock" id="stock" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="stock_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                     <div>
                        <label for="unit" class="block text-sm font-medium text-gray-700">Unit (e.g., pcs, botol, set)</label>
                        <input type="text" name="unit" id="unit" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="unit_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU (Stock Keeping Unit)</label>
                        <input type="text" name="sku" id="sku" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="sku_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                    <div>
                        <label for="storage_location" class="block text-sm font-medium text-gray-700">Tempat Penyimpanan (e.g., Rak A1)</label>
                        <input type="text" name="storage_location" id="storage_location" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="storage_location_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
            </div>

            <div class="flex justify-end items-center border-t pt-4 mt-4">
                <button type="button" id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                    Batal
                </button>
                <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>