<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            [
                'code'          => 'P001',
                'name'          => 'Lenovo Ideapad Gaming 3',
                'description'   => 'Ini merupakan leptop',
                'stock'         => '10',
                'price'         => '15000000',
                'is_delete'     => '0',
                'category'      => 'leptop',
            ],
            [
                'code'          => 'P002',
                'name'          => 'Asus TUF',
                'description'   => 'Ini merupakan leptop',
                'stock'         => '9',
                'price'         => '15000000',
                'is_delete'     => '1',
                'category'      => 'leptop',
            ],
            [
                'code'          => 'P003',
                'name'          => 'Iphone 16 Pro Max',
                'description'   => 'Ini merupakan hp',
                'stock'         => '20',
                'price'         => '25000000',
                'is_delete'     => '0',
                'category'      => 'hp',
            ],
        ];

        foreach($user as $key => $val){
            Product::create($val);
        }
    }
}
