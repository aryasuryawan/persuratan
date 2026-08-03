@php $riwayat = $rekaman->riwayatArsip ?? null; @endphp

@if(optional($riwayat)->isNotEmpty())
<div class="card mt-3">
    <div class="card-header"><h3>Riwayat Perubahan Data</h3></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Field</th>
                        <th>Lama</th>
                        <th>Baru</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat as $r)
                    <tr>
                        <td style="color:var(--muted);">{{ $r->created_at ? $r->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $r->label ?? $r->field }}</td>
                        <td>{{ $r->nilai_lama ?? '-' }}</td>
                        <td>{{ $r->nilai_baru ?? '-' }}</td>
                        <td>{{ $r->dilakukanOleh?->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
