// Pastikan script berjalan setelah DOM selesai dimuat
// Kita menggunakan jQuery yang sudah didaftarkan sebagai dependensi di PHP
jQuery(document).ready(function($) {

    // Ambil container dan data yang dikirim dari PHP
    const container = $('#jhb-title-rotator-container');

    // Cek apakah container dan data ada. Jika tidak, hentikan script.
    // 'jhbData' adalah nama objek yang kita definisikan di wp_localize_script
    if (container.length === 0 || typeof jhbData === 'undefined' || jhbData.pages.length === 0) {
        // Jika tidak ada container atau tidak ada halaman, jangan lakukan apa-apa.
        if(container.length > 0) {
            container.html('<p>Tidak ada halaman yang dapat ditampilkan.</p>');
        }
        return;
    }

    // --- Pengaturan ---
    const pages = jhbData.pages;
    const htmlTag = jhbData.tag; // Ambil tag HTML dari data
    let currentIndex = 0;
    const displayDuration = 3000; // Waktu tampil setiap judul (ms) -> 4 detik
    const fadeSpeed = 500; // Kecepatan efek fade (ms) -> 0.5 detik

    // Fungsi utama untuk menampilkan judul berikutnya
    function showNextTitle() {
        // Ambil data halaman saat ini
        const page = pages[currentIndex];

        // Buat elemen HTML baru (misal: <h2> atau <p>) dengan tautan di dalamnya
        // Elemen ini disembunyikan terlebih dahulu (display: none)
        const newElement = $('<' + htmlTag + ' style="display:none;"></' + htmlTag + '>')
            .html('<a href="' + page.link + '">' + page.title + '</a>');
        
        // Tambahkan elemen ke container, lalu munculkan dengan efek fadeIn
        newElement.appendTo(container).fadeIn(fadeSpeed)
            // Tunggu beberapa detik
            .delay(displayDuration)
            // Hilangkan dengan efek fadeOut
            .fadeOut(fadeSpeed, function() {
                // Setelah fadeOut selesai, hapus elemen dari DOM
                $(this).remove();

                // Pindah ke index berikutnya
                currentIndex++;

                // Jika sudah di akhir array, kembali ke awal (looping)
                if (currentIndex >= pages.length) {
                    currentIndex = 0;
                }

                // Panggil fungsi ini lagi untuk menampilkan judul berikutnya
                showNextTitle();
            });
    }

    // Mulai siklus pertama kali
    showNextTitle();

});