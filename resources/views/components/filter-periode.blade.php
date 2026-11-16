@props([
    'id' => 'filter-periode',
    'dariId' => 'filter-periode-dari',
    'sampaiId' => 'filter-periode-sampai',
    'nameDari' => 'periode_dari',
    'nameSampai' => 'periode_sampai',
])

@php
    $dariVal = request($nameDari, '');
    $sampaiVal = request($nameSampai, '');
@endphp

<div class="dropdown filter-periode">
    <input type="text" id="{{ $id }}" class="form-control form-control-sm" style="width: 220px; cursor: pointer; background:#fff;"
        placeholder="Pilih periode" readonly data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
    <input type="hidden" id="{{ $dariId }}" name="{{ $nameDari }}" value="{{ $dariVal }}">
    <input type="hidden" id="{{ $sampaiId }}" name="{{ $nameSampai }}" value="{{ $sampaiVal }}">
    <div class="dropdown-menu p-3" style="min-width: 280px;">
        <div class="mb-2">
            <label class="form-label form-label-sm mb-1">Dari</label>
            <input type="date" class="form-control form-control-sm fp-dari" value="{{ $dariVal }}">
        </div>
        <div class="mb-2">
            <label class="form-label form-label-sm mb-1">Sampai</label>
            <input type="date" class="form-control form-control-sm fp-sampai" value="{{ $sampaiVal }}">
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-sm btn-ghost-secondary fp-clear">Reset</button>
            <button type="button" class="btn btn-sm btn-primary fp-apply">Terapkan</button>
        </div>
    </div>
</div>
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.filter-periode').forEach(function (wrap) {
        if (wrap.dataset.fpInit) return;
        wrap.dataset.fpInit = '1';
        const display = wrap.querySelector('input[readonly]');
        const dariHidden = wrap.querySelector('input[type=hidden]:nth-of-type(1)');
        const sampaiHidden = wrap.querySelector('input[type=hidden]:nth-of-type(2)');
        const dariInput = wrap.querySelector('.fp-dari');
        const sampaiInput = wrap.querySelector('.fp-sampai');
        const applyBtn = wrap.querySelector('.fp-apply');
        const clearBtn = wrap.querySelector('.fp-clear');

        function fmt(d) {
            if (!d) return '';
            const p = d.split('-');
            return p.length === 3 ? (p[2] + '/' + p[1] + '/' + p[0]) : d;
        }
        function syncDisplay() {
            const d = dariHidden.value, s = sampaiHidden.value;
            if (d && s) display.value = fmt(d) + ' - ' + fmt(s);
            else if (d) display.value = fmt(d) + ' - ...';
            else if (s) display.value = '... - ' + fmt(s);
            else display.value = '';
        }
        function emit() {
            [dariHidden, sampaiHidden, display].forEach(el => el.dispatchEvent(new Event('change', { bubbles: true })));
        }
        applyBtn?.addEventListener('click', function () {
            dariHidden.value = dariInput.value || '';
            sampaiHidden.value = sampaiInput.value || '';
            syncDisplay();
            emit();
            const dd = bootstrap.Dropdown.getOrCreateInstance(display);
            dd.hide();
        });
        clearBtn?.addEventListener('click', function () {
            dariInput.value = ''; sampaiInput.value = '';
            dariHidden.value = ''; sampaiHidden.value = '';
            syncDisplay();
            emit();
        });
        display?.addEventListener('show.bs.dropdown', function () {
            dariInput.value = dariHidden.value || '';
            sampaiInput.value = sampaiHidden.value || '';
        });
        syncDisplay();
    });
});
</script>
@endpush
@endonce
