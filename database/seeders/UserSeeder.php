<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin Nike',
                'email'    => 'superadmin@nike.test',
                'password' => Hash::make('password'),
                'role'     => 'super_admin',
            ],
            [
                'name'     => 'Data Analyst Nike',
                'email'    => 'analyst@nike.test',
                'password' => Hash::make('password'),
                'role'     => 'analyst',
            ],
            [
                'name'     => 'Manager Nike',
                'email'    => 'manager@nike.test',
                'password' => Hash::make('password'),
                'role'     => 'manager',
            ],
            [
                'name'     => 'Staff Input Nike',
                'email'    => 'staff@nike.test',
                'password' => Hash::make('password'),
                'role'     => 'staff',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }

        $this->command->info('✅ 4 user seeded (password: "password" untuk semua)');
    }
}