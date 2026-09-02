<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            StatusSuratSeeder::class,
            KlasifikasiSeeder::class,
            TopikSeeder::class,
            SeedMasterDataSeeder::class,
            PreservedDataSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}
