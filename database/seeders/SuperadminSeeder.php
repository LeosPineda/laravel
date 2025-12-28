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
        $email = env('SUPER_ADMIN_EMAIL');
        $password = env('SUPER_ADMIN_PASSWORD');

        // If env variables aren't loaded, try to read .env file directly
        if (!$email || !$password) {
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContents = file_get_contents($envPath);
                if (preg_match('/SUPER_ADMIN_EMAIL=(.+)/', $envContents, $emailMatches)) {
                    $email = trim($emailMatches[1]);
                }
                if (preg_match('/SUPER_ADMIN_PASSWORD=(.+)/', $envContents, $passwordMatches)) {
                    $password = trim($passwordMatches[1]);
                }
            }
        }

        if (!$email || !$password) {
            echo "❌ Error: SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD must be set in .env file\n";
            echo "📋 Expected format:\n";
            echo "SUPER_ADMIN_EMAIL=your-email@example.com\n";
            echo "SUPER_ADMIN_PASSWORD=your-password\n";
            return;
        }

        $name = 'Super Admin';

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
            echo "✅ Superadmin account updated successfully!\n";
        } else {
            // Create new superadmin
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'superadmin',
                'is_active' => true,
            ]);
            echo "✅ Superadmin account created successfully!\n";
        }

        echo "📧 Email: {$email}\n";
        echo "👤 Role: superadmin\n";
        echo "💡 Superadmin account is ready to use!\n";
    }
}
