<?php
// database/seeders/FeatureToggleSeeder.php

namespace Database\Seeders;

use App\Models\FeatureToggle;
use Illuminate\Database\Seeder;

class FeatureToggleSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'employees',
            'attendance',
            'salary',
            'advances',
            'products',
            'categories',
            'vendors',
            'rentals',
            'sales',
            'customers',
            'notifications',
            'reports',
        ];

        foreach ($features as $feature) {
            FeatureToggle::updateOrCreate(
                ['user_id' => null, 'feature' => $feature],
                ['is_enabled' => true]
            );
        }
    }
}