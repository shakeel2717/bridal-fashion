<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name'    => 'Fatima Malik',
                'phone1'  => '0300-1112233',
                'phone2'  => '0321-1112233',
                'whatsapp'=> '0300-1112233',
                'cnic'    => '33100-1234567-8',
                'address' => 'Street 5, Gulberg, Faisalabad',
                'notes'   => 'Regular customer, prefers red color',
            ],
            [
                'name'    => 'Aisha Nawaz',
                'phone1'  => '0312-9988776',
                'phone2'  => null,
                'whatsapp'=> '0312-9988776',
                'cnic'    => '33102-7654321-0',
                'address' => 'Peoples Colony, Faisalabad',
                'notes'   => null,
            ],
            [
                'name'    => 'Sana Iqbal',
                'phone1'  => '0333-4455667',
                'phone2'  => '0300-4455667',
                'whatsapp'=> '0333-4455667',
                'cnic'    => '35202-1122334-5',
                'address' => 'Model Town, Lahore',
                'notes'   => 'Wedding in December',
            ],
            [
                'name'    => 'Nadia Hussain',
                'phone1'  => '0345-6677889',
                'phone2'  => null,
                'whatsapp'=> '0345-6677889',
                'cnic'    => '33100-9988776-1',
                'address' => 'Samanabad, Faisalabad',
                'notes'   => null,
            ],
            [
                'name'    => 'Zara Khan',
                'phone1'  => '0321-3344556',
                'phone2'  => '0311-3344556',
                'whatsapp'=> '0321-3344556',
                'cnic'    => '33105-5566778-2',
                'address' => 'Millat Road, Faisalabad',
                'notes'   => 'Prefers heavy embroidery work',
            ],
            [
                'name'    => 'Hina Awan',
                'phone1'  => '0300-7788990',
                'phone2'  => null,
                'whatsapp'=> '0300-7788990',
                'cnic'    => '35401-2233445-9',
                'address' => 'DHA Phase 5, Lahore',
                'notes'   => null,
            ],
            [
                'name'    => 'Sara Butt',
                'phone1'  => '0333-1199882',
                'phone2'  => '0321-1199882',
                'whatsapp'=> '0333-1199882',
                'cnic'    => '33100-6677889-4',
                'address' => 'Jinnah Colony, Faisalabad',
                'notes'   => 'Booked for January wedding',
            ],
            [
                'name'    => 'Amna Tariq',
                'phone1'  => '0311-5544332',
                'phone2'  => null,
                'whatsapp'=> null,
                'cnic'    => '33102-3344556-7',
                'address' => 'Madina Town, Faisalabad',
                'notes'   => null,
            ],
            [
                'name'    => 'Rabia Shahid',
                'phone1'  => '0345-8877665',
                'phone2'  => '0300-8877665',
                'whatsapp'=> '0345-8877665',
                'cnic'    => '33105-9900112-3',
                'address' => 'Susan Road, Faisalabad',
                'notes'   => 'Referred by Fatima Malik',
            ],
            [
                'name'    => 'Uzma Raza',
                'phone1'  => '0321-6655443',
                'phone2'  => null,
                'whatsapp'=> '0321-6655443',
                'cnic'    => '35202-7788990-6',
                'address' => 'Gulshan-e-Iqbal, Karachi',
                'notes'   => 'Visiting from Karachi',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::updateOrCreate(
                ['phone1' => $customer['phone1']],
                array_merge($customer, ['is_walkin' => false])
            );
        }
    }
}