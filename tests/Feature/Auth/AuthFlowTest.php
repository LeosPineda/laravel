<?php

use App\Models\User;
use App\Models\Vendor;

beforeEach(function () {
    // Clear any existing users
});

// Login Tests
it('shows login page', function () {
    $this->get('/login')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('auth/Login'));
});

it('allows superadmin to login and redirects to superadmin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'superadmin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    // Follow redirect to check final destination
    $this->get('/dashboard')
        ->assertRedirect(route('superadmin.dashboard'));
});

it('allows customer to login and redirects to customer home', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    // Follow redirect to check final destination
    $this->get('/dashboard')
        ->assertRedirect(route('customer.home'));
});

it('allows vendor to login and redirects to vendor dashboard', function () {
    $user = User::factory()->create([
        'role' => 'vendor',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    Vendor::create([
        'user_id' => $user->id,
        'brand_name' => 'Test Vendor',
        'is_active' => true,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    // Follow redirect to check final destination
    $this->get('/dashboard')
        ->assertRedirect(route('vendor.dashboard'));
});

it('prevents deactivated vendor from logging in', function () {
    $user = User::factory()->create([
        'role' => 'vendor',
        'is_active' => false,
        'email_verified_at' => now(),
    ]);

    Vendor::create([
        'user_id' => $user->id,
        'brand_name' => 'Test Vendor',
        'is_active' => false,
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
});

// Registration Tests
it('shows registration page', function () {
    $this->get('/register')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('auth/Register'));
});

it('allows new users to register as customers', function () {
    $response = $this->post('/register', [
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe('customer')
        ->and($user->is_active)->toBeTrue();
});

// Forgot Password Tests
it('shows forgot password page', function () {
    $this->get('/forgot-password')
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('auth/ForgotPassword'));
});

it('sends password reset link', function () {
    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertSessionHas('status');
});

// Logout Tests
it('allows users to logout', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'is_active' => true,
    ]);

    $this->actingAs($user);
    $this->assertAuthenticated();

    $response = $this->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

// Role-based Access Tests
it('prevents customers from accessing superadmin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('superadmin.dashboard'))
        ->assertStatus(403);
});

it('prevents customers from accessing vendor dashboard', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('vendor.dashboard'))
        ->assertStatus(403);
});

it('prevents vendors from accessing superadmin dashboard', function () {
    $user = User::factory()->create([
        'role' => 'vendor',
        'is_active' => true,
    ]);

    Vendor::create([
        'user_id' => $user->id,
        'brand_name' => 'Test',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('superadmin.dashboard'))
        ->assertStatus(403);
});

// Superadmin Vendor Creation Tests
it('allows superadmin to create vendor accounts', function () {
    $superadmin = User::factory()->create([
        'role' => 'superadmin',
        'is_active' => true,
    ]);

    $this->actingAs($superadmin)
        ->post(route('superadmin.vendors.store'), [
            'name' => 'New Vendor',
            'email' => 'vendor@example.com',
            'password' => 'password123',
            'brand_name' => 'Vendor Brand',
        ])
        ->assertRedirect(route('superadmin.vendors.index'));

    $vendor = User::where('email', 'vendor@example.com')->first();
    expect($vendor)->not->toBeNull()
        ->and($vendor->role)->toBe('vendor')
        ->and($vendor->is_active)->toBeTrue();

    expect(Vendor::where('user_id', $vendor->id)->exists())->toBeTrue();
});

it('prevents non-superadmin from creating vendors', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
        'is_active' => true,
    ]);

    $this->actingAs($customer)
        ->post(route('superadmin.vendors.store'), [
            'name' => 'New Vendor',
            'email' => 'vendor@example.com',
            'password' => 'password123',
            'brand_name' => 'Vendor Brand',
        ])
        ->assertStatus(403);
});
