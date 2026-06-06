<?php
// database/seeders/CategorySeeder.php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Bridal Lahnga',  'code' => 'BL'],
            ['name' => 'Sherwani',       'code' => 'SW'],
            ['name' => 'Qulla',          'code' => 'QL'],
            ['name' => 'Khussa',         'code' => 'KH'],
            ['name' => 'Dupatta',        'code' => 'DP'],
            ['name' => 'Jewellery',      'code' => 'JW'],
            ['name' => 'Accessories',    'code' => 'AC'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }
    }
}