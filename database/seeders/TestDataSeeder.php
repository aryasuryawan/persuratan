<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $sekretaris = User::where('email', 'sekretaris@dd.or.id')->first();
        $pimpinan = User::where('email', 'pimpinan@dd.or.id')->first();
        $dewi = User::where('email', 'dewi@dd.or.id')->first();

        // Relasi sekretaris-pimpinan
        if ($sekretaris && $pimpinan) {
            \App\Models\SekretarisPimpinan::create([
                'sekretaris_id' => $sekretaris->id,
                'pimpinan_id' => $pimpinan->id,
                'is_active' => true,
            ]);
        }
        if ($dewi && $pimpinan) {
            \App\Models\SekretarisPimpinan::create([
                'sekretaris_id' => $dewi->id,
                'pimpinan_id' => $pimpinan->id,
                'is_active' => true,
            ]);
        }

        $this->command->info('Test data seeded.');
    }
}
