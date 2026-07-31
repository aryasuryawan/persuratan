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
            UserSeeder::class,
            SeedMasterDataSeeder::class,
            SampleUsersSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}
