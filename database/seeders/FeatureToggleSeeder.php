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
            'employees', 'attendance', 'salary', 'advances',
            'products', 'categories', 'vendors',
            'rentals', 'sales', 'customers',
            'notifications', 'reports',
            'purchase_orders', 'accounts', 'expenses',

            // Stat cards — all on by default
            'stat_total_customers', 'stat_total_products',
            'stat_active_rentals',  'stat_monthly_revenue',
            'stat_pickup_today',    'stat_pickup_tomorrow',
            'stat_overdue',         'stat_pending_balance',
            'stat_total_cash',      'stat_expenses',
            'stat_total_sales',     'stat_pending_po',

            // Bottom dashboard cards
            'dash_overdue_card', 'dash_pickup_card', 'dash_return_card',
        ];

        foreach ($features as $feature) {
            FeatureToggle::updateOrCreate(
                ['user_id' => null, 'feature' => $feature],
                ['is_enabled' => true]
            );
        }
    }
}
