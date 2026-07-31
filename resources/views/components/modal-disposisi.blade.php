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
                                <input type="radio" name="jenis" value="ke_pelaksana" class="form-selectgroup-input" id="jenis-pelaksana">
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

                    {{--                    <div class="mb-3">
                        <label class="form-label">Disposisi kepada</label>
                        <select name="disposisi_kepada[]" class="form-select" multiple>
                            @foreach(\App\Models\User::where('is_active', true)->get() as $u)
                            <option value="{{ $u->name }}">{{ $u->name }} @if($u->jabatan)({{ $u->jabatan }})@endif</option>
                            @endforeach
                        </select>
                        <small class="form-hint">Tahan Ctrl/Cmd untuk pilih multiple. Atau ketik nama bebas di bawah.</small>
                        <input type="text" name="disposisi_kepada_free[]" class="form-control form-control-sm mt-2" placeholder="Tambah nama/jabatan bebas (opsional)">
                    </div>--}}

                    <div id="section-pelaksana" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Pelaksana</label>
                            <select name="pelaksana_id" class="form-select">
                                <option value="">-- Pilih Pelaksana --</option>
                                @foreach(\App\Models\User::whereHas('role', fn($q) => $q->where('kode', 'unit_lain'))->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="date" name="tanggal_deadline" class="form-control">
                        </div>
                    </div>

                    <div id="section-langsung" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">Catatan Tindak Lanjut</label>
                            <textarea name="catatan_pimpinan" class="form-control" rows="4"></textarea>
                        </div>
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
</script>
@endpush
