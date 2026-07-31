document.addEventListener('DOMContentLoaded', () => {
    if (typeof GLightbox === 'undefined') return;

    GLightbox({
        selector: '.galeri-kegiatan a[data-glightbox]',
        touchNavigation: true,
        loop: true,
    });
});
