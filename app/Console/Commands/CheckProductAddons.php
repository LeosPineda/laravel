<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckProductAddons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:addons {product_id? : Product ID to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check addons for a specific product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $productId = $this->argument('product_id');

        if ($productId) {
            // Check specific product
            $product = DB::table('products')->where('id', $productId)->first();
            if (!$product) {
                $this->error("Product ID {$productId} not found!");
                return 1;
            }

            $this->info("Product: {$product->name} (ID: {$productId})");
            
            $addons = DB::table('addons')->where('product_id', $productId)->get();
            $this->info("Addons found: " . $addons->count());

            if ($addons->count() > 0) {
                foreach ($addons as $addon) {
                    $this->info("- {$addon->name} (₱{$addon->price}) - Active: " . ($addon->is_active ? 'YES' : 'NO'));
                }
            } else {
                $this->warn("No addons found for this product!");
            }
        } else {
            // Check all products and their addon counts
            $this->info("All products and their addon counts:");
            
            $products = DB::table('products')->get();
            foreach ($products as $product) {
                $addonCount = DB::table('addons')->where('product_id', $product->id)->count();
                $this->info("Product {$product->id}: {$product->name} - {$addonCount} addons");
            }
        }

        return 0;
    }
}
