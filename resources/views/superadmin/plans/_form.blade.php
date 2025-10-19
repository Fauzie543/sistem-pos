<div class="bg-white p-6 rounded-md shadow-sm">
    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="space-y-6 max-w-4xl mx-auto">
        {{-- Informasi Dasar --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Paket*</label>
                <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" class="mt-1 block w-full" required>
            </div>
            <div>
                <label for="key" class="block text-sm font-medium text-gray-700">Kunci Unik (Key)*</label>
                <input type="text" name="key" id="key" value="{{ old('key', $plan->key) }}" placeholder="e.g., bengkel_pro (tanpa spasi)" class="mt-1 block w-full" required>
            </div>
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi Singkat</label>
            <input type="text" name="description" id="description" value="{{ old('description', $plan->description) }}" class="mt-1 block w-full">
        </div>
       <div class="border-t pt-6">
            <h3 class="text-lg font-medium">Fitur yang Termasuk</h3>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($features as $feature)
                    <label class="flex items-center">
                        <input type="checkbox" name="feature_ids[]" value="{{ $feature->id }}" class="rounded"
                            @if(in_array($feature->id, old('feature_ids', ($plan->exists && $plan->features) ? $plan->features->pluck('id')->toArray() : []))) checked @endif>
                        <span class="ms-2 text-sm">{{ $feature->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Tingkatan Harga (Tiers) --}}
        <div class="border-t pt-6">
            <h3 class="text-lg font-medium">Tingkatan Harga</h3>
            <div id="tiers-container" class="space-y-4 mt-4">
                {{-- Loop untuk data yang sudah ada (saat edit) atau untuk old input --}}
                @foreach(old('tiers', $plan->tiers->isEmpty() ? [['duration_months' => 1, 'price' => ''], ['duration_months' => 6, 'price' => ''], ['duration_months' => 12, 'price' => '']] : $plan->tiers->toArray()) as $index => $tier)
                    <div class="grid grid-cols-3 gap-4 items-center tier-row">
                        <input type="hidden" name="tiers[{{ $index }}][key]" value="{{ old('key', $plan->key) }}_{{ $tier['duration_months'] }}bulan">
                        <div>
                            <label class="block text-sm font-medium">Durasi (Bulan)*</label>
                            <input type="number" name="tiers[{{ $index }}][duration_months]" value="{{ $tier['duration_months'] }}" class="mt-1 block w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Harga (Rp)*</label>
                            <input type="number" name="tiers[{{ $index }}][price]" value="{{ $tier['price'] }}" class="mt-1 block w-full" required>
                        </div>
                        <div class="pt-5">
                            <button type="button" class="text-red-500 hover:text-red-700 remove-tier-btn">&times; Hapus</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-tier-btn" class="mt-4 text-sm text-blue-600 hover:underline">+ Tambah Tingkatan Harga</button>
        </div>
        
        {{-- Status Aktif --}}
        <div class="border-t pt-4">
            <label for="is_active" class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded" @checked(old('is_active', $plan->is_active))>
                <span class="ms-2 text-sm text-gray-600">Aktifkan Paket Ini</span>
            </label>
        </div>

        <div class="flex justify-end pt-4">
            <a href="{{ route('superadmin.plans.index') }}" class="bg-gray-200 text-gray-800 font-bold py-2 px-4 rounded mr-2">Batal</a>
            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded">Simpan Paket</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function() {
        let tierIndex = {{ count(old('tiers', $plan->tiers->isEmpty() ? [1,1,1] : $plan->tiers->toArray())) }};
        const planKeyInput = $('#key');

        function generateTierKey(duration) {
            const planKey = planKeyInput.val() || 'newplan';
            return `${planKey}_${duration}bulan`;
        }

        $('#add-tier-btn').on('click', function() {
            const newTier = `
                <div class="grid grid-cols-3 gap-4 items-center tier-row">
                    <input type="hidden" name="tiers[${tierIndex}][key]" value="">
                    <div>
                        <label class="block text-sm font-medium">Durasi (Bulan)*</label>
                        <input type="number" name="tiers[${tierIndex}][duration_months]" class="mt-1 block w-full duration-input" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Harga (Rp)*</label>
                        <input type="number" name="tiers[${tierIndex}][price]" class="mt-1 block w-full" required>
                    </div>
                    <div class="pt-5">
                        <button type="button" class="text-red-500 hover:text-red-700 remove-tier-btn">&times; Hapus</button>
                    </div>
                </div>
            `;
            $('#tiers-container').append(newTier);
            tierIndex++;
        });

        $('#tiers-container').on('click', '.remove-tier-btn', function() {
            $(this).closest('.tier-row').remove();
        });

        // Update hidden key input when duration changes
        $('#tiers-container').on('change', '.duration-input', function() {
            const duration = $(this).val();
            const keyInput = $(this).closest('.tier-row').find('input[type=hidden]');
            keyInput.val(generateTierKey(duration));
        });
    });
</script>
@endpush