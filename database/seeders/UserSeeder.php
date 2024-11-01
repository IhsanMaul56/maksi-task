<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = [
            [
                'name'      => 'ihsan',
                'email'     => 'ihsan@gmail.com',
                'password'  => 'admin',
                'role'      => 'admin'
            ],
            [
                'name'      => 'lukman',
                'email'     => 'lukman@gmail.com',
                'password'  => 'user',
                'role'      => 'user'
            ]
        ];

        foreach($user as $key => $val){
            User::create($val);
        }
    }
}
