// Menunggu seluruh halaman dimuat sebelum menjalankan skrip
document.addEventListener("DOMContentLoaded", () => {

    // --- Ambil Elemen-elemen Penting ---
    const video = document.getElementById('kamera-preview');
    const canvas = document.getElementById('kamera-canvas');
    const btnAbsenMasuk = document.getElementById('btn-absen-masuk');
    const btnAbsenKeluar = document.getElementById('btn-absen-keluar');
    const statusLokasi = document.getElementById('status-lokasi');
    
    // Ambil form tersembunyi
    const formAbsensi = document.getElementById('form-absensi');
    const inputLat = document.getElementById('input-latitude');
    const inputLon = document.getElementById('input-longitude');
    const inputTipe = document.getElementById('input-tipe-absen');
    const inputFoto = document.getElementById('input-foto-base64');

    let koordinatPengguna = null;
    let streamKamera = null;
    let tipeAbsenSaatIni = 'masuk'; // Default

    // --- Fungsi Utama 1: Memulai Kamera ---
    async function startCamera() {
        // Cek apakah tombol absen ada (jika sudah absen keluar, tombol tidak ada)
        if (!btnAbsenMasuk && !btnAbsenKeluar) {
            statusLokasi.textContent = "Absensi hari ini sudah selesai.";
            return;
        }

        // Hanya jalankan jika belum ada stream
        if (streamKamera) return; 

        try {
            statusLokasi.textContent = "Meminta izin kamera...";
            // Minta izin kamera (dan audio, meskipun tidak dipakai)
            streamKamera = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user' // Prioritaskan kamera depan
                },
                audio: false
            });
            
            video.srcObject = streamKamera;
            video.play();
            statusLokasi.textContent = "Kamera aktif. " + statusLokasi.textContent;
            
        } catch (err) {
            console.error("Error Kamera:", err);
            statusLokasi.textContent = "Error: Kamera tidak diizinkan atau tidak ditemukan.";
            btnAbsenMasuk.disabled = true;
            btnAbsenKeluar.disabled = true;
            btnAbsenMasuk.textContent = "Kamera Diblokir";
            btnAbsenKeluar.textContent = "Kamera Diblokir";
        }
    }

    // --- Fungsi Utama 2: Mendapatkan Lokasi ---
    function setupLokasi() {
        // Cek apakah tombol absen ada
        if (!btnAbsenMasuk && !btnAbsenKeluar) return; 

        if ('geolocation' in navigator) {
            statusLokasi.textContent = "Mendeteksi lokasi Anda...";
            navigator.geolocation.getCurrentPosition(
                (posisi) => {
                    // Sukses dapat lokasi
                    koordinatPengguna = {
                        latitude: posisi.coords.latitude,
                        longitude: posisi.coords.longitude
                    };
                    statusLokasi.textContent = "Lokasi ditemukan.";
                    
                    // Aktifkan tombol jika lokasi sudah siap
                    btnAbsenMasuk.disabled = false;
                    btnAbsenKeluar.disabled = false;
                    console.log("Lokasi:", koordinatPengguna);
                },
                (err) => {
                    // Gagal dapat lokasi
                    console.error("Error Lokasi:", err);
                    statusLokasi.textContent = "Error: Lokasi tidak diizinkan atau gagal dideteksi.";
                    btnAbsenMasuk.disabled = true;
                    btnAbsenKeluar.disabled = true;
                    btnAbsenMasuk.textContent = "Lokasi Diblokir";
                    btnAbsenKeluar.textContent = "Lokasi Diblokir";
                },
                {
                    enableHighAccuracy: true, // Minta akurasi tinggi
                    timeout: 10000,           // Batas waktu 10 detik
                    maximumAge: 0             // Jangan pakai cache
                }
            );
        } else {
            statusLokasi.textContent = "Error: Geolocation tidak didukung di browser ini.";
            btnAbsenMasuk.disabled = true;
            btnAbsenKeluar.disabled = true;
        }
    }

    // --- Fungsi Utama 3: Mengambil Foto (Capture) ---
    function ambilFoto() {
        // Set ukuran canvas sesuai ukuran video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // "Gambar" frame video saat ini ke canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Konversi gambar di canvas ke format data URL (Base64)
        // Mulai dengan kualitas 80%
        let base64 = canvas.toDataURL('image/jpeg', 0.8);
        
        // Check size - jika terlalu besar, compress lebih lanjut
        let sizeBytes = (base64.length * 0.75); // Rough estimate
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (sizeBytes > maxSize) {
            // Coba kualitas 60%
            base64 = canvas.toDataURL('image/jpeg', 0.6);
            sizeBytes = (base64.length * 0.75);
            
            if (sizeBytes > maxSize) {
                // Coba kualitas 40% (last resort)
                base64 = canvas.toDataURL('image/jpeg', 0.4);
            }
        }
        
        return base64;
    }

    // --- Fungsi Utama 4: Menghentikan Kamera ---
    function stopCamera() {
        if (streamKamera) {
            streamKamera.getTracks().forEach(track => track.stop());
            streamKamera = null;
        }
    }

    // --- Menjalankan Fungsi ---

    // 1. Cek apakah tombol absen ada di halaman
    if (btnAbsenMasuk && btnAbsenKeluar) {
        // --- Status awal tombol ---
        // Status absen user didapat dari atribut data-status pada salah satu tombol (set di PHP)
        // data-status: 'belum_masuk', 'sudah_masuk', 'sudah_keluar'
        const statusAbsen = btnAbsenMasuk.getAttribute('data-status') || 'belum_masuk';
        if (statusAbsen === 'belum_masuk') {
            btnAbsenMasuk.disabled = false;
            btnAbsenKeluar.disabled = true;
        } else if (statusAbsen === 'sudah_masuk') {
            btnAbsenMasuk.disabled = true;
            btnAbsenKeluar.disabled = false;
        } else if (statusAbsen === 'sudah_keluar') {
            // FIX: ALLOW MULTIPLE ABSEN KELUAR
            // User yang sudah absen keluar tetap bisa absen keluar lagi untuk update waktu
            btnAbsenMasuk.disabled = true;
            btnAbsenKeluar.disabled = false; // Aktifkan tombol keluar lagi
            btnAbsenKeluar.textContent = 'Update Absen Keluar';
            btnAbsenKeluar.style.background = '#FF9800'; // Warna berbeda untuk update
        } else {
            btnAbsenMasuk.disabled = true;
            btnAbsenKeluar.disabled = true;
        }
        setupLokasi();
        startCamera();
        // Handler Absen Masuk
        btnAbsenMasuk.addEventListener('click', async () => {
            if (!koordinatPengguna) {
                alert("Lokasi belum siap. Mohon tunggu atau izinkan lokasi.");
                return;
            }
            btnAbsenMasuk.disabled = true;
            btnAbsenMasuk.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
            let fotoBase64 = ambilFoto();
            inputLat.value = koordinatPengguna.latitude;
            inputLon.value = koordinatPengguna.longitude;
            inputTipe.value = 'masuk';
            inputFoto.value = fotoBase64;
            const formData = new FormData(formAbsensi);
            try {
                const response = await fetch('proses_absensi.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    btnAbsenMasuk.innerHTML = 'Absen Masuk';
                    btnAbsenKeluar.disabled = false;
                    btnAbsenMasuk.disabled = true;
                    statusLokasi.textContent = 'Silakan lakukan absen keluar saat pulang.';
                } else {
                    alert(result.message || 'Terjadi error.');
                    window.location.reload();
                }
            } catch (e) {
                alert('Gagal mengirim absensi. Silakan coba lagi.');
                window.location.reload();
            }
        });
        // Handler Absen Keluar
        btnAbsenKeluar.addEventListener('click', async () => {
            if (!koordinatPengguna) {
                alert("Lokasi belum siap. Mohon tunggu atau izinkan lokasi.");
                return;
            }
            btnAbsenKeluar.disabled = true;
            btnAbsenKeluar.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
            let fotoBase64 = ambilFoto();
            inputLat.value = koordinatPengguna.latitude;
            inputLon.value = koordinatPengguna.longitude;
            inputTipe.value = 'keluar';
            inputFoto.value = fotoBase64;
            const formData = new FormData(formAbsensi);
            try {
                const response = await fetch('proses_absensi.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'success') {
                    // Cek apakah ada pesan custom (untuk update absen keluar)
                    if (result.message) {
                        statusLokasi.textContent = result.message;
                    }
                    
                    // Jika next = konfirmasi_lembur, tampilkan modal
                    if (result.next === 'konfirmasi_lembur') {
                        // Tampilkan modal konfirmasi lembur
                        document.getElementById('modal-lembur').style.display = 'flex';
                        // Simpan absen_id jika ada
                        let absenId = result.absen_id ? result.absen_id : '';
                        // Handler tombol modal
                        document.getElementById('btn-lembur-ya').onclick = function() {
                            if (absenId) {
                                window.location.href = 'konfirmasi_lembur.php?absen_id=' + encodeURIComponent(absenId);
                            } else {
                                window.location.href = 'konfirmasi_lembur.php';
                            }
                        };
                        document.getElementById('btn-lembur-tidak').onclick = function() {
                            window.location.href = 'absen.php';
                        };
                    } else if (result.next === 'done') {
                        // Absen keluar selesai tanpa lembur
                        btnAbsenKeluar.innerHTML = '✓ Selesai';
                        btnAbsenKeluar.disabled = false; // Tetap aktifkan untuk update lagi
                        btnAbsenKeluar.style.background = '#4CAF50'; // Hijau untuk selesai
                        
                        // Jika ini update (ada message), tunjukkan pesan
                        if (!result.message) {
                            statusLokasi.textContent = 'Absensi hari ini selesai. Terima kasih!';
                        }
                        
                        // Jangan stop kamera agar bisa update lagi
                        // stopCamera();
                    }
                    
                    btnAbsenKeluar.innerHTML = 'Absen Keluar';
                } else if (result.status === 'error' && (result.message === 'Not logged in' || result.message.toLowerCase().includes('unauthorized'))) {
                    alert('Sesi Anda telah habis. Silakan login kembali.');
                    window.location.href = 'index.php?error=notloggedin';
                } else {
                    alert(result.message || 'Terjadi error.');
                    btnAbsenKeluar.disabled = false; // Re-enable untuk coba lagi
                    btnAbsenKeluar.innerHTML = 'Absen Keluar';
                }
            } catch (e) {
                alert('Gagal mengirim absensi. Silakan coba lagi.');
                window.location.reload();
            }
        });
    } else {
        statusLokasi.textContent = "Absensi hari ini sudah selesai.";
    }

    // --- Fitur: Ubah tombol otomatis setelah absen masuk tanpa reload ---
    window.ubahTombolAbsen = function(tipe) {
        if (!btnAbsenMasuk && !btnAbsenKeluar) return;
        if (tipe === 'keluar') {
            btnAbsenKeluar.setAttribute('data-tipe', 'keluar');
            btnAbsenKeluar.textContent = 'Absen Keluar';
            btnAbsenKeluar.disabled = false;
            // startCamera(); // Jangan reset kamera agar tidak gelap
        } else if (tipe === 'done') {
            btnAbsenMasuk.setAttribute('data-tipe', 'done');
            btnAbsenMasuk.textContent = 'Absensi Selesai';
            btnAbsenMasuk.disabled = true;
            stopCamera();
        }
    }

    // Membersihkan stream kamera jika pengguna meninggalkan halaman
    window.addEventListener('beforeunload', stopCamera);
});