<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Run the production superadmin seeder.
     * Creates the final superadmin account for deployment.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Production Superadmin Seeding...');

        // Get credentials from environment variables
        $superadminEmail = env('SUPER_ADMIN_EMAIL');
        $superadminPassword = env('SUPER_ADMIN_PASSWORD');

        // Validate environment variables exist
        if (!$superadminEmail || !$superadminPassword) {
            $this->command->error('❌ SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD must be set in .env');
            return;
        }

        // Remove any existing superadmin accounts first
        User::where('role', 'superadmin')->delete();

        // Create superadmin user
        $superadmin = User::create([
            'name' => 'Production Superadmin',
            'email' => $superadminEmail,
            'email_verified_at' => now(),
            'password' => Hash::make($superadminPassword),
            'role' => 'superadmin',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        $this->command->info('✅ Production superadmin created successfully');
        $this->command->info('📧 Email: ' . $superadminEmail);
        $this->command->info('🔑 Role: superadmin');
        $this->command->info('✅ Status: Active');

        $this->command->info('🎉 Production Superadmin Seeding Completed!');
        $this->command->info('📋 Total Users: ' . User::count());
        $this->command->info('👤 Superadmins: ' . User::where('role', 'superadmin')->count());

        $this->command->info('');
        $this->command->info('🔒 SECURITY NOTE:');
        $this->command->info('- Only one superadmin account exists');
        $this->command->info('- Credentials are sourced from .env variables');
        $this->command->info('- Safe for production deployment');
        $this->command->info('');
        $this->command->info('🚀 Ready for production deployment!');
    }
}
