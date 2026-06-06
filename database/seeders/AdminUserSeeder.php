<?php
// database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@wedding.com'],
            [
                'name'          => 'Admin',
                'email'         => 'admin@gmail.com',
                'password'      => Hash::make('asdfasdf'),
                'role'          => 'admin',
                'phone'         => '0300-0000000',
                'designation'   => 'Administrator',
                'joining_date'  => now()->toDateString(),
                'salary_type'   => 'monthly',
                'salary_amount' => 0,
                'is_active'     => true,
            ]
        );
    }
}