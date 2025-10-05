<div id="vehicleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h3 id="modal_title" class="text-xl font-semibold"></h3>
            <button class="text-gray-500 hover:text-gray-800 text-2xl close-modal">&times;</button>
        </div>
        <form id="vehicleForm">
            @csrf
            <input type="hidden" name="_method" id="form_method" value="POST">
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
            
            <div class="space-y-4">
                <div>
                    <label for="license_plate" class="block text-sm font-medium text-gray-700">No Kendaraan</label>
                    <input type="text" name="license_plate" id="license_plate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span id="license_plate_error" class="text-red-500 text-xs error-message"></span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                        <input type="text" name="brand" id="brand" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="brand_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                        <input type="text" name="model" id="model" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <span id="model_error" class="text-red-500 text-xs error-message"></span>
                    </div>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Tahun</label>
                    <input type="number" name="year" id="year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span id="year_error" class="text-red-500 text-xs error-message"></span>
                </div>
            </div>

            <div class="flex justify-end items-center border-t pt-4 mt-4">
                <button type="button" id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">Batal</button>
                <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"></button>
            </div>
        </form>
    </div>
</div>