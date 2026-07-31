<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ArsipFormRequest extends FormRequest
{
    protected function rulesBerkas(): array
    {
        return [
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
            'lampiran_keterangan' => ['nullable', 'array'],
            'lampiran_keterangan.*' => ['nullable', 'string', 'max:255'],
            'foto_kegiatan' => ['nullable', 'array'],
            'foto_kegiatan.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'foto_caption' => ['nullable', 'string', 'max:255'],
            'hapus_lampiran' => ['nullable', 'array'],
            'hapus_lampiran.*' => ['integer'],
            'hapus_foto' => ['nullable', 'array'],
            'hapus_foto.*' => ['integer'],
        ];
    }
}
