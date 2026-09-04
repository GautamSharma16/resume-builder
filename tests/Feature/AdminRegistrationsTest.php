<?php

use App\Models\User;
use Illuminate\Support\Carbon;

test('admin can view day wise account registration counts', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    User::factory()->create(['role' => 'user', 'created_at' => Carbon::parse('2026-08-01 09:00:00')]);
    User::factory()->create(['role' => 'user', 'created_at' => Carbon::parse('2026-08-01 11:00:00')]);
    User::factory()->create(['role' => 'user', 'created_at' => Carbon::parse('2026-08-02 11:00:00')]);
    User::factory()->create(['role' => 'company', 'created_at' => Carbon::parse('2026-08-02 12:00:00')]);

    $response = $this->actingAs($admin)->get(route('admin.registrations', [
        'from' => '2026-08-01',
        'to' => '2026-08-02',
    ]));

    $response->assertOk();
    $response->assertSee('Daily user sign-ups');
    $response->assertSee('2026-08-01');
    $response->assertSee('2026-08-02');
    $response->assertSee('>2<', false);
    $response->assertSee('>1<', false);
});

test('regular users cannot access registration statistics', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get(route('admin.registrations'));

    $response->assertRedirect(route('dashboard'));
});
