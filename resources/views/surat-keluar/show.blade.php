@extends('layouts.app')

@section('page-title', 'Detail Surat Keluar')

@section('page-header')
@endsection

@section('content')
<div class="page-header d-print-none mb-3">
    <div class="row align-items-center">
        <div class="col">
            <div class="page-pretitle">Surat Keluar</div>
            <h2 class="page-title d-flex align-items-center gap-2 flex-wrap">
                {{ $surat->perihal }}
                @if($surat->klasifikasi && $surat->klasifikasi->kode === 'TERBATAS')
                <span class="badge bg-dark-lt text-dark"><i class="ti ti-shield-lock icon icon-1 me-1"></i>Restricted</span>
                @endif
            </h2>
            <div class="text-secondary mt-1">
                @if($surat->nomor_surat)
                <span class="letter-code">{{ $surat->nomor_surat }}</span>
                <span class="mx-2">·</span>
                @endif
                Dibuat oleh {{ $surat->pembuat->name }}, {{ $surat->created_at->format('d/m/Y') }}
            </div>
        </div>
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">
                <a href="{{ route('surat-keluar.index') }}" class="btn btn-outline-secondary d-none d-sm-inline-block"><i class="ti ti-arrow-left icon"></i> Kembali</a>
                <a href="#" class="btn btn-outline-secondary" onclick="window.print()"><i class="ti ti-printer icon"></i> <span class="d-none d-sm-inline">Cetak</span></a>
                <a href="{{ route('surat-keluar.edit', $surat->uuid) }}" class="btn btn-success"><i class="ti ti-edit icon"></i> <span class="d-none d-sm-inline">Edit</span></a>
                <form method="POST" action="{{ route('surat-keluar.destroy', $surat->uuid) }}" class="d-inline" onsubmit="return confirm('Hapus surat keluar ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="ti ti-trash icon"></i> <span class="d-none d-sm-inline">Hapus</span></button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row row-cards">
    <!-- MAIN COLUMN -->
    <div class="col-12 col-lg-8">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-file-text me-2 text-secondary"></i>Informasi Surat</h3>
            </div>
            <div class="card-body">
                <div class="datagrid">
                    @if($surat->nomor_surat)
                    <div class="datagrid-item">
                        <div class="datagrid-title">No. Surat</div>
                        <div class="datagrid-content letter-code">{{ $surat->nomor_surat }}</div>
                    </div>
                    @endif
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tanggal Surat</div>
                        <div class="datagrid-content">{{ $surat->tanggal_surat->format('d/m/Y') }}</div>
                    </div>
                    <div class="datagrid-item">
                        <div class="datagrid-title">Tujuan</div>
                        <div class="datagrid-content">{{ $surat->tujuan_surat }}</div>
                    </div>
                    @if($surat->kodeSurat)
                    <div class="datagrid-item">
                        <div class="datagrid-title">Kode Surat</div>
                        <div class="datagrid-content">
                            <span class="badge bg-blue-lt">{{ $surat->kodeSurat->kode }}</span>
                            <span class="text-secondary">{{ $surat->kodeSurat->nama }}</span>
                        </div>
                    </div>
                    @endif
                    <div class="datagrid-item datagrid-item-full" style="grid-column:1/-1;">
                        <div class="datagrid-title">Perihal</div>
                        <div class="datagrid-content">{{ $surat->perihal }}</div>
                    </div>
                    @if($surat->pimpinanPengirim)
                    <div class="datagrid-item">
                        <div class="datagrid-title">Pimpinan Pengirim</div>
                        <div class="datagrid-content d-flex align-items-center gap-2">
                            <span class="avatar avatar-xs avatar-soft fw-bold">{{ strtoupper(substr($surat->pimpinanPengirim->name, 0, 2)) }}</span>
                            <span>{{ $surat->pimpinanPengirim->name }}
                                @if($surat->pimpinanPengirim->title ?? $surat->pimpinanPengirim->jabatan)
                                <span class="text-secondary">— {{ $surat->pimpinanPengirim->title ?? $surat->pimpinanPengirim->jabatan }}</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @endif
                    <div class="datagrid-item">
                        <div class="datagrid-title">Klasifikasi</div>
                        <div class="datagrid-content">
                            <x-badge-klasifikasi :klasifikasi="$surat->klasifikasi" />
                        </div>
                    </div>
                    @if($surat->topiks->count() > 0)
                    <div class="datagrid-item">
                        <div class="datagrid-title">Topik</div>
                        <div class="datagrid-content">
                            <x-badge-topik :topiks="$surat->topiks" />
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($surat->lampiran && count($surat->lampiran) > 0)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="ti ti-paperclip me-2 text-secondary"></i>Lampiran</h3>
            </div>
            <div class="card-body">
                @foreach($surat->lampiran as $file)
                <div class="file-row">
                    <div class="file-ico">
                        @if(str_contains($file['mime_type'] ?? '', 'pdf'))
                        <i class="ti ti-file-type-pdf"></i>
                        @elseif(str_contains($file['mime_type'] ?? '', 'image'))
                        <i class="ti ti-file-type-png"></i>
                        @else
                        <i class="ti ti-file"></i>
                        @endif
                    </div>
                    <div class="flex-fill min-w-0">
                        <div class="fw-medium text-truncate">{{ $file['original_name'] }}</div>
                        <div class="text-secondary small">
                            {{ strtoupper($file['mime_type'] ?? 'file') }} ·
                            {{ number_format(($file['file_size'] ?? 0) / 1024, 1) }} KB
                        </div>
                    </div>
                    <div class="btn-list">
                        <a href="{{ \Storage::disk('public')->url($file['stored_path']) }}" target="_blank" class="btn btn-icon btn-outline-secondary" title="Lihat"><i class="ti ti-eye"></i></a>
                        <a href="{{ \Storage::disk('public')->url($file['stored_path']) }}" download class="btn btn-outline-secondary"><i class="ti ti-download icon"></i> Unduh</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- SIDE COLUMN -->
    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title"><i class="ti ti-history me-2 text-secondary"></i>Riwayat Pencatatan</h3></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="d-flex gap-2 mb-3">
                        <span class="timeline-dot mt-1" style="background:var(--dd-green);"></span>
                        <div>
                            <div class="fw-medium">Surat dicatat</div>
                            <div class="text-secondary small">oleh {{ $surat->pembuat->name }} · {{ $surat->created_at->format('d/m/Y, H.i') }}</div>
                        </div>
                    </li>
                    @if($surat->updated_at->diffInSeconds($surat->created_at) > 1)
                    <li class="d-flex gap-2 mb-3">
                        <span class="timeline-dot mt-1 bg-secondary-lt"></span>
                        <div>
                            <div class="fw-medium">Surat diperbarui</div>
                            <div class="text-secondary small">oleh {{ $surat->pembuat->name }} · {{ $surat->updated_at->format('d/m/Y, H.i') }}</div>
                        </div>
                    </li>
                    @else
                    <li class="d-flex gap-2">
                        <span class="timeline-dot mt-1 bg-secondary-lt"></span>
                        <div>
                            <div class="fw-medium text-secondary">Belum ada perubahan lain</div>
                            <div class="text-secondary small">Riwayat edit akan muncul di sini</div>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </div>

        @if($surat->suratMasuk)
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title"><i class="ti ti-link me-2 text-secondary"></i>Terkait Surat Masuk</h3></div>
            <div class="card-body">
                <a href="{{ route('penerimaan-surat.show', $surat->suratMasuk->uuid) }}" class="related-card text-decoration-none">
                    <div class="file-ico"><i class="ti ti-mail"></i></div>
                    <div class="flex-fill">
                        <div class="letter-code d-inline-block mb-1">{{ $surat->suratMasuk->nomor_agenda }}</div>
                        <div class="text-secondary small">{{ $surat->suratMasuk->perihal }}</div>
                    </div>
                    <i class="ti ti-chevron-right text-secondary"></i>
                </a>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="subtle-label mb-2">Ringkasan Status</div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-green-lt"><i class="ti ti-check icon icon-1 me-1"></i>Tercatat</span>
                    <span class="text-secondary small">Surat keluar sudah dicatat</span>
                </div>
                <hr class="my-3">
                <div class="d-flex justify-content-between small mb-1"><span class="text-secondary">Dicatat oleh</span><span class="fw-medium">{{ $surat->pembuat->name }}</span></div>
                <div class="d-flex justify-content-between small"><span class="text-secondary">Terakhir diubah</span><span class="fw-medium">{{ $surat->updated_at->diffInSeconds($surat->created_at) > 1 ? $surat->updated_at->format('d/m/Y H:i') : '—' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
  :root{ --dd-green:#1E8E5A; --dd-green-dark:#0E6A40; }
  .letter-code{ font-family:ui-monospace, SFMono-Regular, Menlo, monospace; font-size:.8rem; background:var(--tblr-bg-surface-tertiary); padding:.15rem .5rem; border-radius:6px; }
  .avatar-soft{ background:rgba(30,142,90,.12); color:var(--dd-green-dark); }
  .datagrid-item{ padding-block:.65rem; }
  .datagrid-title{ font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; }
  .file-row{ display:flex; align-items:center; gap:.85rem; padding:.9rem 1rem; border:1px solid var(--tblr-border-color); border-radius:8px; }
  .file-ico{ width:42px; height:42px; border-radius:8px; background:rgba(30,142,90,.1); color:var(--dd-green-dark); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.15rem; }
  .timeline-dot{ width:10px; height:10px; border-radius:50%; }
  .related-card{ border:1px dashed var(--tblr-border-color); border-radius:10px; padding:.85rem 1rem; display:flex; align-items:center; gap:.75rem; }
  .subtle-label{ font-size:.72rem; color:var(--tblr-secondary); text-transform:uppercase; letter-spacing:.04em; font-weight:600; }
  .page-pretitle{ color:var(--dd-green-dark)!important; }
</style>
@endpush