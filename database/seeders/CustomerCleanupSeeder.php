<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerCleanupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting customer cleanup...');

        // Delete ALL customers
        $this->deleteAllCustomers();

        // Show updated customer statistics
        $this->showCustomerStats();

        $this->command->info('Customer cleanup completed!');
    }

    /**
     * Delete all customers (use with caution!)
     */
    private function deleteAllCustomers(): void
    {
        $customerCount = User::where('role', 'customer')->count();

        if ($customerCount === 0) {
            $this->command->info('No customers found to delete.');

            return;
        }

        $this->command->info("Found {$customerCount} customer accounts to delete...");

        // Step 1: Delete related data first
        // Delete carts (uses user_id)
        $cartsDeleted = DB::table('carts')->whereIn('user_id', function ($query) {
            $query->select('id')->from('users')->where('role', 'customer');
        })->delete();

        // Delete orders (uses customer_id)
        $ordersDeleted = DB::table('orders')->whereIn('customer_id', function ($query) {
            $query->select('id')->from('users')->where('role', 'customer');
        })->delete();

        // Order items will be deleted automatically due to foreign key cascade

        // Step 2: Delete customers
        $deleted = User::where('role', 'customer')->delete();

        $this->command->info('🗑️  Cleanup Summary:');
        $this->command->info("   - Deleted {$cartsDeleted} cart records");
        $this->command->info("   - Deleted {$ordersDeleted} order records");
        $this->command->info('   - Order items deleted automatically (cascade)');
        $this->command->info("   - Deleted {$deleted} customer accounts");
        $this->command->info('✅ All customer accounts and related data deleted successfully!');
    }

    /**
     * Show customer statistics
     */
    private function showCustomerStats(): void
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $totalVendors = User::where('role', 'vendor')->count();
        $totalSuperadmins = User::where('role', 'superadmin')->count();

        $this->command->info('📊 Updated User Statistics:');
        $this->command->info("   Customers: {$totalCustomers}");
        $this->command->info("   Vendors: {$totalVendors}");
        $this->command->info("   Superadmins: {$totalSuperadmins}");
        $this->command->info('   Total Users: '.($totalCustomers + $totalVendors + $totalSuperadmins));

        // Show remaining customer details if any
        $recentCustomers = User::where('role', 'customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'created_at']);

        if ($recentCustomers->isNotEmpty()) {
            $this->command->info('📋 Remaining Customers:');
            foreach ($recentCustomers as $customer) {
                $this->command->info("   - {$customer->name} ({$customer->email})");
            }
        } else {
            $this->command->info('🎉 No customers remaining in database!');
        }
    }
}
