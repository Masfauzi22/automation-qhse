{{--
    Script pemilih foto + kompres otomatis di browser sebelum upload.
    Dipakai di: Lapor Hazard, Tindak Lanjut PIC, dan form Safety Campaign.

    Pemakaian:
        <div x-data="photoPicker(3, 5120)">                         ← foto bukti (maks 1600 px)
        <div x-data="photoPicker(5, 5120, { maxSide: 2000, quality: 0.85 })">  ← poster (lebih tajam)
            <input type="file" ... @change="pick($event)">
            <p x-show="compressing">Mengompres foto...</p>
            <p x-show="info" x-text="info"></p>
            <p x-show="error" x-text="error"></p>
            <template x-for="p in previews"> <img :src="p.url"> </template>
        </div>

    Taruh <x-photo-picker-script /> sekali di halaman (aman walau terpanggil lebih dari sekali).
--}}
@once
<script>
    (function () {
        // Jumlah foto yang sedang dikompres; form tidak boleh terkirim selama masih proses
        let pending = 0;
        const waiters = [];
        const done = () => { if (--pending === 0) waiters.splice(0).forEach(fn => fn()); };
        const idle = () => pending === 0 ? Promise.resolve() : new Promise(r => waiters.push(r));

        const formatSize = (bytes) => bytes >= 1048576
            ? (bytes / 1048576).toFixed(1) + ' MB'
            : Math.max(1, Math.round(bytes / 1024)) + ' KB';

        async function loadImage(file) {
            // createImageBitmap ikut membaca orientasi EXIF (foto HP tidak jadi miring)
            if ('createImageBitmap' in window) {
                try { return await createImageBitmap(file, { imageOrientation: 'from-image' }); } catch (e) { /* lanjut ke cara lama */ }
            }
            return await new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => { URL.revokeObjectURL(img.src); resolve(img); };
                img.onerror = reject;
                img.src = URL.createObjectURL(file);
            });
        }

        // Perkecil sisi terpanjang ke maxSide px dan simpan sebagai JPEG
        async function compressImage(file, { maxSide = 1600, quality = 0.8 } = {}) {
            if (! file.type.startsWith('image/') || file.type === 'image/gif') return file;

            const img   = await loadImage(file);
            const w     = img.width, h = img.height;
            const scale = Math.min(1, maxSide / Math.max(w, h));

            const canvas = document.createElement('canvas');
            canvas.width  = Math.round(w * scale);
            canvas.height = Math.round(h * scale);
            const ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';                     // latar putih untuk PNG transparan
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            if (img.close) img.close();

            const blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', quality));

            // Kalau hasilnya malah lebih besar (foto sudah kecil), pakai file asli
            if (! blob || (blob.size >= file.size && scale === 1)) return file;

            const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';
            return new File([blob], name, { type: 'image/jpeg', lastModified: Date.now() });
        }

        window.compressImage = compressImage;

        window.photoPicker = function (max, maxKb, options = {}) {
            return {
                previews: [],
                error: '',
                info: '',
                compressing: false,

                async pick(event) {
                    const input = event.target;
                    const files = Array.from(input.files);
                    this.error = '';
                    this.info = '';
                    this.previews.forEach(p => URL.revokeObjectURL(p.url));
                    this.previews = [];

                    if (! files.length) return;

                    if (files.length > max) {
                        this.error = `Maksimal ${max} foto. Pilih ulang.`;
                        input.value = '';
                        return;
                    }

                    // Kompres dulu, baru cek ukuran
                    let result = files;
                    const canReplace = typeof DataTransfer !== 'undefined';
                    if (canReplace) {
                        pending++;
                        this.compressing = true;
                        try {
                            result = await Promise.all(files.map(f => compressImage(f, options).catch(() => f)));
                            const dt = new DataTransfer();
                            result.forEach(f => dt.items.add(f));
                            input.files = dt.files; // file yang dikirim ke server = hasil kompres
                        } catch (e) {
                            result = files;         // browser lama: kirim file asli
                        } finally {
                            this.compressing = false;
                            done();
                        }
                    }

                    const tooBig = result.find(f => f.size > maxKb * 1024);
                    if (tooBig) {
                        this.error = `Ukuran "${tooBig.name}" melebihi ${Math.round(maxKb / 1024)} MB. Pilih ulang.`;
                        input.value = '';
                        return;
                    }

                    const before = files.reduce((s, f) => s + f.size, 0);
                    const after  = result.reduce((s, f) => s + f.size, 0);
                    if (after < before) {
                        this.info = `Foto dikompres otomatis: ${formatSize(before)} → ${formatSize(after)}`;
                    }

                    this.previews = result.map(f => ({ url: URL.createObjectURL(f), name: f.name }));
                },
            };
        };

        // Kalau user menekan Kirim saat foto masih dikompres, tunggu sampai selesai lalu kirim otomatis
        document.addEventListener('submit', async (e) => {
            if (pending === 0 || e.target.dataset.waitedForPhotos) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            await idle();
            e.target.dataset.waitedForPhotos = '1';
            e.target.requestSubmit(e.submitter || undefined);
            delete e.target.dataset.waitedForPhotos;
        }, true);
    })();
</script>
@endonce