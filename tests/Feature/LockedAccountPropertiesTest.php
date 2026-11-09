<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * The other components that keep the signed-in account (CLAUDE.md,
 * "Conventions & gotchas"): the navbar, on every page, and the division
 * transfer and GCA pages. As on auth-button, their account is locked: a
 * forged write gets Livewire's 419, never a 500. The requests go through
 * the real update endpoint.
 */

uses(DatabaseTransactions::class);

beforeEach(function () {
    // As in production: Livewire answers a refused write with a 419
    config(['app.debug' => false]);
});

test('a forged write of the account is refused with a 419, not a 500', function (string $uri, string $component) {
    livewireRoundTrip(componentSnapshot($uri, $component), updates: ['user.name' => 'Pirate'])
        ->assertStatus(419);
})->with([
    'the navbar' => ['/', 'navbar'],
    'the transfer page' => ['/division/transfer', 'pages::division.transfer'],
    'the GCA page' => ['/training/gca', 'pages::training.gca'],
]);

test('a forged admin flag of the navbar is refused', function () {
    livewireRoundTrip(componentSnapshot('/', 'navbar'), updates: ['isAdmin' => true])
        ->assertStatus(419);
});

test('a visitor snapshot of the transfer page replayed once signed in still renders', function () {
    $snapshot = componentSnapshot('/division/transfer', 'pages::division.transfer');

    $this->actingAs(createMember());

    livewireRoundTrip($snapshot, calls: [['path' => '', 'method' => '$refresh', 'params' => []]])
        ->assertOk();
});

test('members and administrators still get their own details', function () {
    $this->actingAs(createMember(['division' => 'XU']))
        ->withoutVite()
        ->get('/division/transfer')
        ->assertOk()
        ->assertSee('XU-hq@ivao.aero');

    $this->withoutVite()->get('/')->assertOk()->assertDontSee('Admin Panel');

    $this->actingAs(createAdmin())
        ->withoutVite()
        ->get('/')
        ->assertOk()
        ->assertSee('Admin Panel');
});
