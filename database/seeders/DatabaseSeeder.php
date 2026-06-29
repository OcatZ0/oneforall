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
            'password' => bcrypt('admin123'),
            'role' =>'admin',
        ]);

        // Create customer users
        $customer1 = User::create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'password' => bcrypt('johndoe123'),
            'role' =>'customer',
        ]);

        // Add more customers for pagination testing
        User::create([
            'username' => 'michael',
            'email' => 'michael@example.com',
            'password' => bcrypt('michael123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'sarah',
            'email' => 'sarah@example.com',
            'password' => bcrypt('sarah123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'robert',
            'email' => 'robert@example.com',
            'password' => bcrypt('robert123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'emma',
            'email' => 'emma@example.com',
            'password' => bcrypt('emma123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'anisabet',
            'email' => 'anisabet@example.com',
            'password' => bcrypt('anisabet123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'fauzii',
            'email' => 'fauzii@example.com',
            'password' => bcrypt('fauzii123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'wahyu',
            'email' => 'wahyu@example.com',
            'password' => bcrypt('wahyu123'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'astreed',
            'email' => 'astreed@example.com',
            'password' => bcrypt('astreed123'),
            'role' =>'admin',
        ]);

        User::create([
            'username' => 'ariffin',
            'email' => 'ariffin@example.com',
            'password' => bcrypt('ariffin123'),
            'role' =>'customer',
        ]);
    }
}
