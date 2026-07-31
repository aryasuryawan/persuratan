@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success']) }}>
        <div class="d-flex">
            <div>
                <i class="ti ti-circle-check"></i>
            </div>
            <div class="ms-2">
                {{ $status }}
            </div>
        </div>
    </div>
@endif
