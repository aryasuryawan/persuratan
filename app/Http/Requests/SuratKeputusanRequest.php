<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SuratKeputusanRequest extends ArsipFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $unique = Rule::unique('surat_keputusan', 'nomor_sk');
        if ($this->route('surat_keputusan')) {
            $unique = $unique->ignore($this->route('surat_keputusan'), 'uuid');
        }

        return array_merge([
            'nomor_sk' => ['required', 'string', 'max:255', $unique],
            'perihal' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'klasifikasi_id' => ['nullable', 'exists:klasifikasi,id'],
            'tanggal_sk' => ['required', 'date'],
            'tanggal_berlaku' => ['nullable', 'date'],
            'ditetapkan_oleh' => ['nullable', 'string', 'max:255'],
            'ringkasan' => ['nullable', 'string'],
            'status' => ['required', 'in:aktif,tidak_berlaku'],
        ], $this->rulesBerkas());
    }
}
