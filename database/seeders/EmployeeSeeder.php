<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'name'          => 'Muhammad Rashid',
                'email'         => 'rashid@wedding.com',
                'password'      => Hash::make('employee123'),
                'role'          => 'employee',
                'phone'         => '0300-1111222',
                'cnic'          => '33100-1234561-1',
                'address'       => 'Street 3, Gulberg, Faisalabad',
                'designation'   => 'Sales Staff',
                'joining_date'  => '2024-01-15',
                'salary_type'   => 'monthly',
                'salary_amount' => 25000,
                'is_active'     => true,
            ],
            [
                'name'          => 'Bilal Ahmed',
                'email'         => 'bilal@wedding.com',
                'password'      => Hash::make('employee123'),
                'role'          => 'employee',
                'phone'         => '0321-2223334',
                'cnic'          => '33102-2345672-2',
                'address'       => 'Peoples Colony, Faisalabad',
                'designation'   => 'Senior Sales Staff',
                'joining_date'  => '2023-06-01',
                'salary_type'   => 'monthly',
                'salary_amount' => 32000,
                'is_active'     => true,
            ],
            [
                'name'          => 'Usman Ali',
                'email'         => 'usman@wedding.com',
                'password'      => Hash::make('employee123'),
                'role'          => 'employee',
                'phone'         => '0333-3334445',
                'cnic'          => '33105-3456783-3',
                'address'       => 'Madina Town, Faisalabad',
                'designation'   => 'Delivery Staff',
                'joining_date'  => '2024-03-10',
                'salary_type'   => 'daily',
                'salary_amount' => 1200,
                'is_active'     => true,
            ],
            [
                'name'          => 'Kamran Iqbal',
                'email'         => 'kamran@wedding.com',
                'password'      => Hash::make('employee123'),
                'role'          => 'employee',
                'phone'         => '0345-4445556',
                'cnic'          => '33100-4567894-4',
                'address'       => 'Jinnah Colony, Faisalabad',
                'designation'   => 'Tailor',
                'joining_date'  => '2023-01-01',
                'salary_type'   => 'monthly',
                'salary_amount' => 28000,
                'is_active'     => true,
            ],
            [
                'name'          => 'Adnan Farooq',
                'email'         => 'adnan@wedding.com',
                'password'      => Hash::make('employee123'),
                'role'          => 'employee',
                'phone'         => '0311-5556667',
                'cnic'          => '33102-5678905-5',
                'address'       => 'Susan Road, Faisalabad',
                'designation'   => 'Cashier',
                'joining_date'  => '2024-06-01',
                'salary_type'   => 'monthly',
                'salary_amount' => 22000,
                'is_active'     => true,
            ],
        ];

        foreach ($employees as $employee) {
            User::updateOrCreate(
                ['email' => $employee['email']],
                $employee
            );
        }
    }
}