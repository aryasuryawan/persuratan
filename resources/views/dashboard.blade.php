@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $namaUser = auth()->user()->name;
    $hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulanList = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $now = \Carbon\Carbon::now();
    $tanggalLabel = $hariList[$now->dayOfWeek] . ', ' . $now->day . ' ' . $bulanList[$now->month] . ' ' . $now->year;

    $statusKelasMap = [
        'received' => 'status-warning',
        'verified' => 'status-info',
        'forwarded_to_leadership' => 'status-info',
        'assigned' => 'status-info',
        'handled_by_leadership' => 'status-info',
        'in_progress' => 'status-info',
        'completed' => 'status-success',
        'archived' => 'status-success',
    ];
    $modulKelasMap = [
        'Komite Persetujuan' => 'tag-violet',
        'Notulensi RAPIM' => 'tag-cyan',
        'Surat Keputusan' => 'tag-purple',
    ];
@endphp

<style>
    .simpel-dash{
        --teal-700:#0E7C72; --teal-600:#128377; --teal-500:#16A398; --teal-100:var(--tblr-primary-lt);
        --gold-500:#E8A33D;
        --ink-900:var(--tblr-body-color); --ink-700:var(--tblr-body-color); --ink-500:var(--tblr-secondary-color); --ink-400:var(--tblr-secondary);
        --paper-0:var(--tblr-bg-surface); --paper-50:var(--tblr-bg-surface-secondary); --paper-100:var(--tblr-bg-surface-tertiary); --line:var(--tblr-border-color);
        --danger:#C5493E; --danger-bg:var(--tblr-danger-lt); --info:#2F7DC2; --info-bg:var(--tblr-info-lt); --success:#1E9E63; --success-bg:var(--tblr-success-lt); --warning:#B8791F; --warning-bg:var(--tblr-warning-lt);
        --violet:#6E5CC4; --violet-bg:var(--tblr-violet-lt); --cyan:#1C8FA6; --cyan-bg:var(--tblr-cyan-lt); --purple:#9250A6; --purple-bg:var(--tblr-purple-lt); --azure:#2C7FBF; --azure-bg:var(--tblr-blue-lt);
        --radius-lg:14px; --radius-md:12px; --radius-sm:8px;
        --shadow-card:0 1px 2px rgba(16,40,34,.05), 0 8px 24px -16px rgba(16,40,34,.16);
    }
    .simpel-dash{color:var(--ink-900); font-size:14px; -webkit-font-smoothing:antialiased;}
    .simpel-dash h1,.simpel-dash h2,.simpel-dash h3{font-weight:700; color:var(--ink-900);}
    .simpel-dash a{color:inherit; text-decoration:none;}
    .simpel-dash .icon{width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0;}

    /* page head */
    .simpel-dash .page-head{display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap;}
    .simpel-dash .eyebrow{font-size:.74rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--teal-600); margin-bottom:.35rem;}
    .simpel-dash .page-title{font-size:1.45rem; font-weight:800; margin:0;}
    .simpel-dash .page-desc{color:var(--ink-500); font-size:.87rem; margin-top:.3rem; max-width:46ch;}
    .simpel-dash .date-chip{display:flex; align-items:center; gap:.5rem; background:var(--paper-0); border:1px solid var(--line); padding:.5rem .85rem; border-radius:999px; font-size:.8rem; color:var(--ink-500); box-shadow:var(--shadow-card);}
    .simpel-dash .date-chip strong{color:var(--ink-900); font-weight:600;}

    /* KPI */
    .simpel-dash .kpi-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.15rem;}
    .simpel-dash .kpi-card{background:var(--paper-0); border:1px solid var(--line); border-radius:var(--radius-lg); padding:1.1rem 1.15rem 1rem; box-shadow:var(--shadow-card); position:relative; overflow:hidden;}
    .simpel-dash .kpi-top{display:flex; align-items:flex-start; justify-content:space-between; gap:.5rem;}
    .simpel-dash .kpi-label{font-size:.78rem; color:var(--ink-500); font-weight:600;}
    .simpel-dash .kpi-value{font-size:1.9rem; font-weight:800; color:var(--ink-900); margin-top:.15rem; line-height:1;}
    .simpel-dash .kpi-icon{width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;}
    .simpel-dash .kpi-icon .icon{width:18px; height:18px;}
    .simpel-dash .kpi-foot{display:flex; align-items:center; justify-content:space-between; margin-top:.7rem; font-size:.76rem; color:var(--ink-400);}
    .simpel-dash .kpi-trend{display:flex; align-items:center; gap:.3rem;}
    .simpel-dash .kpi-trend.up{color:var(--success);}
    .simpel-dash .kpi-link{color:var(--teal-600); font-weight:600; display:flex; align-items:center; gap:.2rem;}
    .simpel-dash .kpi-link:hover{text-decoration:underline;}
    .simpel-dash .kpi-primary .kpi-icon{background:var(--teal-100); color:var(--teal-700);}
    .simpel-dash .kpi-warning .kpi-icon{background:var(--warning-bg); color:var(--warning);}
    .simpel-dash .kpi-info .kpi-icon{background:var(--info-bg); color:var(--info);}
    .simpel-dash .kpi-success .kpi-icon{background:var(--success-bg); color:var(--success);}

    /* grid */
    .simpel-dash .grid-2col{display:grid; grid-template-columns:2fr 1fr; gap:1.15rem; align-items:start;}

    /* card */
    .simpel-dash .card{background:var(--paper-0); border:1px solid var(--line); border-radius:var(--radius-lg); box-shadow:var(--shadow-card); margin-bottom:1.15rem;}
    .simpel-dash .card-head{display:flex; align-items:center; justify-content:space-between; padding:1.05rem 1.2rem; border-bottom:1px solid var(--line); gap:.75rem;}
    .simpel-dash .card-title{font-size:.97rem; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:.5rem; margin:0;}
    .simpel-dash .card-title .punch{width:7px; height:7px; border-radius:50%; border:1.5px dashed var(--teal-500); flex-shrink:0;}
    .simpel-dash .card-link{font-size:.79rem; font-weight:600; color:var(--teal-600); display:flex; align-items:center; gap:.2rem; padding:.35rem .6rem; border-radius:999px;}
    .simpel-dash .card-link:hover{background:var(--teal-100);}

    /* table */
    .simpel-dash .table-wrap{overflow-x:auto;}
    .simpel-dash table{width:100%; border-collapse:collapse; font-size:.85rem;}
    .simpel-dash thead th{text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; color:var(--ink-400); font-weight:700; padding:.7rem 1.2rem; border-bottom:1px solid var(--line); white-space:nowrap;}
    .simpel-dash tbody td{padding:.85rem 1.2rem; border-bottom:1px solid var(--line); color:var(--ink-700); vertical-align:middle;}
    .simpel-dash tbody tr:last-child td{border-bottom:none;}
    .simpel-dash tbody tr:hover{background:var(--paper-50);}
    .simpel-dash .agenda-chip{font-size:.76rem; font-weight:600; background:var(--paper-100); color:var(--ink-700); padding:.22rem .5rem; border-radius:6px; letter-spacing:-.2px;}
    .simpel-dash .cell-title{max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:block;}
    .simpel-dash .cell-muted{color:var(--ink-400); white-space:nowrap;}
    .simpel-dash .row-go{width:1%;}
    .simpel-dash .go-btn{width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:var(--ink-400); background:none; border:none;}
    .simpel-dash tr:hover .go-btn{background:var(--paper-100); color:var(--teal-700);}

    /* status + tag */
    .simpel-dash .status{display:inline-flex; align-items:center; gap:.35rem; font-size:.76rem; font-weight:600; padding:.28rem .6rem .28rem .5rem; border-radius:999px; white-space:nowrap;}
    .simpel-dash .status-success{background:var(--success-bg); color:var(--success);}
    .simpel-dash .status-warning{background:var(--warning-bg); color:var(--warning);}
    .simpel-dash .status-info{background:var(--info-bg); color:var(--info);}
    .simpel-dash .tag{font-size:.72rem; font-weight:700; padding:.25rem .55rem; border-radius:6px; white-space:nowrap;}
    .simpel-dash .tag-violet{background:var(--violet-bg); color:var(--violet);}
    .simpel-dash .tag-cyan{background:var(--cyan-bg); color:var(--cyan);}
    .simpel-dash .tag-purple{background:var(--purple-bg); color:var(--purple);}

    /* quick actions */
    .simpel-dash .quick-grid{padding:1.1rem; display:grid; gap:.55rem;}
    .simpel-dash .qa-btn{display:flex; align-items:center; gap:.65rem; padding:.7rem .8rem; border-radius:var(--radius-md); border:1px solid var(--line); font-size:.85rem; font-weight:600; color:var(--ink-700); background:var(--paper-0); border-left:3px solid var(--qa-color, var(--teal-500));}
    .simpel-dash .qa-btn:hover{background:var(--paper-50); transform:translateX(2px);}
    .simpel-dash .qa-btn .icon{color:var(--qa-color, var(--teal-500));}

    /* mini stats */
    .simpel-dash .mini-stat-list{padding:.4rem .5rem 1rem;}
    .simpel-dash .mini-stat{display:flex; align-items:center; gap:.7rem; padding:.65rem .7rem; border-radius:var(--radius-md);}
    .simpel-dash .mini-stat:hover{background:var(--paper-50);}
    .simpel-dash .mini-ico{width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0;}
    .simpel-dash .mini-ico .icon{width:16px; height:16px;}
    .simpel-dash .mini-body{flex:1; min-width:0;}
    .simpel-dash .mini-label{font-size:.79rem; color:var(--ink-500); font-weight:500;}
    .simpel-dash .mini-value{font-weight:800; font-size:1.05rem; color:var(--ink-900);}
    .simpel-dash .mini-arrow{color:var(--ink-400);}

    /* empty state */
    .simpel-dash .empty{display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:1.8rem 1.4rem; color:var(--ink-400);}
    .simpel-dash .ring-big{width:52px; height:52px; border-radius:50%; border:2px dashed var(--line); display:flex; align-items:center; justify-content:center; margin-bottom:.8rem; color:var(--ink-400);}
    .simpel-dash .empty p{font-size:.83rem; max-width:30ch; margin:0;}
    .simpel-dash .empty strong{color:var(--ink-600); font-weight:600; display:block; margin-bottom:.15rem; font-size:.87rem;}
    .simpel-dash .need-item{display:flex; gap:.7rem; padding:.75rem 1.1rem; border-bottom:1px solid var(--line);}
    .simpel-dash .need-item:last-child{border-bottom:none;}
    .simpel-dash .need-dot{width:8px; height:8px; border-radius:50%; margin-top:.4rem; flex-shrink:0; background:var(--warning);}
    .simpel-dash .need-text{font-size:.82rem; color:var(--ink-700); line-height:1.4;}
    .simpel-dash .need-text b{font-weight:600;}

    @media (max-width:1100px){ .simpel-dash .kpi-grid{grid-template-columns:repeat(2,1fr);} .simpel-dash .grid-2col{grid-template-columns:1fr;} }
    @media (max-width:560px){ .simpel-dash .kpi-grid{grid-template-columns:1fr;} }
</style>

<div class="simpel-dash">

    <div class="page-head">
        <div>
            <div class="eyebrow">SiMPEL</div>
            <h1 class="page-title">Selamat datang kembali, {{ $namaUser }}</h1>
            <p class="page-desc">Ringkasan alur surat masuk, verifikasi, dan arsip per hari ini.</p>
        </div>
        <div class="date-chip">
            <svg class="icon" style="width:16px;height:16px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
            <span>{{ $tanggalLabel }}</span>
        </div>
    </div>

    <!-- KPI -->
    <div class="kpi-grid">
        <div class="card kpi-card kpi-primary">
            <div class="kpi-top">
                <div><div class="kpi-label">Surat Masuk</div><div class="kpi-value">{{ $totalSuratMasuk }}</div></div>
                <div class="kpi-icon"><svg class="icon" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg></div>
            </div>
            <div class="kpi-foot">
                <span class="kpi-trend up"><svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg> +{{ $suratHariIni }} hari ini</span>
            </div>
        </div>

        <div class="card kpi-card kpi-warning">
            <div class="kpi-top">
                <div><div class="kpi-label">Perlu Verifikasi</div><div class="kpi-value">{{ $perluVerifikasi }}</div></div>
                <div class="kpi-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M12 3l10 18H2L12 3z"/><path d="M12 10v4M12 17.5v.1"/></svg></div>
            </div>
            <div class="kpi-foot">
                <a href="{{ route('verifikasi-surat.index') }}" class="kpi-link">Lihat semua <svg class="icon" style="width:13px;height:13px;" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
        </div>

        <div class="card kpi-card kpi-info">
            <div class="kpi-top">
                <div><div class="kpi-label">Dalam Proses</div><div class="kpi-value">{{ $dalamProses }}</div></div>
                <div class="kpi-icon"><svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg></div>
            </div>
            <div class="kpi-foot"><span class="kpi-trend">sedang diproses</span></div>
        </div>

        <div class="card kpi-card kpi-success">
            <div class="kpi-top">
                <div><div class="kpi-label">Selesai</div><div class="kpi-value">{{ $selesai }}</div></div>
                <div class="kpi-icon"><svg class="icon" viewBox="0 0 24 24"><path d="M5 12l4 4 10-10"/></svg></div>
            </div>
            <div class="kpi-foot"><span class="kpi-trend up">{{ $persenSelesai }}% dari total</span></div>
        </div>
    </div>

    <div class="grid-2col">
        <!-- MAIN COLUMN -->
        <div class="col-main">

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><span class="punch"></span> Surat Masuk Terbaru</div>
                    <a href="{{ route('penerimaan-surat.index') }}" class="card-link">Lihat Semua <svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>No. Agenda</th><th>Pengirim</th><th>Perihal</th><th>Tanggal</th><th>Status</th><th class="row-go"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($suratTerbaru as $surat)
                            <tr>
                                <td><span class="agenda-chip">{{ $surat->nomor_agenda }}</span></td>
                                <td>{{ $surat->pengirim }}</td>
                                <td><span class="cell-title">{{ $surat->perihal }}</span></td>
                                <td class="cell-muted">{{ $surat->tanggal_diterima->format('d/m/Y') }}</td>
                                <td><span class="status {{ $statusKelasMap[$surat->status->kode] ?? 'status-info' }}">{{ $surat->status->label }}</span></td>
                                <td class="row-go"><a href="{{ route('penerimaan-surat.show', $surat->uuid) }}" class="go-btn"><svg class="icon" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6"><div class="empty"><div class="ring-big"><svg class="icon" viewBox="0 0 24 24"><path d="M5 12l4 4 10-10"/></svg></div><strong>Belum ada surat masuk</strong><p>Surat yang diterima akan tampil di sini.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><span class="punch"></span> Arsip Terbaru</div>
                    <a href="{{ route('komite-persetujuan.index') }}" class="card-link">Lihat Semua <svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>Modul</th><th>Judul</th><th>Tanggal</th><th class="row-go"></th></tr>
                        </thead>
                        <tbody>
                            @forelse($arsipTerbaru as $item)
                            <tr>
                                <td><span class="tag {{ $modulKelasMap[$item['modul']] ?? 'tag-violet' }}">{{ $item['modul'] }}</span></td>
                                <td><span class="cell-title">{{ $item['judul'] }}</span></td>
                                <td class="cell-muted">{{ $item['tanggal'] ? $item['tanggal']->format('d/m/Y') : '-' }}</td>
                                <td class="row-go"><a href="{{ $item['route'] }}" class="go-btn"><svg class="icon" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="4"><div class="empty"><div class="ring-big"><svg class="icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="4"/><path d="M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8"/><path d="M10 12h4"/></svg></div><strong>Belum ada arsip</strong><p>Arsip komite, notulensi, dan SK akan tampil di sini.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- SIDE COLUMN -->
        <div class="col-side">

            <div class="card">
                <div class="card-head"><div class="card-title"><span class="punch"></span> Aksi Cepat</div></div>
                <div class="quick-grid">
                    @can('create', App\Models\SuratMasuk::class)
                    <a href="{{ route('penerimaan-surat.create') }}" class="qa-btn" style="--qa-color:var(--teal-600);"><svg class="icon" viewBox="0 0 24 24"><rect x="2" y="6" width="14" height="11" rx="1.5"/><path d="M2 8l7 5 7-5"/><path d="M17 9l4 3-4 3"/></svg> Surat Masuk Baru</a>
                    @endcan
                    @can('create', App\Models\KomitePersetujuan::class)
                    <a href="{{ route('komite-persetujuan.create') }}" class="qa-btn" style="--qa-color:var(--violet);"><svg class="icon" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="12" rx="2"/><path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg> Catat Hasil Komite</a>
                    @endcan
                    @can('create', App\Models\NotulensiRapim::class)
                    <a href="{{ route('notulensi-rapim.create') }}" class="qa-btn" style="--qa-color:var(--cyan);"><svg class="icon" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg> Catat Notulensi RAPIM</a>
                    @endcan
                    @can('create', App\Models\SuratKeputusan::class)
                    <a href="{{ route('surat-keputusan.create') }}" class="qa-btn" style="--qa-color:var(--purple);"><svg class="icon" viewBox="0 0 24 24"><path d="M6 3h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><circle cx="12" cy="14" r="2.2"/></svg> Catat SK</a>
                    @endcan
                    <a href="{{ route('tracking.index') }}" class="qa-btn" style="--qa-color:var(--cyan);"><svg class="icon" viewBox="0 0 24 24"><circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h5a4 4 0 0 0 4-4V7"/></svg> Tracking Surat</a>
                    <a href="{{ route('disposisi.index') }}" class="qa-btn" style="--qa-color:var(--gold-500);"><svg class="icon" viewBox="0 0 24 24"><path d="M4 20l17-8L4 4l0 6.5L15 12 4 13.5V20z"/></svg> Disposisi</a>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><div class="card-title"><span class="punch"></span> Ringkasan Arsip</div></div>
                <div class="mini-stat-list">
                    <a href="{{ route('surat-keluar.index') }}" class="mini-stat">
                        <div class="mini-ico" style="background:var(--azure-bg); color:var(--azure);"><svg class="icon" viewBox="0 0 24 24"><path d="M4 20l17-8L4 4l0 6.5L15 12 4 13.5V20z"/></svg></div>
                        <div class="mini-body"><div class="mini-label">Surat Keluar</div><div class="mini-value">{{ $suratKeluar }}</div></div>
                        <svg class="icon mini-arrow" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('komite-persetujuan.index') }}" class="mini-stat">
                        <div class="mini-ico" style="background:var(--violet-bg); color:var(--violet);"><svg class="icon" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="12" rx="2"/><path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg></div>
                        <div class="mini-body"><div class="mini-label">Komite Persetujuan</div><div class="mini-value">{{ $komitePersetujuan }}</div></div>
                        <svg class="icon mini-arrow" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('notulensi-rapim.index') }}" class="mini-stat">
                        <div class="mini-ico" style="background:var(--cyan-bg); color:var(--cyan);"><svg class="icon" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></div>
                        <div class="mini-body"><div class="mini-label">Notulensi RAPIM</div><div class="mini-value">{{ $notulensiRapim }}</div></div>
                        <svg class="icon mini-arrow" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('surat-keputusan.index') }}" class="mini-stat">
                        <div class="mini-ico" style="background:var(--purple-bg); color:var(--purple);"><svg class="icon" viewBox="0 0 24 24"><path d="M6 3h9l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><circle cx="12" cy="14" r="2.2"/></svg></div>
                        <div class="mini-body"><div class="mini-label">Surat Keputusan</div><div class="mini-value">{{ $suratKeputusan }}</div></div>
                        <svg class="icon mini-arrow" viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><div class="card-title"><span class="punch"></span> Perlu Tindakan</div></div>
                @if($perluVerifikasi > 0)
                <div class="need-item">
                    <span class="need-dot"></span>
                    <div>
                        <div class="need-text"><b>{{ $perluVerifikasi }} surat</b> menunggu verifikasi</div>
                        <a href="{{ route('verifikasi-surat.index') }}" class="card-link" style="margin-top:.3rem;">Verifikasi sekarang <svg class="icon" style="width:13px;height:13px;" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                    </div>
                </div>
                @else
                <div class="empty">
                    <div class="ring-big"><svg class="icon" viewBox="0 0 24 24"><path d="M5 12l4 4 10-10"/></svg></div>
                    <strong>Semua beres</strong>
                    <p>Tidak ada surat yang menunggu verifikasi atau melewati tenggat hari ini.</p>
                </div>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
