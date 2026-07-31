@props([
    'headers' => [],
    'rows' => [],
    'emptyMessage' => 'Belum ada data',
    'actions' => null,
    'striped' => true,
    'hover' => true,
    'vcenter' => true,
    'responsive' => true,
])

@php
    $tableClasses = 'table';
    $tableClasses .= $striped ? ' table-striped' : '';
    $tableClasses .= $hover ? ' table-hover' : '';
    $tableClasses .= $vcenter ? ' table-vcenter' : '';
    $tableClasses .= ' card-table';
@endphp

<div class="{{ $responsive ? 'table-responsive' : '' }}">
    <table class="{{ $tableClasses }}">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th {{ isset($header['width']) ? 'style="width: '.$header['width'].'"' : '' }} {{ isset($header['class']) ? 'class="'.$header['class'].'"' : '' }}>
                        {{ $header['label'] ?? $header }}
                    </th>
                @endforeach
                @if($actions)
                    <th class="text-end" style="width: 1%">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr {{ isset($row['trAttrs']) ? $row['trAttrs'] : '' }}>
                    @foreach($headers as $header)
                        <td {{ isset($header['class']) ? 'class="'.$header['class'].'"' : '' }}>
                            @php
                                $key = $header['key'] ?? $header;
                                $value = data_get($row, $key);
                                $formatter = $header['formatter'] ?? null;
                            @endphp
                            @if($formatter)
                                {{ $formatter($value, $row) }}
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                    @if($actions)
                        <td class="text-end">
                            {{ $actions($row) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="text-center text-muted py-4">
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>