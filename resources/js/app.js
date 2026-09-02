import Alpine from 'alpinejs';
import { tabler } from '@tabler/core';
import 'chart.js/auto';

window.Alpine = Alpine;
Alpine.start();

import './simpel/tracking-chart.js';
import './simpel/disposisi-form.js';
import './simpel/surat-table.js';
import './simpel/repeater-lampiran.js';
import './simpel/galeri-lightbox.js';

// Dashboard 2 charts — only load on dashboard2 route
if (window.location.pathname === '/dashboard2') {
    import('./simpel/dashboard2-charts.js');
}

// Tabler components auto-init via data-bs-* attributes
// tabler namespace provides: getColor, hexToRgba, prefix
console.log('Tabler loaded:', Object.keys(tabler));

// Theme toggle
const html = document.documentElement;
const toggle = document.getElementById('theme-toggle');
if (toggle) {
    const cur = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', cur);
    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
    });
}
