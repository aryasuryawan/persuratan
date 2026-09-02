<?php

namespace Database\Seeders;

use App\Models\Topik;
use Illuminate\Database\Seeder;

class TopikSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'KEUANGAN', 'nama' => 'Keuangan', 'color' => 'success'],
            ['kode' => 'SDM', 'nama' => 'Sumber Daya Manusia', 'color' => 'info'],
            ['kode' => 'PROGRAM', 'nama' => 'Program', 'color' => 'primary'],
            ['kode' => 'PENGHIMPUNAN', 'nama' => 'Penghimpunan', 'color' => 'warning'],
        ];

        foreach ($data as $d) {
            Topik::firstOrCreate(['kode' => $d['kode']], $d);
        }
    }
}
