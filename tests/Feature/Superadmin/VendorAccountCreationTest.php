<?php

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(RefreshDatabase::class);

describe('Superadmin Vendor Account Creation', function () {
    it('allows superadmin to create vendor account', function () {
        // Create superadmin user
        $superadmin = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $vendorData = [
            'name' => 'Test Restaurant',
            'email' => 'vendor@test.com',
            'password' => 'password',
            'brand_name' => 'TestBrand',
        ];

        $response = test()->actingAs($superadmin, 'web')
            ->post('/superadmin/vendors', $vendorData);

        $response->assertRedirect('/superadmin/vendors');

        test()->assertDatabaseHas('vendors', [
            'brand_name' => $vendorData['brand_name'],
        ]);

        $vendor = Vendor::where('brand_name', $vendorData['brand_name'])->first();
        expect($vendor)->not->toBeNull();
    });

    it('validates required fields when creating vendor', function () {
        // Create superadmin user
        $superadmin = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $invalidData = [
            'name' => '',
            'email' => '',
            'password' => '',
            'brand_name' => '',
        ];

        $response = test()->actingAs($superadmin, 'web')
            ->post('/superadmin/vendors', $invalidData);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'brand_name']);
    });

    it('prevents non-superadmin users from creating vendors', function () {
        // Create regular vendor user
        $vendorUser = User::factory()->create([
            'email' => 'vendor@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $vendorData = [
            'name' => 'Test Restaurant',
            'email' => 'vendor@test.com',
            'password' => 'password',
            'brand_name' => 'TestBrand',
        ];

        $response = test()->actingAs($vendorUser, 'web')
            ->post('/superadmin/vendors', $vendorData);

        $response->assertStatus(403);
    });

    it('allows superadmin to toggle vendor active status', function () {
        // Create superadmin user
        $superadmin = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        // Create vendor user and vendor
        $vendorUser = User::factory()->create([
            'email' => 'vendor@test.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $vendor = Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'brand_name' => 'TestBrand',
            'is_active' => true,
        ]);

        expect($vendor->is_active)->toBeTrue();
        expect($vendorUser->is_active)->toBeTrue();

        $response = test()->actingAs($superadmin, 'web')
            ->patch('/superadmin/vendors/' . $vendor->id . '/toggle-active');

        $response->assertRedirect('/superadmin/vendors');

        $vendor->refresh();
        $vendorUser->refresh();

        expect($vendor->is_active)->toBeFalse();
        expect($vendorUser->is_active)->toBeFalse();
    });
});
