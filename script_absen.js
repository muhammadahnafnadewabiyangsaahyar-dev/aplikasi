document.addEventListener("DOMContentLoaded", function() {
    // --- Referensi Elemen DOM ---
    const video = document.getElementById('camera-stream');
    const canvas = document.getElementById('photo-canvas');
    const captureBtn = document.getElementById('capture-btn');
    const locationInfo = document.getElementById('location-info');
    const form = document.getElementById('absensi-form');
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    const photoInput = document.getElementById('foto_absensi_base64');
    const btnMasuk = document.getElementById('btn-absen-masuk');
    const btnKeluar = document.getElementById('btn-absen-keluar');
    const errorMessage = document.getElementById('error-message');

    // --- Variabel Status ---
    let stream = null; // Untuk menyimpan stream kamera
    let locationReady = false;
    let photoTaken = false;
    
    // ========================================================
    // --- FUNGSI UTAMA ---
    // ========================================================

    /**
     * Mengaktifkan/menonaktifkan tombol submit berdasarkan status
     */
    function updateButtonState() {
        if (locationReady && photoTaken) {
            btnMasuk.disabled = false;
            btnKeluar.disabled = false;
            errorMessage.textContent = "Siap untuk absen.";
            errorMessage.style.color = 'green';
        } else {
            btnMasuk.disabled = true;
            btnKeluar.disabled = true;
            if (!locationReady) {
                errorMessage.textContent = "Sedang menunggu lokasi...";
            } else if (!photoTaken) {
                errorMessage.textContent = "Harap ambil foto terlebih dahulu.";
            }
            errorMessage.style.color = 'red';
        }
    }

    /**
     * (PERBAIKAN) Menghentikan stream kamera yang sedang aktif
     */
    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    /**
     * (PERBAIKAN) Memulai stream kamera
     */
    async function startCamera() {
        // Hentikan dulu jika ada stream lama
        stopCamera(); 
        
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'user', // Kamera depan
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                } 
            });
            video.srcObject = stream;
            video.style.display = 'block'; // Tampilkan video
            canvas.style.display = 'none'; // Sembunyikan canvas
            captureBtn.textContent = 'Ambil Foto';
            captureBtn.style.display = 'block';
            photoTaken = false;
            updateButtonState();
        } catch (err) {
            console.error("Error accessing camera: ", err);
            errorMessage.textContent = "Gagal mengakses kamera. Pastikan Anda mengizinkan akses kamera di browser Anda. " + err.message;
            video.style.display = 'none';
            captureBtn.style.display = 'none';
        }
    }

    /**
     * (PERBAIKAN) Mengambil foto dari video
     */
    function takePhoto() {
        // Set ukuran canvas sesuai video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Gambar frame video ke canvas
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Konversi canvas ke Base64 JPEG
        const dataUrl = canvas.toDataURL('image/jpeg', 0.9); // Kompresi 90%
        photoInput.value = dataUrl;
        
        // Tampilkan foto
        video.style.display = 'none';
        canvas.style.display = 'block';
        
        // HENTIKAN KAMERA (Bug UX Fix)
        stopCamera(); 
        
        captureBtn.textContent = 'Ambil Ulang';
        photoTaken = true;
        updateButtonState();
    }

    /**
     * (Kode Asli) Mendapatkan lokasi GPS
     */
    function getLocation() {
        locationInfo.textContent = "Mendeteksi lokasi...";
        locationInfo.style.color = 'orange';

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                latInput.value = lat;
                lonInput.value = lon;
                locationInfo.textContent = `Lokasi terdeteksi (Akurasi: ${position.coords.accuracy.toFixed(0)} meter)`;
                locationInfo.style.color = 'green';
                
                locationReady = true;
                updateButtonState();
                
            }, function(err) {
                // Error handling untuk GPS (Kode asli Anda sudah bagus)
                let errorMsg = "Gagal mendapatkan lokasi. ";
                switch(err.code) {
                    case err.PERMISSION_DENIED:
                        errorMsg += "Anda menolak izin akses lokasi.";
                        break;
                    case err.POSITION_UNAVAILABLE:
                        errorMsg += "Informasi lokasi tidak tersedia.";
                        break;
                    case err.TIMEOUT:
                        errorMsg += "Waktu permintaan lokasi habis (timeout).";
                        break;
                    default:
                        errorMsg += "Terjadi error tidak diketahui.";
                }
                locationInfo.textContent = errorMsg;
                locationInfo.style.color = 'red';
                errorMessage.textContent = errorMsg; 
                locationReady = false;
                updateButtonState();
            }, { 
                enableHighAccuracy: true, 
                timeout: 15000, // Waktu tunggu 15 detik
                maximumAge: 0 
            });
        } else {
            errorMessage.textContent = "Geolocation tidak didukung oleh browser ini.";
            locationInfo.textContent = "Browser tidak mendukung Geolocation.";
            locationInfo.style.color = 'red';
        }
    }
    
    // ========================================================
    // --- EVENT LISTENERS ---
    // ========================================================

    /**
     * (PERBAIKAN) Logika Tombol Capture
     */
    captureBtn.addEventListener('click', function() {
        // Logika dipisah. Cek status tombol saat ini.
        if (photoTaken) {
            // Jika status "Ambil Ulang", maka mulai ulang kamera
            startCamera();
        } else {
            // Jika status "Ambil Foto", maka ambil foto
            takePhoto();
        }
    });
            
    /**
     * (Kode Asli) Logika Submit Form
     */
    form.addEventListener('submit', function(e) {
        // Dobel cek sebelum submit
        if (!locationReady || !photoTaken) {
            e.preventDefault(); // Hentikan submit
            updateButtonState(); // Tampilkan pesan error yang relevan
        }
        // Hentikan stream (jika masih nyala) saat submit
        stopCamera();
    });

    // ========================================================
    // --- MULAI APLIKASI ---
    // ========================================================
    startCamera();
    getLocation();
    updateButtonState(); // Set status awal (disabled)
});