<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('kode', 'admin')->first();

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@dd.or.id',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $sekretarisRole = Role::where('kode', 'sekretaris')->first();
        User::create([
            'name' => 'Sekretaris',
            'email' => 'sekretaris@dd.or.id',
            'password' => bcrypt('password'),
            'role_id' => $sekretarisRole->id,
            'is_active' => true,
        ]);

        $pimpinanRole = Role::where('kode', 'pimpinan')->first();
        User::create([
            'name' => 'Pimpinan',
            'email' => 'pimpinan@dd.or.id',
            'password' => bcrypt('password'),
            'role_id' => $pimpinanRole->id,
            'is_active' => true,
        ]);
    }
};
