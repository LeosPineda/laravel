<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestAddonCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:addons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test addons for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test addons...');

        // Check existing products
        $products = DB::table('products')->where('id', 6)->get();
        if ($products->isEmpty()) {
            $this->error('Product ID 6 (Cheese) not found!');
            return 1;
        }

        $product = $products->first();
        $this->info("Found product: {$product->name}");

        // Check existing addons for this product
        $existingAddons = DB::table('addons')->where('product_id', 6)->get();
        $this->info("Existing addons for product 6: " . $existingAddons->count());

        if ($existingAddons->count() == 0) {
            // Create test addons
            DB::table('addons')->insert([
                [
                    'product_id' => 6,
                    'name' => 'Extra Cheese',
                    'price' => 10.00,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => 6,
                    'name' => 'Tomato Sauce',
                    'price' => 5.00,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $this->info('Created 2 test addons for product 6 (Cheese)');
        } else {
            $this->info('Addons already exist for this product');
        }

        // Verify the addons
        $addons = DB::table('addons')->where('product_id', 6)->get();
        $this->info("Total addons for product 6: " . $addons->count());

        foreach ($addons as $addon) {
            $this->info("- {$addon->name} (₱{$addon->price}) - Active: " . ($addon->is_active ? 'YES' : 'NO'));
        }

        return 0;
    }
}
