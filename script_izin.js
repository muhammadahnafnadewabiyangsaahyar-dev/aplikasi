document.addEventListener('DOMContentLoaded', function() {
    // ... (Kode Anda untuk applyIzinButton, izinFormContainer, signaturePad pertama, dll.) ...

    // --- BARU: Logika untuk Ubah Tanda Tangan ---
    const editSignatureButton = document.getElementById('edit-signature-btn');        // Tombol "Ubah Tanda Tangan"
    const editSignatureContainer = document.getElementById('edit-signature-container'); // Div yang membungkus form edit
    const editSignatureForm = document.getElementById('edit-signature-form');         // Form edit TTD
    const newSignatureCanvas = document.getElementById('new-signature-pad');      // Canvas BARU
    const clearNewSignatureButton = document.getElementById('clear-new-signature'); // Tombol Hapus BARU
    const cancelEditSignatureButton = document.getElementById('cancel-edit-signature'); // Tombol Batal
    const newHiddenInput = document.getElementById('new-signature-data');         // Input hidden BARU
    let newSignaturePad; // Variabel untuk instance SignaturePad BARU

    // 1. Tampilkan Form Edit saat Tombol "Ubah" diklik
    if (editSignatureButton && editSignatureContainer) {
        editSignatureButton.addEventListener('click', function() {
            console.log("Tombol Ubah TTD diklik!");
            editSignatureContainer.style.display = 'block'; // Tampilkan container
            editSignatureForm.style.display = 'block'; // Tampilkan form di dalamnya jg

            // Inisialisasi SignaturePad BARU HANYA saat form muncul
            if (newSignatureCanvas && !newSignaturePad) { // Cek jika belum diinisialisasi
                 console.log("Inisialisasi SignaturePad BARU...");
                 newSignaturePad = new SignaturePad(newSignatureCanvas, {
                    penColor: "rgb(0, 0, 0)"
                 });
                 // Panggil resize sekali untuk canvas baru
                 resizeNewCanvas(); 
            } else if (newSignaturePad) {
                newSignaturePad.clear(); // Hapus jika sudah ada sebelumnya
            }
        });
    }

    // 2. Sembunyikan Form Edit saat Tombol "Batal" diklik
    if (cancelEditSignatureButton && editSignatureContainer) {
        cancelEditSignatureButton.addEventListener('click', function() {
            console.log("Tombol Batal diklik!");
            editSignatureContainer.style.display = 'none';
            editSignatureForm.style.display = 'none';
            if (newSignaturePad) {
                newSignaturePad.clear(); // Hapus gambar saat dibatalkan
            }
        });
    }

    // 3. Hapus Tanda Tangan BARU saat Tombol "Hapus" diklik
    if (clearNewSignatureButton && newSignatureCanvas) { // Perlu cek newSignatureCanvas dulu
         clearNewSignatureButton.addEventListener('click', function() {
             if (newSignaturePad) { // Pastikan sudah diinisialisasi
                 console.log("Tombol Hapus Baru diklik!");
                 newSignaturePad.clear();
             }
         });
    }

    // 4. Tangani Pengiriman Form EDIT Tanda Tangan
    if (editSignatureForm && newSignatureCanvas) { // Perlu cek newSignatureCanvas dulu
        editSignatureForm.addEventListener('submit', function(event) {
            console.log("Submit Form Edit TTD...");
            if (newSignaturePad && !newSignaturePad.isEmpty()) {
                const dataURL = newSignaturePad.toDataURL('image/png');
                newHiddenInput.value = dataURL;
                console.log("Data TTD Baru disimpan ke hidden input.");
                // Biarkan form dikirim ke update_signature.php
            } else {
                alert("Mohon gambar tanda tangan baru Anda.");
                event.preventDefault(); // Hentikan pengiriman jika kosong
            }
        });
    }

    // 5. Fungsi Resize untuk Canvas BARU (mirip yang lama)
    function resizeNewCanvas() {
        if (!newSignatureCanvas) return; // Keluar jika canvas baru tidak ada
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        newSignatureCanvas.width = newSignatureCanvas.offsetWidth * ratio;
        newSignatureCanvas.height = newSignatureCanvas.offsetHeight * ratio;
        newSignatureCanvas.getContext("2d").scale(ratio, ratio);
        if (newSignaturePad) { // Cek jika sudah diinisialisasi
            newSignaturePad.clear(); 
        }
    }
    // Tambahkan event listener resize untuk canvas baru juga
    window.addEventListener("resize", resizeNewCanvas); 
    // Tidak perlu memanggil resizeNewCanvas() di awal karena formnya tersembunyi

    // --- Akhir dari Logika Ubah Tanda Tangan ---

    // ... (Kode Anda yang sudah ada untuk form utama dan signature pad pertama) ...

}); // Akhir DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM siap, skrip berjalan!");

    // --- Bagian Logika Formulir Izin ---
    const applyIzinButton = document.getElementById('btn-apply'); // Tombol "Ajukan Surat Izin" awal
    console.log("Tombol 'Ajukan':", applyIzinButton);
    const izinFormContainer = document.getElementById('form-container'); // Div yang berisi form
    console.log("Container Form:", izinFormContainer);
    const izinInfoContainer = document.querySelector('.content-container:not(#form-container)'); 
    const izinForm = document.querySelector('.form-surat-izin'); // Elemen <form>

    // Tampilkan formulir saat tombol "Ajukan Surat Izin" awal diklik
    if (applyIzinButton) {
        applyIzinButton.addEventListener('click', function() {
            console.log("Tombol diklik!");
            if (izinInfoContainer) izinInfoContainer.style.display = 'none'; // Sembunyikan info
            console.log("Menampilkan form container...");
            if (izinFormContainer) izinFormContainer.style.display = 'block'; // Tampilkan form
        });
    } else {
        // Jika tombol tidak ditemukan saat halaman dimuat
        console.error("ERROR: Tombol 'Ajukan' (id: btn-apply) tidak ditemukan saat DOM siap!");
    }

    // --- Bagian Logika Signature Pad ---
    const canvas = document.getElementById('signature-pad'); // Dapatkan elemen canvas
    const clearButton = document.getElementById('clear-signature'); // Dapatkan tombol hapus
    const hiddenInput = document.getElementById('signature-data'); // Dapatkan input tersembunyi
    let signaturePad; // Variabel untuk menyimpan objek SignaturePad

    // Hanya inisialisasi jika elemen canvas ditemukan
    if (canvas) {
        // Buat objek SignaturePad baru menggunakan elemen canvas
        signaturePad = new SignaturePad(canvas, {
            penColor: "rgb(0, 0, 0)" // Atur warna pena (misal: hitam)
        });

        // Tambahkan fungsi untuk tombol 'Hapus'
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                signaturePad.clear(); // Gunakan fungsi clear() dari library
            });
        }
    }

    // --- Menangani Pengiriman Formulir ---
    if (izinForm) {
        izinForm.addEventListener('submit', function(event) {
            // Cek apakah SignaturePad sudah diinisialisasi DAN apakah tanda tangan tidak kosong
            if (signaturePad && !signaturePad.isEmpty()) {
                // Ambil data gambar sebagai string Base64 PNG
                const dataURL = signaturePad.toDataURL('image/png');
                // Masukkan string Base64 ke dalam input tersembunyi
                hiddenInput.value = dataURL;

                // Jika Anda sudah siap mengirim data ke docx.php,
                // hapus komentar pada baris di bawah ini dan hapus alert/preventDefault
                // Formulir akan dikirim secara normal
                
                // Jika masih tahap testing:
                // alert('Tanda tangan terisi, formulir akan dikirim (sekarang dicegah).');
                // event.preventDefault(); // Hentikan pengiriman untuk testing

            } else if (canvas) { // Hanya validasi jika canvas memang seharusnya ada
                // Jika tanda tangan kosong
                alert("Mohon isi tanda tangan Anda.");
                event.preventDefault(); // Hentikan pengiriman formulir
            }
            // Jika tidak ada canvas (misal di halaman lain), biarkan formulir dikirim
        });
        if (canvas) { // Pastikan canvas ada sebelum memanggil
                 resizeCanvas();
        }
    } // Tutup blok izinForm event listener
    showEditSignatureForm();// Panggil fungsi untuk menampilkan form edit TTD jika perlu
    this.addEventListener('click', function(event) {
    signature.style.display = 'block';
    editSignatureContainer.style.display = 'block';
    newSignaturePad = new SignaturePad(newSignatureCanvas, {
        penColor: "rgb(0, 0, 0)"
    });

    });
    cancelEditSignatureForm(); // Panggil fungsi untuk membatalkan edit TTD jika perlu
    this.addEventListener('click', function(event) {
    if (event.target === cancelEditSignatureBtn) {
        editSignatureContainer.style.display = 'none';
        newSignaturePad.clear();
    } else {
        return;
    }
    });
}); // Tutup DOMContentLoaded event listener

document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM siap, skrip berjalan!");

    // --- Bagian Logika Formulir Izin ---
    const applyIzinButton = document.getElementById('btn-apply'); 
    const izinFormContainer = document.getElementById('form-container'); 
    const izinInfoContainer = document.querySelector('.content-container:not(#form-container)'); 
    const izinForm = document.querySelector('.form-surat-izin'); 

    // Tampilkan formulir saat tombol "Ajukan Surat Izin" awal diklik
    if (applyIzinButton) {
        applyIzinButton.addEventListener('click', function() {
            console.log("Tombol diklik!");
            if (izinInfoContainer) izinInfoContainer.style.display = 'none'; 
            console.log("Menampilkan form container...");
            if (izinFormContainer) {
                izinFormContainer.style.display = 'block'; 
                // !!! PANGGIL resizeCanvas SETELAH FORM TAMPIL !!!
                if (canvas) { // Pastikan canvas ada
                    resizeCanvas(); 
                    console.log("Resize canvas dipanggil setelah form tampil.");
                }
            }
        });
    } else {
        console.error("ERROR: Tombol 'Ajukan' (id: btn-apply) tidak ditemukan saat DOM siap!");
    }

    // --- Bagian Logika Signature Pad ---
    const canvas = document.getElementById('signature-pad'); 
    const clearButton = document.getElementById('clear-signature'); 
    const hiddenInput = document.getElementById('signature-data'); 
    let signaturePad; 

    // !!! DEFINISIKAN FUNGSI resizeCanvas DI SINI !!!
    function resizeCanvas() {
        if (!canvas) return; // Keluar jika canvas tidak ada
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        if (signaturePad) { // Hapus TTD hanya jika signaturePad sudah dibuat
             signaturePad.clear(); 
        } else if(canvas.getContext("2d")) { // Atau bersihkan manual jika belum
             canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
        }
    }

    // Hanya inisialisasi jika elemen canvas ditemukan
    if (canvas) {
        signaturePad = new SignaturePad(canvas, {
            penColor: "rgb(0, 0, 0)" 
        });

        // Tambahkan fungsi untuk tombol 'Hapus'
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                signaturePad.clear(); 
            });
        }
        
        // Tambahkan listener resize window untuk canvas ini
        window.addEventListener("resize", resizeCanvas);
        // Tidak perlu memanggil resizeCanvas() di sini karena form mungkin tersembunyi

    } else {
         console.log("Canvas #signature-pad tidak ditemukan saat DOM siap (mungkin TTD sudah ada).");
    }

    // --- Menangani Pengiriman Formulir ---
    if (izinForm) {
        izinForm.addEventListener('submit', function(event) {
            // Cek jika canvas ADA dan signaturePad sudah dibuat
            if (canvas && signaturePad) { 
                if (!signaturePad.isEmpty()) {
                    const dataURL = signaturePad.toDataURL('image/png');
                    hiddenInput.value = dataURL;
                    // Biarkan form dikirim
                } else {
                    // Jika canvas ada tapi kosong
                    alert("Mohon isi tanda tangan Anda.");
                    event.preventDefault(); // Hentikan pengiriman
                }
            } 
            // Jika canvas tidak ada (karena TTD sudah tersimpan), biarkan form dikirim
        });
        // !!! HAPUS PEMANGGILAN resizeCanvas() DARI SINI !!!
        // if (canvas) { 
        //          resizeCanvas(); // <-- Hapus ini
        // }
    } 

});