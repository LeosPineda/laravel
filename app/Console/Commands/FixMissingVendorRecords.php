<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMissingVendorRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vendor:fix-missing-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing Vendor records for users with role "vendor" but no Vendor profile';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Finding vendor users without Vendor records...');

        // Find users with role 'vendor' but no Vendor record
        $vendorUsers = User::where('role', 'vendor')
            ->whereDoesntHave('vendor')
            ->get();

        if ($vendorUsers->isEmpty()) {
            $this->info('✅ All vendor users have Vendor records. Nothing to fix.');
            return Command::SUCCESS;
        }

        $this->warn("Found {$vendorUsers->count()} vendor users without Vendor records:");

        $createdCount = 0;

        foreach ($vendorUsers as $user) {
            try {
                DB::beginTransaction();

                // Create Vendor record
                $vendor = Vendor::create([
                    'user_id' => $user->id,
                    'brand_name' => $user->name . "'s Store",
                    'is_active' => $user->is_active,
                ]);

                $createdCount++;
                $this->line("  ✅ Created Vendor record for: {$user->name} ({$user->email})");

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("  ❌ Failed to create Vendor record for: {$user->name} ({$user->email})");
                $this->error("     Error: " . $e->getMessage());
            }
        }

        $this->info("\n🎉 Successfully created {$createdCount} missing Vendor records!");
        $this->info('Vendor functionality should now work properly.');

        return Command::SUCCESS;
    }
}
