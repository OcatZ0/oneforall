<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Agent;
use App\Models\LogActivity;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'kata_sandi' => bcrypt('admin'),
            'peran' => 'admin',
        ]);

        // Create customer users
        $customer1 = User::create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'kata_sandi' => bcrypt('johndoe'),
            'peran' => 'customer',
        ]);

        $customer2 = User::create([
            'username' => 'janedoe',
            'email' => 'jane@example.com',
            'kata_sandi' => bcrypt('janedoe'),
            'peran' => 'customer',
        ]);

        // Add more customers for pagination testing
        User::create([
            'username' => 'michael',
            'email' => 'michael@example.com',
            'kata_sandi' => bcrypt('michael'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'sarah',
            'email' => 'sarah@example.com',
            'kata_sandi' => bcrypt('sarah'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'robert',
            'email' => 'robert@example.com',
            'kata_sandi' => bcrypt('robert'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'emma',
            'email' => 'emma@example.com',
            'kata_sandi' => bcrypt('emma'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'david',
            'email' => 'david@example.com',
            'kata_sandi' => bcrypt('david'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'lisa',
            'email' => 'lisa@example.com',
            'kata_sandi' => bcrypt('lisa'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'james',
            'email' => 'james@example.com',
            'kata_sandi' => bcrypt('james'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'olivia',
            'email' => 'olivia@example.com',
            'kata_sandi' => bcrypt('olivia'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'william',
            'email' => 'william@example.com',
            'kata_sandi' => bcrypt('william'),
            'peran' => 'customer',
        ]);
    }
}
