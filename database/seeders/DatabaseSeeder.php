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
            'kata_sandi' => bcrypt('admin123'),
            'peran' => 'admin',
        ]);

        // Create customer users
        $customer1 = User::create([
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'kata_sandi' => bcrypt('password123'),
            'peran' => 'customer',
        ]);

        $customer2 = User::create([
            'username' => 'janedoe',
            'email' => 'jane@example.com',
            'kata_sandi' => bcrypt('password456'),
            'peran' => 'customer',
        ]);

        // Add more customers for pagination testing
        User::create([
            'username' => 'michael',
            'email' => 'michael@example.com',
            'kata_sandi' => bcrypt('password789'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'sarah',
            'email' => 'sarah@example.com',
            'kata_sandi' => bcrypt('password000'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'robert',
            'email' => 'robert@example.com',
            'kata_sandi' => bcrypt('password111'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'emma',
            'email' => 'emma@example.com',
            'kata_sandi' => bcrypt('password222'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'david',
            'email' => 'david@example.com',
            'kata_sandi' => bcrypt('password333'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'lisa',
            'email' => 'lisa@example.com',
            'kata_sandi' => bcrypt('password444'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'james',
            'email' => 'james@example.com',
            'kata_sandi' => bcrypt('password555'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'olivia',
            'email' => 'olivia@example.com',
            'kata_sandi' => bcrypt('password666'),
            'peran' => 'customer',
        ]);

        User::create([
            'username' => 'william',
            'email' => 'william@example.com',
            'kata_sandi' => bcrypt('password777'),
            'peran' => 'customer',
        ]);

        // Create agents for customers (001-010)
        $agentData = [
            ['id' => '001', 'name' => 'Web Server Production', 'ip' => '192.168.1.10'],
            ['id' => '002', 'name' => 'Database Server', 'ip' => '192.168.1.25'],
            ['id' => '003', 'name' => 'API Server', 'ip' => '192.168.1.15'],
            ['id' => '004', 'name' => 'Cache Server', 'ip' => '192.168.1.30'],
            ['id' => '005', 'name' => 'File Server', 'ip' => '192.168.1.40'],
            ['id' => '006', 'name' => 'Mail Server', 'ip' => '192.168.1.50'],
            ['id' => '007', 'name' => 'DNS Server', 'ip' => '192.168.1.60'],
            ['id' => '008', 'name' => 'Load Balancer', 'ip' => '192.168.1.70'],
            ['id' => '009', 'name' => 'Monitoring Server', 'ip' => '192.168.1.80'],
            ['id' => '010', 'name' => 'Backup Server', 'ip' => '192.168.1.90'],
        ];

        // Get customers
        $customers = User::where('peran', 'customer')->get();

        // Assign agents to customers (round-robin distribution)
        foreach ($agentData as $index => $agent) {
            $customerIndex = $index % count($customers);
            $assignedCustomer = $customers[$customerIndex];

            Agent::create([
                'id_agent' => $agent['id'],
                'nama' => $agent['name'],
                'deskripsi' => 'Wazuh monitored agent',
                'id_pengguna' => $assignedCustomer->id_pengguna,
            ]);
        }

        // Create activity logs
        LogActivity::create([
            'id_pengguna' => $admin->id_pengguna,
            'aktivitas' => 'User login',
        ]);

        LogActivity::create([
            'id_pengguna' => $customer1->id_pengguna,
            'aktivitas' => 'User registered',
        ]);

        LogActivity::create([
            'id_pengguna' => $customer2->id_pengguna,
            'aktivitas' => 'User registered',
        ]);
    }
}
