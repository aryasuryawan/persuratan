<?php

namespace Database\Seeders;

use App\Models\Klasifikasi;
use Illuminate\Database\Seeder;

class KlasifikasiSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'UMUM', 'nama' => 'Umum', 'color' => 'secondary', 'is_rahasia' => false],
            ['kode' => 'KEUANGAN', 'nama' => 'Keuangan', 'color' => 'success', 'is_rahasia' => true],
            ['kode' => 'SDM', 'nama' => 'SDM', 'color' => 'info', 'is_rahasia' => true],
            ['kode' => 'PROGRAM', 'nama' => 'Program', 'color' => 'primary', 'is_rahasia' => false],
            ['kode' => 'RAHASIA', 'nama' => 'Rahasia', 'color' => 'danger', 'is_rahasia' => true],
            ['kode' => 'restricted', 'nama' => 'Restricted', 'color' => 'dark', 'is_rahasia' => true],
        ];

        foreach ($data as $d) {
            Klasifikasi::create($d);
        }
    }
};
