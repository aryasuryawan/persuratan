import _ from 'lodash';

class SuratTable {
    constructor(endpoint) {
        this.endpoint = endpoint;
        this.page = 1;
        this.init();
    }

    init() {
        const searchEl = document.getElementById('search-surat');
        const statusEl = document.getElementById('filter-status');

        searchEl?.addEventListener('input', _.debounce(() => {
            this.page = 1;
            this.fetch();
        }, 300));

        statusEl?.addEventListener('change', () => {
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

    async fetch() {
        const url = new URL(this.endpoint, window.location.origin);
        url.searchParams.set('page', this.page);
        url.searchParams.set('search', this.getSearch());
        url.searchParams.set('status', this.getStatus());

        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        this.render(data);
    }

    render(data) {
        const tbody = document.getElementById('tabel-surat-body');

        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <div class="empty-icon">
                                <i class="ti ti-inbox"></i>
                            </div>
                            <p class="empty-title">Tidak ada surat</p>
                            <p class="empty-subtitle text-secondary">Belum ada data surat masuk yang ditemukan</p>
                        </div>
                    </td>
                </tr>`;
        } else {
            tbody.innerHTML = data.data.map(s => `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="flex-fill">
                                <div class="fw-bold">${s.nomor_agenda}</div>
                                <div class="text-secondary small">${s.pengirim}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 220px;">${s.perihal}</div>
                    </td>
                    <td class="text-secondary">${s.tanggal_diterima}</td>
                    <td>
                        <span class="badge bg-${this.statusColor(s.status_kode)}-lt text-${this.statusColor(s.status_kode)}">
                            <span class="d-inline-block status-dot bg-${this.statusColor(s.status_kode)} me-1"></span>
                            ${s.status_label}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${s.klasifikasi_color}-lt text-${s.klasifikasi_color}">${s.klasifikasi}</span>
                    </td>
                    <td>
                        <div class="btn-list justify-content-end">
                            <a href="/penerimaan-surat/${s.uuid}" class="btn btn-sm btn-ghost-secondary" title="Lihat detail">
                                <i class="ti ti-eye me-1"></i> Detail
                            </a>
                        </div>
                    </td>
                </tr>
            `).join('');
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
