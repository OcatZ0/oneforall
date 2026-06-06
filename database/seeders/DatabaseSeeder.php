<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WazuhAgent;
use App\Models\LogActivity;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin'),
            'role' =>'admin',
        ]);

        // Create customer users
        $customer1 = User::create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => bcrypt('johndoe'),
            'role' =>'customer',
        ]);

        $customer2 = User::create([
            'username' => 'janedoe',
            'email' => 'jane@example.com',
            'password' => bcrypt('janedoe'),
            'role' =>'customer',
        ]);

        // Add more customers for pagination testing
        User::create([
            'username' => 'michael',
            'email' => 'michael@example.com',
            'password' => bcrypt('michael'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'sarah',
            'email' => 'sarah@example.com',
            'password' => bcrypt('sarah'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'robert',
            'email' => 'robert@example.com',
            'password' => bcrypt('robert'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'emma',
            'email' => 'emma@example.com',
            'password' => bcrypt('emma'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'david',
            'email' => 'david@example.com',
            'password' => bcrypt('david'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'lisa',
            'email' => 'lisa@example.com',
            'password' => bcrypt('lisa'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'james',
            'email' => 'james@example.com',
            'password' => bcrypt('james'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'olivia',
            'email' => 'olivia@example.com',
            'password' => bcrypt('olivia'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'william',
            'email' => 'william@example.com',
            'password' => bcrypt('william'),
            'role' =>'customer',
        ]);
    }
}
