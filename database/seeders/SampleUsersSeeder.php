<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $samples = [
            ['name' => 'Thatcher Keel', 'email' => 'tkeel@blogger.com', 'title' => 'VP Sales', 'dir_nama' => 'Sales', 'div_nama' => 'Business Development', 'role_id' => 5, 'password' => 'password'],
            ['name' => 'Dyann Escala', 'email' => 'descala@usatoday.com', 'title' => 'Mechanical Systems Engineer', 'dir_nama' => 'Sales', 'div_nama' => 'Engineering', 'role_id' => 4, 'password' => 'password'],
            ['name' => 'Avivah Mugleston', 'email' => 'amugleston@intel.com', 'title' => 'Actuary', 'dir_nama' => 'Sales', 'div_nama' => 'Finance', 'role_id' => 4, 'password' => 'password'],
            ['name' => 'Arlie Armstead', 'email' => 'aarmsteadi@yellowpages.com', 'title' => 'VP Quality Control', 'dir_nama' => 'Accounting', 'div_nama' => 'Quality Control', 'role_id' => 3, 'password' => 'password'],
            ['name' => 'Tessie Curzon', 'email' => 'tcurzonj@hp.com', 'title' => 'Research Nurse', 'dir_nama' => 'Product Management', 'div_nama' => 'Research', 'role_id' => 4, 'password' => 'password'],
        ];

        foreach ($samples as $s) {
            $dir = \App\Models\Direktorat::where('nama', $s['dir_nama'])->first();
            $div = \App\Models\Divisi::where('nama', $s['div_nama'])->first();
            User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'title' => $s['title'],
                'role_id' => $s['role_id'],
                'password' => Hash::make($s['password']),
                'direktorat_id' => $dir?->id,
                'divisi_id' => $div?->id,
            ]);
        }
    }
}
