<?php
// database/seeders/AccountSeeder.php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Default accounts
        $accounts = [
            ['name' => 'Cash in Hand',    'type' => 'cash',          'is_default' => true,  'opening_balance' => 0],
            ['name' => 'HBL Bank',        'type' => 'bank',          'is_default' => false, 'opening_balance' => 0],
            ['name' => 'Meezan Bank',     'type' => 'bank',          'is_default' => false, 'opening_balance' => 0],
            ['name' => 'JazzCash',        'type' => 'mobile_wallet', 'is_default' => false, 'opening_balance' => 0],
            ['name' => 'Easypaisa',       'type' => 'mobile_wallet', 'is_default' => false, 'opening_balance' => 0],
        ];

        foreach ($accounts as $acc) {
            Account::updateOrCreate(
                ['name' => $acc['name']],
                array_merge($acc, [
                    'current_balance' => $acc['opening_balance'],
                    'is_active'       => true,
                ])
            );
        }

        // Default expense categories
        $categories = [
            ['name' => 'Shop Rent',         'color' => '#e53e3e'],
            ['name' => 'Electricity',        'color' => '#d69e2e'],
            ['name' => 'Employee Salary',    'color' => '#3182ce'],
            ['name' => 'Employee Bonus',     'color' => '#319795'],
            ['name' => 'Repair & Maintenance','color' => '#805ad5'],
            ['name' => 'Dry Clean',          'color' => '#2c5282'],
            ['name' => 'Guest Food & Drinks','color' => '#c05621'],
            ['name' => 'Taxes & Government', 'color' => '#c53030'],
            ['name' => 'Miscellaneous',      'color' => '#718096'],
            ['name' => 'Owner Withdrawal',   'color' => '#1a2340'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::updateOrCreate(
                ['name' => $cat['name']],
                array_merge($cat, ['is_active' => true])
            );
        }
    }
}