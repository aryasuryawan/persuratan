<?php

namespace App\Concerns;

use App\Models\RiwayatArsip;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait PunyaRiwayatArsip
{
    public function riwayatArsip(): MorphMany
    {
        return $this->morphMany(RiwayatArsip::class, 'arsipable');
    }

    public function catatPerubahanData(array $dataBaru, ?array $dataLama = null): void
    {
        $labels = $this->labelsRiwayat();
        $lama = $dataLama ?? $this->getOriginal();

        Log::debug('CATAT PERUBAHAN', ['model' => static::class, 'labels' => array_keys($labels), 'dataKeys' => array_keys($dataBaru), 'lamaKeys' => array_keys($lama)]);

        $perubahan = [];

        foreach ($labels as $field => $label) {
            if (! array_key_exists($field, $dataBaru)) {
                continue;
            }

            $old = $lama[$field] ?? null;
            $new = $dataBaru[$field] ?? null;

            if ((string) $old === (string) $new) {
                continue;
            }

            $perubahan[] = [
                'arsipable_type' => $this->getMorphClass(),
                'arsipable_id' => $this->getKey(),
                'field' => $field,
                'label' => $label,
                'nilai_lama' => $this->formatRiwayatNilai($field, $old),
                'nilai_baru' => $this->formatRiwayatNilai($field, $new),
                'dilakukan_oleh_id' => Auth::id(),
                'catatan' => 'Data arsip diperbarui.',
            ];
        }

        if ($perubahan) {
            RiwayatArsip::insert($perubahan);
        }
    }

    protected function formatRiwayatNilai(string $field, $nilai)
    {
        if (in_array($field, ['tanggal_sk', 'tanggal_berlaku'])) {
            return is_string($nilai) && $nilai ? \Carbon\Carbon::parse($nilai)->format('d/m/Y') : null;
        }

        if (in_array($field, ['tanggal_rapat', 'tanggal'])) {
            return is_string($nilai) && $nilai ? \Carbon\Carbon::parse($nilai)->format('d/m/Y') : null;
        }

        if ($field === 'is_rahasia') {
            return $nilai ? 'Rahasia' : 'Biasa';
        }

        return $nilai === null ? null : (string) $nilai;
    }

    protected function labelsRiwayat(): array
    {
        return [];
    }
}
