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
            'username' => 'anisabet',
            'email' => 'anisabet@example.com',
            'password' => bcrypt('anisabet'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'fauzii',
            'email' => 'fauzii@example.com',
            'password' => bcrypt('fauzii'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'wahyu',
            'email' => 'wahyu@example.com',
            'password' => bcrypt('wahyu'),
            'role' =>'customer',
        ]);

        User::create([
            'username' => 'astreed',
            'email' => 'astreed@example.com',
            'password' => bcrypt('astreed'),
            'role' =>'admin',
        ]);

        User::create([
            'username' => 'ariffin',
            'email' => 'ariffin@example.com',
            'password' => bcrypt('ariffin'),
            'role' =>'customer',
        ]);
    }
}
