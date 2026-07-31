document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('repeater-lampiran');
    if (!container) return;

    const tombolTambah = document.getElementById('btn-tambah-lampiran');

    function buatBaris() {
        const contoh = container.querySelector('.baris-lampiran');
        const baris = contoh.cloneNode(true);
        baris.querySelectorAll('input').forEach((input) => {
            input.value = '';
        });
        return baris;
    }

    function pasangHapus(baris) {
        baris.querySelectorAll('.btn-hapus-lampiran').forEach((tombol) => {
            tombol.addEventListener('click', () => baris.remove());
        });
    }

    if (tombolTambah) {
        tombolTambah.addEventListener('click', () => {
            const baris = buatBaris();
            pasangHapus(baris);
            container.appendChild(baris);
        });
    }

    container.querySelectorAll('.baris-lampiran').forEach(pasangHapus);
});
