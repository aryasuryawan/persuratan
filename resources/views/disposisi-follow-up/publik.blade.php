@extends('layouts.guest')

@section('title', 'Lapor Follow Up Disposisi')

@section('content')
<div class="text-center mb-4">
    @if(\App\Models\Setting::get('app_logo'))
    <a href="/" class="navbar-brand navbar-brand-autodark">
        <img src="{{ \Storage::disk('public')->url(\App\Models\Setting::get('app_logo')) }}" alt="{{ \App\Models\Setting::get('app_name', 'SiMPEL') }}" style="height: 32px;">
    </a>
    @endif
</div>

<div class="card card-md">
    <div class="card-body">
        <h2 class="h2 text-center mb-1">Lapor Follow Up Disposisi</h2>
        <p class="text-secondary text-center mb-4">Isi laporan perkembangan surat berikut melalui tautan khusus ini.</p>

        @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-warning" role="alert">{{ session('error') }}</div>
        @endif

        @if($sudahDiisi)
        <div class="alert alert-success" role="alert">
            <div class="fw-semibold mb-1">Laporan follow-up sudah dikirim.</div>
            <div class="text-secondary">Link ini tidak lagi aktif karena laporan sudah diisi.</div>
            @php
                $laporan = $disposisi->followUps()->orderByDesc('created_at')->first();
            @endphp
            @if($laporan)
            <hr class="my-2">
            <div class="text-secondary mb-1">Dikirim pada <strong>{{ $laporan->created_at->format('d/m/Y H:i') }}</strong></div>
            <div class="text-secondary">{{ $laporan->keterangan }}</div>
            @endif
        </div>
        @else
        <div class="mb-4 p-3 border rounded-3 bg-light-subtle">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr><td class="text-secondary fw-medium" style="width:130px;">No. Agenda</td><td><strong>{{ $surat->nomor_agenda }}</strong></td></tr>
                    <tr><td class="text-secondary fw-medium">No. Surat</td><td>{{ $surat->nomor_surat ?? '-' }}</td></tr>
                    <tr><td class="text-secondary fw-medium">Perihal</td><td>{{ $surat->perihal }}</td></tr>
                    <tr><td class="text-secondary fw-medium">Pengirim</td><td>{{ $surat->pengirim }}</td></tr>
                    <tr><td class="text-secondary fw-medium">Disposisi Kepada</td><td>{{ !empty($disposisi->disposisi_kepada) ? implode(', ', $disposisi->disposisi_kepada) : ($disposisi->kepadaUser?->name ?? '-') }}</td></tr>
                    @if($disposisi->tanggal_deadline)
                    <tr><td class="text-secondary fw-medium">Batas Waktu</td><td>{{ $disposisi->tanggal_deadline->format('d/m/Y') }}</td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        <form method="POST" action="{{ route('follow-up.publik.kirim', $disposisi->uuid) }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label required">Keterangan / Laporan Follow Up</label>
                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" placeholder="Tuliskan hasil / progres pengerjaan surat ini..." required>{{ old('keterangan') }}</textarea>
                @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Lampiran</label>
                <input type="file" name="lampiran[]" class="form-control @error('lampiran.*') is-invalid @enderror" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp">
                <div class="form-hint">Maks 10MB per file. Format: JPG, PNG, PDF, atau file Office (Word/Excel/PowerPoint).</div>
                @error('lampiran.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-footer">
                <button type="submit" class="btn btn-primary w-100">Kirim Laporan Follow Up</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
