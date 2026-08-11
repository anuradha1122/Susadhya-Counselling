<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration page is not available', function () {
    $this->get('/register')
        ->assertNotFound();
});

test('registration requests cannot create users', function () {
    $this->post('/register', [
        'name' => 'Unauthorized User',
        'email' => 'unauthorized@example.com',
        'password' => 'Password@123',
        'password_confirmation' => 'Password@123',
    ])->assertNotFound();

    $this->assertGuest();

    $this->assertDatabaseMissing('users', [
        'email' => 'unauthorized@example.com',
    ]);
});
