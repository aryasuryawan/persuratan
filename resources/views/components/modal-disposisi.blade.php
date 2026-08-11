@props(['surat' => null])
@php
    $submitToken = \Illuminate\Support\Str::random(32);
    $tokens = session('submit_tokens', []);
    $tokens[] = $submitToken;
    session()->put('submit_tokens', $tokens);
@endphp

<div class="modal modal-blur fade" id="modal-disposisi" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Disposisi Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('disposisi.store') }}" id="form-disposisi" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="surat_masuk_id" id="surat-masuk-id" value="{{ $surat->id ?? '' }}">
                <input type="hidden" name="_submit_token" value="{{ $submitToken }}">
                <div class="modal-body">
                    @if(auth()->user()->role->kode === 'sekretaris')
                    <div class="mb-3">
                        <label class="form-label required">Atas Nama Pimpinan</label>
                        <select name="atas_nama_pimpinan_id" class="form-select" required>
                            <option value="">-- Pilih Pimpinan --</option>
                            @foreach(auth()->user()->pimpinanYangDilayani as $p)
                                <option value="{{ $p->pimpinan->id }}">{{ $p->pimpinan->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-hint">Disposisi dicatat atas nama pimpinan yang dipilih.</small>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label required">Jenis Disposisi</label>
                        <div class="form-selectgroup">
                            <label class="form-selectgroup-item">
                                <input type="radio" name="jenis" value="ke_pelaksana" class="form-selectgroup-input" id="jenis-pelaksana" checked>
                                <span class="form-selectgroup-label">Ke Pelaksana</span>
                            </label>
                            <label class="form-selectgroup-item">
                                <input type="radio" name="jenis" value="langsung_pimpinan" class="form-selectgroup-input" id="jenis-langsung">
                                <span class="form-selectgroup-label">Tindak Lanjut Langsung</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Instruksi</label>
                        <div class="form-checklist">
                            @foreach(['Diperiksa', 'Diperhatikan', 'Ditindaklanjuti', 'Dilaksanakan', 'Arsip/Ditolak'] as $instruksi)
                            <label class="form-checklist-item">
                                <input type="checkbox" name="instruksi[]" value="{{ $instruksi }}" class="form-checklist-input">
                                <span class="form-checklist-label">{{ $instruksi }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Disposisi kepada</label>
                        <select name="disposisi_kepada[]" class="form-select" multiple id="disposisi-kepada-select">
                            @foreach(\App\Models\User::where('is_active', true)->get() as $u)
                            <option value="{{ $u->name }}">{{ $u->name }} @if($u->jabatan)({{ $u->jabatan }})@endif</option>
                            @endforeach
                        </select>
                        <small class="form-hint">Tahan Ctrl/Cmd untuk pilih multiple user. User pertama = pelaksana utama. Tambah nama/jabatan bebas di bawah.</small>
                        <div class="mt-2">
                            <label class="form-label small">Tambah nama/jabatan bebas <span class="text-muted">(email wajib agar notifikasi terkirim)</span></label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="disposisi_kepada_free[]" class="form-control" placeholder="Nama (contoh: Divisi Keuangan)">
                                <input type="email" name="disposisi_kepada_free_email[]" class="form-control" placeholder="Email (wajib)" style="max-width:220px;">
                                <button type="button" class="btn btn-outline-secondary" id="btn-add-disposisi-free">Tambah</button>
                            </div>
                            <div id="disposisi-free-list" class="mt-1"></div>
                        </div>
                        @error('disposisi_kepada') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CC Email <small class="text-muted">(opsional)</small></label>
                        <select name="cc[]" class="form-select" multiple id="cc-select">
                            @foreach(\App\Models\User::where('is_active', true)->get() as $u)
                            <option value="{{ $u->email }}">{{ $u->name }} @if($u->jabatan)({{ $u->jabatan }})@endif — {{ $u->email }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="cc_free" class="form-control mt-2" placeholder="Atau ketik email lain, pisahkan dengan koma (contoh: eksternal@contoh.com)">
                        <small class="form-hint">Tahan Ctrl/Cmd untuk pilih multiple user, atau masukkan email eksternal. Mereka menerima salinan notifikasi disposisi.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3" id="deadline-field">
                        <label class="form-label">Deadline Laporan Follow up Disposisi</label>
                        <input type="date" name="tanggal_deadline" class="form-control">
                    </div>

                    <div class="mb-3" id="catatan-pimpinan-field">
                        <label class="form-label">Catatan Tindak Lanjut</label>
                        <textarea name="catatan_pimpinan" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lampiran <small class="text-muted">(opsional)</small></label>
                        <input type="file" name="lampiran[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-hint">PDF, JPG, PNG. Maks 10MB per file. Bisa pilih banyak file.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan-disposisi">Simpan Disposisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('form-disposisi')?.addEventListener('submit', function() {
    const btn = document.getElementById('btn-simpan-disposisi');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
});

// Dynamic free text inputs for Disposisi kepada
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('btn-add-disposisi-free');
    const list = document.getElementById('disposisi-free-list');
    if (addBtn && list) {
        addBtn.addEventListener('click', function() {
            const div = document.createElement('div');
            div.className = 'input-group input-group-sm mt-1';
            div.innerHTML = '<input type="text" name="disposisi_kepada_free[]" class="form-control" placeholder="Nama">' +
                '<input type="email" name="disposisi_kepada_free_email[]" class="form-control" placeholder="Email (wajib)" style="max-width:220px;">' +
                '<button type="button" class="btn btn-outline-danger" onclick="this.closest(\'.input-group\').remove()">Hapus</button>';
            list.appendChild(div);
        });
    }

    // Show/hide fields based on jenis
    const jenisPelaksana = document.getElementById('jenis-pelaksana');
    const jenisLangsung = document.getElementById('jenis-langsung');
    const deadlineField = document.getElementById('deadline-field');
    const catatanPimpinanField = document.getElementById('catatan-pimpinan-field');
    const disposisiKepadaSelect = document.getElementById('disposisi-kepada-select');

    function toggleFields() {
        if (jenisPelaksana.checked) {
            deadlineField.style.display = '';
            catatanPimpinanField.style.display = 'none';
        } else {
            deadlineField.style.display = 'none';
            catatanPimpinanField.style.display = '';
        }
    }

    jenisPelaksana?.addEventListener('change', toggleFields);
    jenisLangsung?.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endpush