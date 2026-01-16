<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $existingAdmin = Admin::where('email', 'admin@gmail.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('Admin user already exists. Updating password...');
            $existingAdmin->update([
                'username' => 'admin@gmail.com',
                'password' => Hash::make('11221122'),
                'name' => 'Admin User',
            ]);
            $this->command->info('Admin user updated successfully!');
        } else {
            // Create new admin user
            Admin::create([
                'username' => 'admin@gmail.com',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('11221122'),
                'name' => 'Admin User',
            ]);
            $this->command->info('Admin user created successfully!');
        }
        
        $this->command->info('Admin credentials:');
        $this->command->info('Email/Username: admin@gmail.com');
        $this->command->info('Password: 11221122');
    }
}
