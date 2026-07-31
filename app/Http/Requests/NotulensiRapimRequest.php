<?php

namespace App\Http\Requests;

class NotulensiRapimRequest extends ArsipFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'judul_rapat' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'klasifikasi_id' => ['nullable', 'exists:klasifikasi,id'],
            'daftar_peserta' => ['nullable', 'string'],
            'ringkasan_pembahasan' => ['required', 'string'],
            'keputusan_arahan' => ['nullable', 'string'],
        ], $this->rulesBerkas());
    }
}
