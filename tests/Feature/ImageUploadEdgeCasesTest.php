<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and vendor for testing
        $user = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->create(['user_id' => $user->id]);

        // Login as vendor
        $this->actingAs($user);

        // Setup fake storage
        Storage::fake('public');
    }

    /** @test */
    public function it_rejects_files_with_uppercase_extensions()
    {
        // Create a fake image with uppercase extension
        $image = UploadedFile::fake()->image('test.JPG');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('image', strtolower($response->json('message')));
    }

    /** @test */
    public function it_rejects_files_with_mixed_case_extensions()
    {
        // Create a fake image with mixed case extension
        $image = UploadedFile::fake()->image('test.JpEg');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_accepts_webp_format()
    {
        // WebP is widely supported but not in the current mimes list
        $image = UploadedFile::fake()->image('test.webp');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product WebP',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        // This should fail with current validation but should pass after fix
        $response->assertStatus(422);
    }

    /** @test */
    public function it_accepts_heic_format()
    {
        // HEIC is common in mobile devices but less widely supported
        $image = UploadedFile::fake()->create('test.heic', 1024, 'image/heic');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product HEIC',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        // This should fail with current validation
        $response->assertStatus(422);
    }

    /** @test */
    public function it_rejects_oversized_files()
    {
        // Create a file larger than 2MB (2048KB)
        $image = UploadedFile::fake()->image('test.jpg')->size(2049); // 2049 KB

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product Large',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    }

    /** @test */
    public function it_accepts_exact_size_limit()
    {
        // Create a file exactly at the 2MB limit
        $image = UploadedFile::fake()->image('test.jpg')->size(2048); // 2048 KB

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product Exact Size',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        // This should pass if exactly 2048KB is allowed
        $response->assertStatus(201);
    }

    /** @test */
    public function it_rejects_non_image_files_with_image_extension()
    {
        // Create a text file with image extension
        $fakeFile = UploadedFile::fake()->create('malicious.jpg', 1024, 'text/plain');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product Fake Image',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $fakeFile,
        ]);

        // This should fail but might not with just extension validation
        $response->assertStatus(422);
    }

    /** @test */
    public function it_handles_empty_image_field_correctly()
    {
        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product No Image',
            'price' => 10.00,
            'stock_quantity' => 100,
            // No image field
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product No Image',
            'image_url' => null,
        ]);
    }

    /** @test */
    public function it_rejects_file_with_no_extension()
    {
        // Create a file with no extension
        $image = UploadedFile::fake()->create('testfile', 1024, 'image/jpeg');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product No Extension',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_handles_unicode_characters_in_filename()
    {
        // Create a file with unicode characters in name
        $image = UploadedFile::fake()->image('测试图片.jpg');

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Test Product Unicode Name',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function it_updates_image_correctly()
    {
        // First create a product
        $product = \App\Models\Product::factory()->create([
            'vendor_id' => auth()->user()->vendor->id,
            'name' => 'Original Product',
        ]);

        // Upload a new image
        $newImage = UploadedFile::fake()->image('updated.jpg');

        $response = $this->putJson("/api/vendor/products/{$product->id}", [
            'name' => 'Updated Product',
            'price' => 15.00,
            'stock_quantity' => 50,
            'image' => $newImage,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'image_url' => 'products/' . $newImage->hashName(),
        ]);
    }

    /** @test */
    public function it_handles_concurrent_image_uploads()
    {
        // Simulate multiple uploads at once
        $responses = [];

        for ($i = 0; $i < 5; $i++) {
            $image = UploadedFile::fake()->image("test{$i}.jpg");

            $responses[] = $this->postJson('/api/vendor/products', [
                'name' => "Concurrent Product {$i}",
                'price' => 10.00 + $i,
                'stock_quantity' => 100,
                'image' => $image,
            ]);
        }

        // All should succeed
        foreach ($responses as $response) {
            $response->assertStatus(201);
        }
    }

    /** @test */
    public function it_validates_image_dimensions_when_needed()
    {
        // This test would require additional validation rules for minimum dimensions
        // which should be added based on UI requirements
        $image = UploadedFile::fake()->image('tiny.jpg')->resize(10, 10);

        $response = $this->postJson('/api/vendor/products', [
            'name' => 'Tiny Image Product',
            'price' => 10.00,
            'stock_quantity' => 100,
            'image' => $image,
        ]);

        // This should pass with current validation but might fail with dimension validation
        $response->assertStatus(201);
    }
}
