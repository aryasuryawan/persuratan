@php
    $appName = \App\Models\Setting::get('app_name', 'Aplikasi Surat');
    $lembaga = \App\Models\Setting::get('lembaga_nama', '');
    $alamat = \App\Models\Setting::get('lembaga_alamat', '');
    $telepon = \App\Models\Setting::get('lembaga_telepon', '');
    $email = \App\Models\Setting::get('lembaga_email', '');
    $website = \App\Models\Setting::get('lembaga_website', '');
    $logo = \App\Models\Setting::get('app_logo');
    $disposisi = $surat->disposisi;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Disposisi - {{ $surat->nomor_agenda }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000; padding: 20px 30px; }
        .kop { text-align: center; margin-bottom: 4px; }
        .kop-logo { max-height: 60px; margin-bottom: 4px; }
        .kop-nama { font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop-lembaga { font-size: 13px; font-weight: bold; margin-top: 2px; }
        .kop-alamat { font-size: 10px; margin-top: 2px; }
        .garis { border-bottom: 3px double #000; margin: 6px 0 14px; }
        .judul { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 14px; letter-spacing: 1px; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.info td { padding: 3px 6px; vertical-align: top; }
        table.info td.label { width: 160px; font-weight: bold; }
        table.disposisi { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.disposisi th, table.disposisi td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; }
        table.disposisi th { background: #f0f0f0; text-align: center; }
        .isi-catatan { min-height: 70px; }
        .tandatangan { width: 100%; margin-top: 28px; }
        .tandatangan table { width: 100%; border-collapse: collapse; }
        .tandatangan td { text-align: center; width: 33.33%; padding: 0 6px; vertical-align: top; font-size: 11px; }
        .tandatangan .judul-ttd { margin-bottom: 40px; }
        .tandatangan .nama { font-weight: bold; text-decoration: underline; margin-top: 2px; }
        .tandatangan .jabatan { font-size: 10px; }
        .keterangan-followup { margin-top: 14px; }
        .keterangan-followup table { width: 100%; border-collapse: collapse; }
        .keterangan-followup th, .keterangan-followup td { border: 1px solid #000; padding: 5px 8px; vertical-align: top; }
        .keterangan-followup th { background: #f0f0f0; }
        .btn-cetak { display: block; margin: 0 auto 16px; padding: 10px 28px; font-size: 14px; cursor: pointer; }
        @media print {
            .btn-cetak { display: none; }
            body { padding: 0; }
            @page { size: A4 portrait; margin: 12mm; }
        }
    </style>
</head>
<body>
    <button type="button" class="btn-cetak" onclick="window.print()">Cetak</button>

    <div class="kop">
        @if($logo)
        <img src="{{ \Storage::disk('public')->url($logo) }}" alt="Logo" class="kop-logo">
        @endif
        <div class="kop-nama">{{ $appName }}</div>
        @if($lembaga)<div class="kop-lembaga">{{ $lembaga }}</div>@endif
        @if($alamat)
        <div class="kop-alamat">{{ $alamat }}{{ $telepon ? ' | Telp: ' . $telepon : '' }}{{ $email ? ' | Email: ' . $email : '' }}{{ $website ? ' | ' . $website : '' }}</div>
        @endif
    </div>
    <div class="garis"></div>

    <div class="judul">LEMBAR DISPOSISI</div>

    <table class="info">
        <tr>
            <td class="label">Nomor Agenda</td>
            <td>: {{ $surat->nomor_agenda }}</td>
            <td class="label">Nomor Surat</td>
            <td>: {{ $surat->nomor_surat ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Surat</td>
            <td>: {{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '-' }}</td>
            <td class="label">Tanggal Diterima</td>
            <td>: {{ $surat->tanggal_diterima->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Pengirim</td>
            <td>: {{ $surat->pengirim }}</td>
            <td class="label">Sifat</td>
            <td>: {{ $surat->klasifikasi->nama }}</td>
        </tr>
        <tr>
            <td class="label">Perihal</td>
            <td colspan="3">: {{ $surat->perihal }}</td>
        </tr>
    </table>

    <table class="disposisi">
        <thead>
            <tr>
                <th style="width:26%">Ditujukan Kepada</th>
                <th style="width:48%">Isi Disposisi / Instruksi</th>
                <th style="width:26%">Catatan / Batas Waktu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($disposisi as $d)
            <tr>
                <td>
                    @if($d->kepadaUser)
                        <strong>{{ $d->kepadaUser->name }}</strong>
                        @if($d->kepadaUser->jabatan)
                        <br>{{ $d->kepadaUser->jabatan }}
                        @endif
                        @if($d->kepadaUser->direktorat || $d->kepadaUser->divisi || $d->kepadaUser->departemen)
                        <br><small>{{ $d->kepadaUser->direktorat?->nama ?? '' }}{{ $d->kepadaUser->divisi?->nama ? ' / ' . $d->kepadaUser->divisi->nama : '' }}{{ $d->kepadaUser->departemen?->nama ? ' / ' . $d->kepadaUser->departemen->nama : '' }}</small>
                        @endif
                    @elseif($d->jenis === 'langsung_pimpinan')
                        <strong>Langsung Pimpinan</strong>
                    @else
                        -
                    @endif
                </td>
                <td class="isi-catatan">
                    {{ $d->catatan ?: 'Mohon periksa dan tindak lanjuti.' }}
                    @if($d->atasNamaPimpinan)
                    <br><small>(Atas nama {{ $d->atasNamaPimpinan->name }})</small>
                    @endif
                </td>
                <td>
                    @if($d->tanggal_deadline)
                    <strong>Batas waktu:</strong> {{ $d->tanggal_deadline->format('d/m/Y') }}
                    @endif
                    @if($d->catatan_pimpinan)
                    <br><strong>Catatan pimpinan:</strong> {{ $d->catatan_pimpinan }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="isi-catatan">Belum ada disposisi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($disposisi->isNotEmpty() && $disposisi->pluck('followUps')->flatten()->isNotEmpty())
    <div class="keterangan-followup">
        <strong>Hasil Tindak Lanjut / Follow Up:</strong>
        <table style="margin-top:6px;">
            <thead>
                <tr>
                    <th style="width:18%">Tanggal</th>
                    <th style="width:26%">Oleh</th>
                    <th style="width:56%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disposisi as $d)
                    @foreach($d->followUps as $fu)
                    <tr>
                        <td>{{ $fu->created_at->format('d/m/Y') }}</td>
                        <td>{{ $fu->user->name ?? '-' }}</td>
                        <td>{{ $fu->keterangan }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="tandatangan">
        <table>
            <tr>
                <td>
                    <div class="judul-ttd">Penerima,</div>
                    <br><br><br>
                    <div class="nama">( ............................. )</div>
                    <div class="jabatan">.............................</div>
                </td>
                <td>
                    <div class="judul-ttd">Mengetahui,</div>
                    <br><br><br>
                    <div class="nama">( ............................. )</div>
                    <div class="jabatan">.............................</div>
                </td>
                <td>
                    <div class="judul-ttd">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Disposisikan oleh,</div>
                    <br><br><br>
                    <div class="nama">( ............................. )</div>
                    <div class="jabatan">.............................</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
