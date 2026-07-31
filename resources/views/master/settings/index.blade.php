@extends('layouts.app')

@section('page-title', 'Pengaturan')

@section('page-header')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pengaturan Aplikasi</h3>
    </div>
    <form method="POST" action="{{ route('master.settings.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Aplikasi</label>
                        <input type="text" name="app_name" class="form-control @error('app_name') is-invalid @enderror" value="{{ old('app_name', $settings['app_name']->value ?? '') }}">
                        @error('app_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Nama Lembaga</label>
                        <input type="text" name="lembaga_nama" class="form-control @error('lembaga_nama') is-invalid @enderror" value="{{ old('lembaga_nama', $settings['lembaga_nama']->value ?? '') }}">
                        @error('lembaga_nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Logo Aplikasi</label>
                        <input type="file" name="app_logo" class="form-control @error('app_logo') is-invalid @enderror" accept="image/jpg,image/jpeg,image/png,image/svg+xml,image/webp">
                        @error('app_logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if(isset($settings['app_logo']) && $settings['app_logo']->value)
                        <div class="mt-2">
                            <img src="{{ Storage::disk('public')->url($settings['app_logo']->value) }}" alt="Logo" style="max-height: 40px;">
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Favicon</label>
                        <input type="file" name="app_favicon" class="form-control @error('app_favicon') is-invalid @enderror" accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/webp">
                        @error('app_favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if(isset($settings['app_favicon']) && $settings['app_favicon']->value)
                        <div class="mt-2">
                            <img src="{{ Storage::disk('public')->url($settings['app_favicon']->value) }}" alt="Favicon" style="max-height: 32px;">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Alamat Lembaga</label>
                <textarea name="lembaga_alamat" class="form-control @error('lembaga_alamat') is-invalid @enderror" rows="3">{{ old('lembaga_alamat', $settings['lembaga_alamat']->value ?? '') }}</textarea>
                @error('lembaga_alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="lembaga_telepon" class="form-control @error('lembaga_telepon') is-invalid @enderror" value="{{ old('lembaga_telepon', $settings['lembaga_telepon']->value ?? '') }}">
                        @error('lembaga_telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="lembaga_email" class="form-control @error('lembaga_email') is-invalid @enderror" value="{{ old('lembaga_email', $settings['lembaga_email']->value ?? '') }}">
                        @error('lembaga_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Website</label>
                <input type="url" name="lembaga_website" class="form-control @error('lembaga_website') is-invalid @enderror" value="{{ old('lembaga_website', $settings['lembaga_website']->value ?? '') }}">
                @error('lembaga_website') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Teks Footer</label>
                <textarea name="footer_text" class="form-control @error('footer_text') is-invalid @enderror" rows="2">{{ old('footer_text', $settings['footer_text']->value ?? '') }}</textarea>
                @error('footer_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Copyright Footer</label>
                <input type="text" name="footer_copyright" class="form-control @error('footer_copyright') is-invalid @enderror" value="{{ old('footer_copyright', $settings['footer_copyright']->value ?? '') }}" placeholder="Contoh: © 2026 Nama Lembaga">
                @error('footer_copyright') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Link Footer (Dokumentasi & Panduan)</label>
                @php
                    $footerLinks = \App\Models\Setting::getArray('footer_links', []);
                    if (empty($footerLinks)) {
                        $footerLinks = [['label' => '', 'url' => ''], ['label' => '', 'url' => '']];
                    }
                @endphp
                <div id="footer-links-container">
                    @foreach($footerLinks as $i => $link)
                    <div class="row g-2 mb-2 footer-link-row">
                        <div class="col-5">
                            <input type="text" name="footer_links[{{ $i }}][label]" class="form-control form-control-sm" placeholder="Label (contoh: Dokumentasi)" value="{{ $link['label'] ?? '' }}">
                        </div>
                        <div class="col-5">
                            <input type="url" name="footer_links[{{ $i }}][url]" class="form-control form-control-sm" placeholder="URL" value="{{ $link['url'] ?? '' }}">
                        </div>
                        <div class="col-2 d-flex align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-footer-link" {{ count($footerLinks) <= 1 ? 'disabled' : '' }}>Hapus</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" id="add-footer-link" class="btn btn-sm btn-outline-secondary">Tambah Link</button>
                <small class="form-hint">Link yang ditampilkan di footer (Dokumentasi & Panduan). Kosongkan untuk menghapus.</small>
            </div>
        </div>
        <div class="card-footer d-flex align-items-center">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('footer-links-container');
    const addButton = document.getElementById('add-footer-link');
    let linkCount = container.querySelectorAll('.footer-link-row').length;

    addButton.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 mb-2 footer-link-row';
        newRow.innerHTML = `
            <div class="col-5">
                <input type="text" name="footer_links[${linkCount}][label]" class="form-control form-control-sm" placeholder="Label (contoh: Dokumentasi)">
            </div>
            <div class="col-5">
                <input type="url" name="footer_links[${linkCount}][url]" class="form-control form-control-sm" placeholder="URL">
            </div>
            <div class="col-2 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-outline-danger w-100 remove-footer-link">Hapus</button>
            </div>
        `;
        container.appendChild(newRow);
        linkCount++;
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        const rows = container.querySelectorAll('.footer-link-row');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.remove-footer-link');
            btn.disabled = rows.length <= 1;
            btn.onclick = function() {
                container.removeChild(row);
                updateRemoveButtons();
            };
        });
    }

    container.querySelectorAll('.remove-footer-link').forEach(btn => {
        btn.onclick = function() {
            const row = this.closest('.footer-link-row');
            container.removeChild(row);
            updateRemoveButtons();
        };
    });

    updateRemoveButtons();
});
</script>
@endpush
