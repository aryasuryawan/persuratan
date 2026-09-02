import _ from 'lodash';

const PRESETS = {
    'surat-masuk': {
        columns: ['agenda', 'pengirim', 'perihal', 'tanggal', 'status', 'klasifikasi', 'topik', 'aksi'],
        detailUrl: '/penerimaan-surat',
        emptyTitle: 'Tidak ada surat',
        emptySubtitle: 'Belum ada data surat masuk yang ditemukan',
    },
    'surat-keluar': {
        columns: ['nomor_surat', 'tujuan', 'perihal', 'tanggal', 'klasifikasi', 'topik', 'aksi'],
        detailUrl: '/surat-keluar',
        emptyTitle: 'Tidak ada surat keluar',
        emptySubtitle: 'Belum ada data surat keluar yang ditemukan',
    },
};

class SuratTable {
    constructor(endpoint, config = {}) {
        this.endpoint = endpoint;
        this.page = 1;
        // Use preset defaults for 'surat-masuk' if no config given
        const presetKey = endpoint.includes('surat-keluar') ? 'surat-keluar' : 'surat-masuk';
        this.config = { ...PRESETS[presetKey], ...config };
        this.init();
    }

    init() {
        const searchEl = document.getElementById('search-surat');
        const statusEl = document.getElementById('filter-status');
        const klasifikasiEl = document.getElementById('filter-klasifikasi');
        const topikEl = document.getElementById('filter-topik');
        const kodeSuratEl = document.getElementById('filter-kode-surat');

        searchEl?.addEventListener('input', _.debounce(() => {
            this.page = 1;
            this.fetch();
        }, 300));

        statusEl?.addEventListener('change', () => {
            this.page = 1;
            this.fetch();
        });

        klasifikasiEl?.addEventListener('change', () => {
            this.page = 1;
            this.fetch();
        });

        topikEl?.addEventListener('change', () => {
            this.page = 1;
            this.fetch();
        });

        kodeSuratEl?.addEventListener('change', () => {
            this.page = 1;
            this.fetch();
        });

        this.fetch();
    }

    getSearch() {
        return document.getElementById('search-surat')?.value || '';
    }

    getStatus() {
        return document.getElementById('filter-status')?.value || '';
    }

    getKlasifikasi() {
        return document.getElementById('filter-klasifikasi')?.value || '';
    }

    getTopik() {
        return document.getElementById('filter-topik')?.value || '';
    }

    getKodeSurat() {
        return document.getElementById('filter-kode-surat')?.value || '';
    }

    async fetch() {
        const url = new URL(this.endpoint, window.location.origin);
        url.searchParams.set('page', this.page);
        url.searchParams.set('search', this.getSearch());

        const klasifikasi = this.getKlasifikasi();
        const topik = this.getTopik();
        if (klasifikasi) url.searchParams.set('klasifikasi_id', klasifikasi);
        if (topik) url.searchParams.set('topik_id', topik);

        const status = this.getStatus();
        if (status) url.searchParams.set('status', status);

        const kodeSurat = this.getKodeSurat();
        if (kodeSurat) url.searchParams.set('kode_surat_id', kodeSurat);

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        this.render(data);
    }

    render(data) {
        const tbody = document.getElementById('tabel-surat-body');
        const preset = this.config;

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${preset.columns.length}">
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-inbox"></i>
                            </div>
                            <p class="empty-title">${preset.emptyTitle}</p>
                            <p class="empty-subtitle text-secondary">${preset.emptySubtitle}</p>
                        </div>
                    </td>
                </tr>`;
        } else {
            tbody.innerHTML = data.data.map(s => {
                const cells = [];

                if (preset.columns.includes('agenda')) {
                    cells.push(`<td><div class="d-flex align-items-center"><div class="flex-fill"><div class="fw-bold">${s.nomor_agenda || ''}</div><div class="text-secondary small">${s.pengirim || ''}</div></div></div></td>`);
                }
                if (preset.columns.includes('pengirim')) {
                    cells.push(`<td><div class="text-secondary">${s.organisasi_pengirim || '-'}</div></td>`);
                }
                if (preset.columns.includes('nomor_surat')) {
                    cells.push(`<td><div class="fw-bold">${s.nomor_surat || ''}</div><div class="text-secondary small">${s.kode_surat || ''}</div></td>`);
                }
                if (preset.columns.includes('tujuan')) {
                    cells.push(`<td><div class="text-truncate" style="max-width: 220px;">${s.tujuan_surat || '-'}</div></td>`);
                }
                if (preset.columns.includes('perihal')) {
                    cells.push(`<td><div class="text-truncate" style="max-width: 220px;">${s.perihal}</div></td>`);
                }
                if (preset.columns.includes('tanggal')) {
                    cells.push(`<td class="text-secondary">${s.tanggal_diterima || s.tanggal_surat || ''}</td>`);
                }
                if (preset.columns.includes('status') && s.status_kode) {
                    cells.push(`<td><span class="badge bg-${this.statusColor(s.status_kode)}-lt text-${this.statusColor(s.status_kode)}"><span class="d-inline-block status-dot bg-${this.statusColor(s.status_kode)} me-1"></span>${s.status_label}</span></td>`);
                }
                if (preset.columns.includes('klasifikasi')) {
                    cells.push(`<td><span class="badge bg-${s.klasifikasi_color}-lt text-${s.klasifikasi_color}">${s.klasifikasi}</span></td>`);
                }
                if (preset.columns.includes('topik')) {
                    cells.push(`<td>${s.topiks && s.topiks.length > 0 ? s.topiks.map(t => `<span class="badge bg-${t.color}-lt text-${t.color}">${t.nama}</span>`).join(' ') : '<span class="text-secondary">-</span>'}</td>`);
                }
                if (preset.columns.includes('aksi')) {
                    cells.push(`<td><div class="btn-list justify-content-end"><a href="${preset.detailUrl}/${s.uuid}" class="btn btn-sm btn-ghost-secondary" title="Lihat detail"><i class="ti ti-eye me-1"></i> Detail</a></div></td>`);
                }

                return `<tr>${cells.join('')}</tr>`;
            }).join('');
        }

        const info = document.getElementById('tabel-surat-info');
        if (info && data.total > 0) {
            info.textContent = `Menampilkan ${data.from}–${data.to} dari ${data.total} surat`;
        } else if (info) {
            info.textContent = '';
        }

        this.renderPagination(data);
    }

    statusColor(kode) {
        const map = {
            'received': 'secondary',
            'verified': 'info',
            'forwarded_to_leadership': 'warning',
            'assigned': 'primary',
            'in_progress': 'azure',
            'completed': 'success',
            'archived': 'dark',
        };
        return map[kode] || 'secondary';
    }

    renderPagination(data) {
        const pagination = document.getElementById('tabel-surat-pagination');
        if (!pagination) return;

        const totalPages = Math.ceil(data.total / data.per_page);
        const current = data.current_page;

        if (totalPages <= 1) {
            pagination.innerHTML = '';
            return;
        }

        let html = '';

        if (current > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${current - 1}"><i class="ti ti-chevron-left"></i></a></li>`;
        }

        const range = this.paginationRange(current, totalPages);
        for (const p of range) {
            if (p === '...') {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            } else {
                html += `<li class="page-item ${p === current ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
            }
        }

        if (current < totalPages) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${current + 1}"><i class="ti ti-chevron-right"></i></a></li>`;
        }

        pagination.innerHTML = html;

        pagination.querySelectorAll('a[data-page]').forEach(a => {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                this.page = parseInt(a.dataset.page);
                this.fetch();
            });
        });
    }

    paginationRange(current, total) {
        if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

        const pages = [];
        if (current <= 3) {
            pages.push(1, 2, 3, 4, '...', total);
        } else if (current >= total - 2) {
            pages.push(1, '...', total - 3, total - 2, total - 1, total);
        } else {
            pages.push(1, '...', current - 1, current, current + 1, '...', total);
        }
        return pages;
    }
}

window.SuratTable = SuratTable;
