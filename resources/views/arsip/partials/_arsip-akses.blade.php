@php
    $canManageAkses = \Illuminate\Support\Facades\Gate::allows('assignAkses', $rekaman);
@endphp

@if($canManageAkses)
<div class="card mt-3" id="arsip-akses-{{ $modul }}-{{ $rekaman->getKey() }}">
    <div class="card-header">
        <h3 class="mb-0">Akses Manual</h3>
        <small class="text-muted"> Pengguna dengan akses khusus ke arsip rahasia ini.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive mb-3">
            <table class="table table-sm table-borderless" id="arsip-akses-list-{{ $modul }}-{{ $rekaman->getKey() }}" data-url="{{ route('arsip.akses.list-akses', [$modul, $rekaman->uuid]) }}">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Diberikan oleh</th>
                        <th class="text-end">Diberikan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="text-center" style="color:var(--muted);">Memuat…</td></tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-2 align-items-end">
<div class="flex-grow-1" style="min-width: 200px;">
                <label class="form-label mb-0">Pilih pengguna</label>
                <input type="text" id="arsip-akses-user-{{ $modul }}-{{ $rekaman->getKey() }}" list="user-pilih-{{ $modul }}-{{ $rekaman->getKey() }}" class="form-control" placeholder="Ketik nama/email pengguna" autocomplete="off">
                <input type="hidden" name="user_id" id="arsip-akses-user-id-{{ $modul }}-{{ $rekaman->getKey() }}">
                <datalist id="user-pilih-{{ $modul }}-{{ $rekaman->getKey() }}"></datalist>
            </div>
            <button type="button"
                data-base="{{ route('arsip.akses.assign-akses', [$modul, $rekaman->uuid]) }}"
                data-list="#arsip-akses-list-{{ $modul }}-{{ $rekaman->getKey() }}"
                class="btn btn-outline-primary arsip-akses-assign-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Beri Akses
            </button>
        </div>

        <template id="arsip-akses-row-tpl-{{ $modul }}-{{ $rekaman->getKey() }}">
            <tr>
                <td class="name"></td>
                <td class="email" style="color:var(--muted);"></td>
                <td class="diberikan-oleh"></td>
                <td class="text-end createdAt"></td>
                    <td class="text-end">
                    <button type="button" class="btn btn-sm btn-danger arsip-akses-remove"
                        data-remove-base="{{ route('arsip.akses.assign-akses', [$modul, $rekaman->uuid]) }}"
                        title="Cabut akses">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="3" x2="21" y2="21"/><line x1="21" y1="3l-18 18"/></svg>
                    </button>
                </td>
            </tr>
        </template>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const box = document.getElementById('arsip-akses-{{ $modul }}-{{ $rekaman->getKey() }}');
    const userIdSelect = document.getElementById('arsip-akses-user-{{ $modul }}-{{ $rekaman->getKey() }}');
    const hiddenId = document.getElementById('arsip-akses-user-id-{{ $modul }}-{{ $rekaman->getKey() }}');
    const listBox = document.getElementById('arsip-akses-list-{{ $modul }}-{{ $rekaman->getKey() }}');
    const baseUrl = listBox.dataset.url;
    const template = document.getElementById('arsip-akses-row-tpl-{{ $modul }}-{{ $rekaman->getKey() }}');

    function refreshList() {
        fetch(baseUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const tbody = listBox.querySelector('tbody');
                tbody.innerHTML = '';
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="color:var(--muted);">Belum ada akses manual.</td></tr>';
                    return;
                }
                data.forEach(item => {
                    const clone = template.content.cloneNode(true);
                    clone.querySelector('.name').textContent = item.user_name;
                    clone.querySelector('.email').textContent = item.user_email;
                    clone.querySelector('.diberikan-oleh').textContent = item.diberikan_oleh_name;
                    clone.querySelector('.createdAt').textContent = item.created_at_formatted;
                    const removeBtn = clone.querySelector('button.arsip-akses-remove');
                    const removeUrl = removeBtn.dataset.removeBase + '/' + item.user_id;
                    removeBtn.addEventListener('click', () => {
                        if (!confirm('Cabut akses untuk ' + item.user_name + '?')) return;
                        fetch(removeUrl, {
                            method: 'DELETE',
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        }).then(r => r.json()).then(() => refreshList());
                    });
                    tbody.appendChild(clone);
                });
            });
    }

    userIdSelect && (function () {
        let typing;
        let results = [];
        const dataList = document.getElementById('user-pilih-{{ $modul }}-{{ $rekaman->getKey() }}');
        const searchUrl = '{{ route('api.user-search') }}';

        function doSearch(q) {
            clearTimeout(typing);
            typing = setTimeout(() => {
                fetch(searchUrl + '?q=' + encodeURIComponent(q || ''), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.ok ? r.json() : [])
                    .then(data => { results = data || []; renderOptions(); })
                    .catch(() => { results = []; renderOptions(); });
            }, 200);
        }

        userIdSelect.addEventListener('input', function () {
            doSearch(this.value);
        });

        function renderOptions() {
            dataList.innerHTML = '';
            results.forEach(u => {
                const opt = document.createElement('option');
                opt.value = u.name + ' (' + u.email + ')';
                opt.label = u.name + ' (' + u.email + ')';
                opt.dataset.id = u.id;
                dataList.appendChild(opt);
            });
            if (!results.length && !userIdSelect.value) {
                const empty = document.createElement('option');
                empty.value = '—';
                empty.label = 'Ketik nama/email pengguna';
                dataList.appendChild(empty);
            }
        }

        userIdSelect.addEventListener('change', function () {
            const label = this.value;
            let id = null;
            results.forEach(u => {
                if ((u.name + ' (' + u.email + ')') === label) id = u.id;
            });
            if (id) {
                hiddenId.value = id;
                this.value = label; // keep label visible
            }
        });

        doSearch('');
    })();

    box.querySelector('.arsip-akses-assign-btn').addEventListener('click', assignUser);

    userIdSelect.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            assignUser();
        }
    });

    function assignUser() {
        const userId = hiddenId.value;
        if (!userId) { alert('Pilih pengguna dulu.'); return; }
        const btn = box.querySelector('.arsip-akses-assign-btn');
        fetch(btn.dataset.base, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: Number(userId) }),
        }).then(r => {
            if (!r.ok) return r.text().then(t => { throw new Error(t); });
            return r.json();
        }).then(() => {
            userIdSelect.value = '';
            hiddenId.value = '';
            refreshList();
        }).catch(err => alert('Gagal memberikan akses: ' + err.message));
    }

    refreshList();
})();
</script>
@endpush
@endif
