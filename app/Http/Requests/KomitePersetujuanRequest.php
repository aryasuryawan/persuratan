<?php

namespace App\Http\Requests;

class KomitePersetujuanRequest extends ArsipFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge([
            'judul' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:pembelian,prinsip_proyek'],
            'tanggal_rapat' => ['required', 'date'],
            'daftar_peserta' => ['nullable', 'string'],
            'nilai_pembelian' => ['nullable', 'numeric', 'min:0'],
            'hasil' => ['required', 'in:disetujui,ditolak,revisi,ditunda'],
            'catatan_keputusan' => ['nullable', 'string'],
        ], $this->rulesBerkas());
    }
}
