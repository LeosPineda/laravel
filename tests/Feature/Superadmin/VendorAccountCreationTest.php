<?php

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

describe('Superadmin Vendor Account Creation', function () {
    beforeEach(function () {
        // Create superadmin user
        $this->superadmin = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Create a vendor user that will be assigned to a vendor
        $this->vendorUser = User::factory()->create([
            'email' => 'vendor@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'is_active' => true,
        ]);
    });

    it('allows superadmin to create vendor account with vendor user', function () {
        $vendorData = [
            'name' => 'Test Restaurant',
            'description' => 'A test restaurant for testing',
            'address' => '123 Test Street, Test City',
            'phone' => '+1234567890',
            'brand_name' => 'TestBrand',
        ];

        $response = $this->actingAs($this->superadmin, 'web')
            ->post('/superadmin/vendors', $vendorData);

        $response->assertRedirect('/superadmin/vendors');

        $this->assertDatabaseHas('vendors', [
            'name' => $vendorData['name'],
            'description' => $vendorData['description'],
            'address' => $vendorData['address'],
            'phone' => $vendorData['phone'],
            'brand_name' => $vendorData['brand_name'],
        ]);

        $vendor = Vendor::where('name', $vendorData['name'])->first();
        expect($vendor)->not->toBeNull();
        expect($vendor->user_id)->toBe($this->vendorUser->id);
    });

    it('validates required fields when creating vendor', function () {
        $invalidData = [
            'name' => '', // Empty name
            'description' => '',
            'address' => '',
            'phone' => '',
            'brand_name' => '',
        ];

        $response = $this->actingAs($this->superadmin, 'web')
            ->post('/superadmin/vendors', $invalidData);

        $response->assertSessionHasErrors(['name', 'description', 'address', 'phone', 'brand_name']);
    });

    it('requires authentication for vendor creation', function () {
        $vendorData = [
            'name' => 'Test Restaurant',
            'description' => 'A test restaurant',
            'address' => '123 Test Street',
            'phone' => '+1234567890',
            'brand_name' => 'TestBrand',
        ];

        $response = $this->post('/superadmin/vendors', $vendorData);

        $response->assertRedirect('/login');
    });

    it('prevents non-superadmin users from creating vendors', function () {
        $vendorData = [
            'name' => 'Test Restaurant',
            'description' => 'A test restaurant',
            'address' => '123 Test Street',
            'phone' => '+1234567890',
            'brand_name' => 'TestBrand',
        ];

        $response = $this->actingAs($this->vendorUser, 'web')
            ->post('/superadmin/vendors', $vendorData);

        $response->assertStatus(403); // Forbidden

        $this->assertDatabaseMissing('vendors', [
            'name' => $vendorData['name'],
        ]);
    });

    it('displays vendor creation form to superadmin', function () {
        $response = $this->actingAs($this->superadmin, 'web')
            ->get('/superadmin/vendors/create');

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.vendors.create');
        $response->assertSee('Create New Vendor');
    });

    it('lists all vendors for superadmin', function () {
        $vendor1 = Vendor::factory()->create([
            'user_id' => $this->vendorUser->id,
            'name' => 'Restaurant One',
            'brand_name' => 'BrandOne',
        ]);

        $vendor2 = Vendor::factory()->create([
            'user_id' => $this->vendorUser->id,
            'name' => 'Restaurant Two',
            'brand_name' => 'BrandTwo',
        ]);

        $response = $this->actingAs($this->superadmin, 'web')
            ->get('/superadmin/vendors');

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.vendors.index');
        $response->assertSee('Restaurant One');
        $response->assertSee('Restaurant Two');
    });

    it('shows vendor details to superadmin', function () {
        $vendor = Vendor::factory()->create([
            'user_id' => $this->vendorUser->id,
            'name' => 'Test Restaurant',
            'description' => 'A test restaurant',
            'address' => '123 Test Street',
            'phone' => '+1234567890',
            'brand_name' => 'TestBrand',
        ]);

        $response = $this->actingAs($this->superadmin, 'web')
            ->get('/superadmin/vendors/' . $vendor->id);

        $response->assertStatus(200);
        $response->assertViewIs('superadmin.vendors.show');
        $response->assertSee('Test Restaurant');
        $response->assertSee('A test restaurant');
        $response->assertSee('123 Test Street');
        $response->assertSee('+1234567890');
        $response->assertSee('TestBrand');
    });

    it('allows superadmin to edit vendor information', function () {
        $vendor = Vendor::factory()->create([
            'user_id' => $this->vendorUser->id,
            'name' => 'Original Name',
            'description' => 'Original description',
            'address' => 'Original address',
            'phone' => '+1111111111',
            'brand_name' => 'OriginalBrand',
        ]);

        $updatedData = [
            'name' => 'Updated Restaurant',
            'description' => 'Updated description',
            'address' => 'Updated address',
            'phone' => '+2222222222',
            'brand_name' => 'UpdatedBrand',
        ];

        $response = $this->actingAs($this->superadmin, 'web')
            ->put('/superadmin/vendors/' . $vendor->id, $updatedData);

        $response->assertRedirect('/superadmin/vendors');

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'name' => 'Updated Restaurant',
            'description' => 'Updated description',
            'address' => 'Updated address',
            'phone' => '+2222222222',
            'brand_name' => 'UpdatedBrand',
        ]);
    });

    it('allows superadmin to delete vendor account', function () {
        $vendor = Vendor::factory()->create([
            'user_id' => $this->vendorUser->id,
            'name' => 'Test Restaurant',
            'brand_name' => 'TestBrand',
        ]);

        $response = $this->actingAs($this->superadmin, 'web')
            ->delete('/superadmin/vendors/' . $vendor->id);

        $response->assertRedirect('/superadmin/vendors');

        $this->assertDatabaseMissing('vendors', [
            'id' => $vendor->id,
        ]);
    });

    it('sends notification when vendor account is created', function () {
        $vendorData = [
            'name' => 'Test Restaurant',
            'description' => 'A test restaurant',
            'address' => '123 Test Street',
            'phone' => '+1234567890',
            'brand_name' => 'TestBrand',
        ];

        Notification::fake();

        $response = $this->actingAs($this->superadmin, 'web')
            ->post('/superadmin/vendors', $vendorData);

        $vendor = Vendor::where('name', $vendorData['name'])->first();

        Notification::assertSentTo(
            $this->vendorUser,
            \App\Notifications\WelcomeVendorNotification::class,
            function ($notification) use ($vendor) {
                return $notification->vendor->id === $vendor->id;
            }
        );
    });
});
