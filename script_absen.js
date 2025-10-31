// Menunggu seluruh halaman dimuat sebelum menjalankan skrip
document.addEventListener("DOMContentLoaded", () => {

    // --- Ambil Elemen-elemen Penting ---
    const video = document.getElementById('kamera-preview');
    const canvas = document.getElementById('kamera-canvas');
    const btnAbsen = document.getElementById('btn-absen');
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
        if (!btnAbsen) {
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
            btnAbsen.disabled = true;
            btnAbsen.textContent = "Kamera Diblokir";
        }
    }

    // --- Fungsi Utama 2: Mendapatkan Lokasi ---
    function setupLokasi() {
        // Cek apakah tombol absen ada
        if (!btnAbsen) return; 

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
                    btnAbsen.disabled = false;
                    console.log("Lokasi:", koordinatPengguna);
                },
                (err) => {
                    // Gagal dapat lokasi
                    console.error("Error Lokasi:", err);
                    statusLokasi.textContent = "Error: Lokasi tidak diizinkan atau gagal dideteksi.";
                    btnAbsen.disabled = true;
                    btnAbsen.textContent = "Lokasi Diblokir";
                },
                {
                    enableHighAccuracy: true, // Minta akurasi tinggi
                    timeout: 10000,           // Batas waktu 10 detik
                    maximumAge: 0             // Jangan pakai cache
                }
            );
        } else {
            statusLokasi.textContent = "Error: Geolocation tidak didukung di browser ini.";
            btnAbsen.disabled = true;
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
        // Gunakan 'image/jpeg' untuk ukuran file yang lebih kecil
        return canvas.toDataURL('image/jpeg', 0.8); // Kualitas 80%
    }

    // --- Fungsi Utama 4: Menghentikan Kamera ---
    function stopCamera() {
        if (streamKamera) {
            streamKamera.getTracks().forEach(track => track.stop());
            streamKamera = null;
        }
    }

    // --- Menjalankan Fungsi ---

    // 1. Cek dulu apakah tombol absen ada di halaman
    if (btnAbsen) {
        // Tombol ada, berarti belum absen keluar

        // 2. Nonaktifkan tombol saat halaman dimuat
        btnAbsen.disabled = true;
        
        // 3. Ambil tipe absen dari tombol
        tipeAbsenSaatIni = btnAbsen.getAttribute('data-tipe');
        
        // 4. Jalankan deteksi lokasi
        setupLokasi();

        // 5. Jika absen masuk, jalankan kamera. Jika absen keluar, kamera tidak perlu.
        if (tipeAbsenSaatIni === 'masuk') {
            startCamera();
        } else {
            // Untuk absen keluar, lokasi saja sudah cukup
            statusLokasi.textContent = "Mendeteksi lokasi Anda untuk absen keluar...";
            // (Kita tetap biarkan tombol disabled sampai lokasi didapat)
        }

        // 6. Tambahkan Event Listener ke Tombol
        btnAbsen.addEventListener('click', () => {
            
            // Pastikan lokasi sudah ada
            if (!koordinatPengguna) {
                alert("Lokasi belum siap. Mohon tunggu atau izinkan lokasi.");
                return;
            }
            
            // Tampilkan loading
            btnAbsen.disabled = true;
            btnAbsen.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
            
            let fotoBase64 = null;
            
            // Ambil foto HANYA jika absen masuk
            if (tipeAbsenSaatIni === 'masuk') {
                fotoBase64 = ambilFoto();
                stopCamera(); // Matikan kamera setelah foto diambil
            }

            // Isi form tersembunyi
            inputLat.value = koordinatPengguna.latitude;
            inputLon.value = koordinatPengguna.longitude;
            inputTipe.value = tipeAbsenSaatIni;
            inputFoto.value = fotoBase64; // Akan kosong (null) jika absen keluar, dan itu tidak apa-apa
            
            // Kirim form
            formAbsensi.submit();
        });
        
    } else {
        // Tombol tidak ada, berarti sudah absen keluar
        statusLokasi.textContent = "Absensi hari ini sudah selesai.";
    }

    // Membersihkan stream kamera jika pengguna meninggalkan halaman
    window.addEventListener('beforeunload', stopCamera);
});