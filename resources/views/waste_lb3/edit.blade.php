<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('waste-lb3.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Proses Serah Terima & Tanda Tangan TPS</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                
                <form id="signature-form" action="{{ route('waste-lb3.update', $wasteLb3->id) }}" method="POST" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden input untuk menampung gambar base64 -->
                    <input type="hidden" name="signature" id="signature-input">
                    <!-- Status otomatis diubah menjadi Received -->
                    <input type="hidden" name="status" value="Received">

                    <!-- Ringkasan Request -->
                    <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 mb-8 flex justify-between items-center">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Menyerahkan Limbah B3:</p>
                            <h3 class="text-xl font-black text-gray-900">{{ $wasteLb3->waste_type }}</h3>
                            <p class="text-sm font-medium text-gray-600 mt-1">Volume: <span class="text-red-600 font-bold">{{ $wasteLb3->quantity }} {{ $wasteLb3->unit }}</span> | Dari: {{ $wasteLb3->source_location }}</p>
                        </div>
                        <div class="text-right hidden sm:block">
                            <p class="text-xs text-gray-500">Pemohon:</p>
                            <p class="font-bold text-gray-900">{{ $wasteLb3->user->name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- Identitas Penerima -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Petugas TPS (Penerima) <span class="text-red-500">*</span></label>
                            <input type="text" name="receiver_name" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="Ketik nama petugas...">
                        </div>

                        <!-- Area Canvas Tanda Tangan -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanda Tangan Penerima <span class="text-red-500">*</span></label>
                            
                            <div class="border-2 border-dashed border-gray-300 rounded-lg overflow-hidden bg-gray-50 relative">
                                <canvas id="signature-pad" class="w-full bg-white touch-none cursor-crosshair" height="200"></canvas>
                                
                                <button type="button" id="clear-signature" class="absolute bottom-2 right-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold py-1 px-3 rounded shadow-sm transition">
                                    Hapus / Ulangi
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 italic">* Gunakan mouse atau sentuh layar untuk tanda tangan.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <a href="{{ route('waste-lb3.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition mr-3">Batal</a>
                        <button type="submit" id="submit-btn" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Terima & Simpan Tanda Tangan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Script Logika Canvas -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById('signature-pad');
            const ctx = canvas.getContext('2d');
            const form = document.getElementById('signature-form');
            const signatureInput = document.getElementById('signature-input');
            const clearBtn = document.getElementById('clear-signature');
            let isDrawing = false;
            let hasDrawn = false;

            // Setup Canvas Size & Background
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                ctx.scale(ratio, ratio);
                clearCanvas();
            }
            
            function clearCanvas() {
                ctx.fillStyle = "#ffffff";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                hasDrawn = false;
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas();

            // Konfigurasi Pena
            ctx.strokeStyle = '#0f172a'; // Warna tinta (gelap)
            ctx.lineWidth = 3; // Ketebalan tinta
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';

            // Mendapatkan posisi kursor/jari
            function getPosition(event) {
                const rect = canvas.getBoundingClientRect();
                const clientX = event.clientX || (event.touches && event.touches[0].clientX);
                const clientY = event.clientY || (event.touches && event.touches[0].clientY);
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function startDrawing(event) {
                isDrawing = true;
                hasDrawn = true;
                const pos = getPosition(event);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                event.preventDefault(); // Mencegah scrolling saat TTD di HP
            }

            function draw(event) {
                if (!isDrawing) return;
                const pos = getPosition(event);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                event.preventDefault();
            }

            function stopDrawing() {
                isDrawing = false;
            }

            // Mouse Events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // Touch Events (Untuk HP/Tablet)
            canvas.addEventListener('touchstart', startDrawing, {passive: false});
            canvas.addEventListener('touchmove', draw, {passive: false});
            canvas.addEventListener('touchend', stopDrawing);

            // Tombol Clear
            clearBtn.addEventListener('click', clearCanvas);

            // Saat Form Disubmit
            form.addEventListener('submit', function(e) {
                if (!hasDrawn) {
                    e.preventDefault();
                    alert("Tanda tangan tidak boleh kosong!");
                    return;
                }
                // Ubah kanvas menjadi gambar base64 PNG dan masukkan ke hidden input
                signatureInput.value = canvas.toDataURL("image/png");
            });
        });
    </script>
</x-app-layout>