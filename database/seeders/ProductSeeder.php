<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('code');
        $vendors    = Vendor::all();

        $aliF    = $vendors->where('name', 'Ali Fabrics Faisalabad')->first();
        $hassan  = $vendors->where('name', 'Hassan Sherwani House')->first();
        $zeeshan = $vendors->where('name', 'Zeeshan Jewellers')->first();
        $nadeem  = $vendors->where('name', 'Nadeem Khussa Centre')->first();
        $rehman  = $vendors->where('name', 'Rehman Cloth House')->first();

        // ── RENTAL PRODUCTS (10) ──────────────────────────
        $rentalProducts = [
            [
                'code'           => 'BL-001',
                'name'           => 'Red Heavy Bridal Lahnga',
                'category_code'  => 'BL',
                'vendor'         => $aliF,
                'size'           => '36',
                'purchase_price' => 45000,
                'rental_price'   => 8000,
            ],
            [
                'code'           => 'BL-002',
                'name'           => 'Golden Bridal Lahnga',
                'category_code'  => 'BL',
                'vendor'         => $aliF,
                'size'           => '38',
                'purchase_price' => 55000,
                'rental_price'   => 10000,
            ],
            [
                'code'           => 'BL-003',
                'name'           => 'Maroon Embroidered Lahnga',
                'category_code'  => 'BL',
                'vendor'         => $aliF,
                'size'           => '34',
                'purchase_price' => 38000,
                'rental_price'   => 7000,
            ],
            [
                'code'           => 'SW-001',
                'name'           => 'Black Sherwani with Embroidery',
                'category_code'  => 'SW',
                'vendor'         => $hassan,
                'size'           => '42',
                'purchase_price' => 25000,
                'rental_price'   => 4500,
            ],
            [
                'code'           => 'SW-002',
                'name'           => 'Cream Sherwani Full Set',
                'category_code'  => 'SW',
                'vendor'         => $hassan,
                'size'           => '44',
                'purchase_price' => 30000,
                'rental_price'   => 5500,
            ],
            [
                'code'           => 'QL-001',
                'name'           => 'Golden Qulla with Kalghi',
                'category_code'  => 'QL',
                'vendor'         => $hassan,
                'size'           => 'Free',
                'purchase_price' => 3500,
                'rental_price'   => 800,
            ],
            [
                'code'           => 'DP-001',
                'name'           => 'Red Banarsi Dupatta',
                'category_code'  => 'DP',
                'vendor'         => $rehman,
                'size'           => 'Free',
                'purchase_price' => 4000,
                'rental_price'   => 1000,
            ],
            [
                'code'           => 'DP-002',
                'name'           => 'Gold Net Dupatta',
                'category_code'  => 'DP',
                'vendor'         => $rehman,
                'size'           => 'Free',
                'purchase_price' => 3500,
                'rental_price'   => 800,
            ],
            [
                'code'           => 'JW-001',
                'name'           => 'Bridal Jewellery Full Set',
                'category_code'  => 'JW',
                'vendor'         => $zeeshan,
                'size'           => 'Free',
                'purchase_price' => 15000,
                'rental_price'   => 3000,
            ],
            [
                'code'           => 'JW-002',
                'name'           => 'Gold Necklace & Earring Set',
                'category_code'  => 'JW',
                'vendor'         => $zeeshan,
                'size'           => 'Free',
                'purchase_price' => 12000,
                'rental_price'   => 2500,
            ],
        ];

        foreach ($rentalProducts as $data) {
            $category = $categories[$data['category_code']] ?? null;
            if (!$category) continue;

            Product::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name'           => $data['name'],
                    'category_id'    => $category->id,
                    'vendor_id'      => $data['vendor']?->id,
                    'size'           => $data['size'],
                    'type'           => 'rental',
                    'purchase_price' => $data['purchase_price'],
                    'rental_price'   => $data['rental_price'],
                    'sale_price'     => 0,
                    'stock_qty'      => 1,
                    'is_active'      => true,
                    'is_abandoned'   => false,
                ]
            );
        }

        // ── SALE PRODUCTS (5) ─────────────────────────────
        $saleProducts = [
            [
                'code'           => 'KH-001',
                'name'           => 'Groom Khussa Golden',
                'category_code'  => 'KH',
                'vendor'         => $nadeem,
                'size'           => '42',
                'purchase_price' => 2500,
                'sale_price'     => 4000,
                'stock_qty'      => 8,
            ],
            [
                'code'           => 'KH-002',
                'name'           => 'Bridal Khussa Red Embroidered',
                'category_code'  => 'KH',
                'vendor'         => $nadeem,
                'size'           => '37',
                'purchase_price' => 2000,
                'sale_price'     => 3500,
                'stock_qty'      => 5,
            ],
            [
                'code'           => 'AC-001',
                'name'           => 'Bridal Choora Set Red & White',
                'category_code'  => 'AC',
                'vendor'         => $zeeshan,
                'size'           => 'Free',
                'purchase_price' => 800,
                'sale_price'     => 1500,
                'stock_qty'      => 20,
            ],
            [
                'code'           => 'AC-002',
                'name'           => 'Mehndi Tray Decoration Set',
                'category_code'  => 'AC',
                'vendor'         => null,
                'size'           => 'Free',
                'purchase_price' => 1200,
                'sale_price'     => 2200,
                'stock_qty'      => 10,
            ],
            [
                'code'           => 'AC-003',
                'name'           => 'Sehra Flower Set for Groom',
                'category_code'  => 'AC',
                'vendor'         => null,
                'size'           => 'Free',
                'purchase_price' => 500,
                'sale_price'     => 1000,
                'stock_qty'      => 15,
            ],
        ];

        foreach ($saleProducts as $data) {
            $category = $categories[$data['category_code']] ?? null;
            if (!$category) continue;

            Product::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name'           => $data['name'],
                    'category_id'    => $category->id,
                    'vendor_id'      => $data['vendor']?->id,
                    'size'           => $data['size'],
                    'type'           => 'sale',
                    'purchase_price' => $data['purchase_price'],
                    'rental_price'   => 0,
                    'sale_price'     => $data['sale_price'],
                    'stock_qty'      => $data['stock_qty'],
                    'is_active'      => true,
                    'is_abandoned'   => false,
                ]
            );
        }
    }
}