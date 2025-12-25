<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register and are redirected to login', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // User is created but NOT auto-logged in
    $this->assertGuest();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);

    // Redirects to login page with success message
    $response->assertRedirect(route('login'));
    $response->assertSessionHas('status', 'Account created successfully! Please sign in.');
});
