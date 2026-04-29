<?php

use App\Models\User;

test('admin is redirected to admin dashboard when opening user dashboard url', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertRedirect(route('admin.dashboard'));
});

test('user is redirected to user dashboard when opening admin urls', function () {
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertRedirect(route('dashboard'));
});
