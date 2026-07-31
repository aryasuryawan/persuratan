@php $rekaman = $rekaman ?? null; @endphp

<div class="card">
    <div class="card-header"><h3>Lampiran Dokumen</h3></div>
    <div class="card-body">
        @if($rekaman && $rekaman->relationLoaded('lampiran') && $rekaman->lampiran->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Lampiran Saat Ini <span class="text-muted">(centang untuk hapus)</span></label>
            <div class="list-group">
                @foreach($rekaman->lampiran as $lampiran)
                <div class="list-group-item d-flex align-items-center">
                    <label class="form-check me-2 mb-0">
                        <input class="form-check-input" type="checkbox" name="hapus_lampiran[]" value="{{ $lampiran->id }}">
                        <span class="form-check-label">Hapus</span>
                    </label>
                    <span class="badge bg-secondary-lt me-2">{{ strtoupper($lampiran->tipe_file) }}</span>
                    <a href="{{ $lampiran->url }}" target="_blank" class="text-break">{{ $lampiran->nama_file }}</a>
                    @if($lampiran->keterangan)
                    <small class="text-muted ms-2">— {{ $lampiran->keterangan }}</small>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div id="repeater-lampiran">
            <div class="row g-2 align-items-center mb-2 baris-lampiran">
                <div class="col-md-7">
                    <input type="file" name="lampiran[]" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="lampiran_keterangan[]" class="form-control" placeholder="Keterangan (mis. SK Asli, Daftar Hadir)">
                        <button type="button" class="btn btn-icon btn-outline-danger btn-hapus-lampiran" tabindex="-1" aria-label="Hapus baris">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-lampiran">
            <i class="ti ti-plus me-1"></i>Tambah Baris Lampiran
        </button>
        <small class="form-hint d-block mt-2">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Maks 10MB per file.</small>
        @error('lampiran.*') <div class="text-danger">{{ $message }}</div> @enderror
    </div>
</div>
