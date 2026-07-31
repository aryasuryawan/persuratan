<?php

namespace Database\Seeders;

use App\Models\StatusSurat;
use Illuminate\Database\Seeder;

class StatusSuratSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['kode' => 'received', 'label' => 'Diterima', 'urutan' => 1],
            ['kode' => 'verified', 'label' => 'Diverifikasi', 'urutan' => 2],
            ['kode' => 'forwarded_to_leadership', 'label' => 'Ke Pimpinan', 'urutan' => 3],
            ['kode' => 'assigned', 'label' => 'Didisposisikan', 'urutan' => 4],
            ['kode' => 'handled_by_leadership', 'label' => 'Pimpinan TL', 'urutan' => 5],
            ['kode' => 'in_progress', 'label' => 'Diproses', 'urutan' => 6],
            ['kode' => 'completed', 'label' => 'Selesai', 'urutan' => 7],
            ['kode' => 'archived', 'label' => 'Diarsipkan', 'urutan' => 8],
        ];

        foreach ($statuses as $s) {
            StatusSurat::create($s);
        }
    }
};
