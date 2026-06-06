<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name'    => 'Ali Fabrics Faisalabad',
                'phone'   => '0300-1234567',
                'address' => 'Katchery Bazar, Faisalabad',
                'notes'   => 'Main supplier for bridal lahnga fabric',
            ],
            [
                'name'    => 'Hassan Sherwani House',
                'phone'   => '0321-9876543',
                'address' => 'Anarkali Bazar, Lahore',
                'notes'   => 'Sherwani and groom wear specialist',
            ],
            [
                'name'    => 'Zeeshan Jewellers',
                'phone'   => '0333-5551234',
                'address' => 'Liberty Market, Lahore',
                'notes'   => 'Bridal jewellery sets and accessories',
            ],
            [
                'name'    => 'Nadeem Khussa Centre',
                'phone'   => '0345-7778899',
                'address' => 'Ichhra Bazar, Lahore',
                'notes'   => 'Traditional khussa and footwear',
            ],
            [
                'name'    => 'Rehman Cloth House',
                'phone'   => '0311-4443322',
                'address' => 'Ghanta Ghar, Faisalabad',
                'notes'   => 'Dupatta and accessories fabric',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(
                ['name' => $vendor['name']],
                array_merge($vendor, ['is_active' => true])
            );
        }
    }
}