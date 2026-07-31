<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['kode' => 'admin', 'label' => 'Administrator'],
            ['kode' => 'sekretaris', 'label' => 'Sekretaris'],
            ['kode' => 'pimpinan', 'label' => 'Pimpinan'],
            ['kode' => 'unit_lain', 'label' => 'Unit Lain'],
            ['kode' => 'cc_security', 'label' => 'CC Security'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
};
