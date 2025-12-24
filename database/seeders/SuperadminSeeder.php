<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        // Get superadmin credentials from .env file
        $email = env('SUPERADMIN_EMAIL', 'superadmin@foodcourt.com');
        $password = env('SUPERADMIN_PASSWORD', 'SuperAdmin@123');
        $name = env('SUPERADMIN_NAME', 'Super Admin');

        // Check if superadmin already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Update existing user to be superadmin
            $existingUser->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'is_active' => true,
            ]);
            echo "Superadmin account updated successfully!\n";
        } else {
            // Create new superadmin
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'is_active' => true,
            ]);
            echo "Superadmin account created successfully!\n";
        }

        echo "Email: {$email}\n";
        echo "Password: {$password}\n";
    }
}
