{{-- Floating Button --}}
<button id="supportBtn"
    class="fixed bottom-6 right-6 bg-blue-600 text-white w-14 h-14 flex items-center justify-center rounded-full shadow-xl hover:bg-blue-700 z-50 transition">
    <i class="fa-solid fa-comment-dots text-2xl"></i>
</button>

{{-- Modal Support / Chat --}}
<div id="supportPopup"
    class="hidden fixed bottom-24 right-6 bg-white w-80 rounded-xl shadow-xl border border-gray-200 z-50 transition-all">

    <div class="flex justify-between items-center px-4 py-3 border-b">
        <h3 class="text-sm font-semibold text-gray-800">Bantuan Pelanggan</h3>
        <button id="closePopup" class="text-gray-500 hover:text-red-500 text-lg">
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- === FORM TIKET BARU === --}}
    <div id="supportFormContainer" class="px-4 py-3 text-sm text-gray-600">
        <p class="mb-2">Kirim pesan kepada tim kami, biasanya membalas dalam beberapa menit.</p>

        <form id="supportForm" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-700">Subjek</label>
                <input type="text" name="subject"
                    class="w-full border rounded-lg p-2 text-sm focus:ring focus:ring-blue-200 focus:border-blue-400"
                    placeholder="Masukkan subjek..." required>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-700">Pesan</label>
                <textarea name="message" rows="3"
                    class="w-full border rounded-lg p-2 text-sm focus:ring focus:ring-blue-200 focus:border-blue-400"
                    placeholder="Tuliskan pesan bantuan..." required></textarea>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 rounded-lg">
                Kirim Pesan
            </button>
        </form>
    </div>

    {{-- === CHAT VIEW === --}}
    <div id="supportChatContainer" class="hidden flex flex-col h-96">
        <div id="chatMessages" class="flex-1 overflow-y-auto p-3 space-y-2 bg-gray-50"></div>

        <form id="chatReplyForm" class="flex border-t p-2">
            @csrf
            <input type="text" name="message"
                class="flex-1 border rounded-lg p-2 text-sm focus:ring focus:ring-blue-200 focus:border-blue-400"
                placeholder="Tulis pesan..." required>
            <button class="ml-2 px-3 bg-blue-600 text-white rounded-lg">Kirim</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const supportBtn = document.getElementById('supportBtn');
const supportPopup = document.getElementById('supportPopup');
const closePopup = document.getElementById('closePopup');
const supportForm = document.getElementById('supportForm');
const formContainer = document.getElementById('supportFormContainer');
const chatContainer = document.getElementById('supportChatContainer');
const chatMessages = document.getElementById('chatMessages');
const chatReplyForm = document.getElementById('chatReplyForm');

// === Toggle Popup ===
supportBtn.addEventListener('click', () => supportPopup.classList.toggle('hidden'));
closePopup.addEventListener('click', () => supportPopup.classList.add('hidden'));
document.addEventListener('click', (e) => {
    if (!supportPopup.contains(e.target) && !supportBtn.contains(e.target)) {
        supportPopup.classList.add('hidden');
    }
});

// === Kirim Tiket Baru ===
supportForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    const response = await fetch('{{ route('support.store') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    });

    const result = await response.json();

    if (result.success && result.ticket_id) {
        // simpan ID tiket saat ini
        window.currentTicketId = result.ticket_id;

        // ubah tampilan jadi mode chat
        formContainer.classList.add('hidden');
        chatContainer.classList.remove('hidden');

        // tampilkan pesan pertama user
        chatMessages.innerHTML = `
            <div class="text-right">
                <div class="inline-block bg-blue-600 text-white p-2 rounded-lg text-sm mb-1">
                    ${formData.get('message')}
                </div>
            </div>
        `;

        // mulai polling balasan
        startChatPolling(result.ticket_id);
    }
});

// === Kirim Pesan Balasan ===
chatReplyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = e.target.message.value;
    e.target.reset();

    // tampilkan pesan di sisi user
    chatMessages.innerHTML += `
        <div class="text-right">
            <div class="inline-block bg-blue-600 text-white p-2 rounded-lg text-sm mb-1">
                ${message}
            </div>
        </div>
    `;
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // kirim ke backend
    await fetch(`{{ url('/support') }}/${window.currentTicketId}/reply`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message })
    });
});

// === Polling Balasan Superadmin ===
function startChatPolling(ticketId) {
    const pollingInterval = setInterval(async () => {
        const res = await fetch(`{{ url('/support') }}/${ticketId}/replies`);
        const data = await res.json();

        // Jika API juga mengembalikan status tiket
        if (data.ticket_status === 'resolved') {
            clearInterval(pollingInterval); // hentikan polling
            Swal.fire({
                icon: 'info',
                title: 'Tiket Selesai',
                text: 'Tiket bantuan ini telah ditandai selesai oleh admin.',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'bottom-end',
                background: '#fff',
                color: '#333',
                customClass: {
                    popup: 'shadow-lg rounded-lg'
                }
            });
            
            // Reset tampilan kembali ke form awal
            chatContainer.classList.add('hidden');
            formContainer.classList.remove('hidden');
            supportForm.reset();
            chatMessages.innerHTML = '';
            window.currentTicketId = null;
            return;
        }

        // Jika masih aktif, tampilkan semua pesan
        chatMessages.innerHTML = '';
        data.replies.forEach(msg => {
            const isUser = msg.user_id === {{ auth()->id() }};
            chatMessages.innerHTML += `
                <div class="${isUser ? 'text-right' : 'text-left'}">
                    <div class="inline-block ${isUser ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800'} p-2 rounded-lg text-sm mb-1">
                        ${msg.message}
                    </div>
                </div>
            `;
        });
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }, 10000);
}
</script>
@endpush
