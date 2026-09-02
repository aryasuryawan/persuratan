<?php

namespace Database\Seeders;

use App\Models\Klasifikasi;
use Illuminate\Database\Seeder;

class KlasifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'UMUM', 'nama' => 'Public', 'color' => 'success', 'is_rahasia' => false],
            ['kode' => 'TERBATAS', 'nama' => 'Restricted', 'color' => 'warning', 'is_rahasia' => true],
            ['kode' => 'RAHASIA', 'nama' => 'Confidential', 'color' => 'danger', 'is_rahasia' => true],
        ];

        foreach ($data as $d) {
            Klasifikasi::create($d);
        }
    }
};
