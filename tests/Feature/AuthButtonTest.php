<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/*
 * The account button of the navbar: a visitor's component holds no account,
 * and a forged request that writes one is refused the way Livewire refuses
 * any forged write (419), never with a 500 (CLAUDE.md, "Conventions &
 * gotchas"). The requests go through the real update endpoint.
 */

uses(DatabaseTransactions::class);

beforeEach(function () {
    // As in production: Livewire answers a refused write with a 419
    config(['app.debug' => false]);
});

test('a forged write of the account is refused with a 419, not a 500', function (string $path, mixed $value) {
    livewireRoundTrip(componentSnapshot('/', 'auth-button'), updates: [$path => $value])
        ->assertStatus(419);
})->with([
    'the whole account' => ['user', 1],
    'one of its fields' => ['user.name', 'Pirate'],
]);

test('a visitor snapshot replayed once signed in still renders', function () {
    $snapshot = componentSnapshot('/', 'auth-button');

    $this->actingAs(createMember());

    livewireRoundTrip($snapshot, calls: [['path' => '', 'method' => '$refresh', 'params' => []]])
        ->assertOk();
});

test('the account of the button is locked against the browser', function () {
    Livewire::actingAs(createMember())
        ->test('auth-button')
        ->set('user.name', 'Pirate');
})->throws(CannotUpdateLockedPropertyException::class);

test('a signed-in member still gets their menu', function () {
    $this->actingAs(createMember(['first_name' => 'Amelia']))
        ->withoutVite()
        ->get('/')
        ->assertOk()
        ->assertSee('Amelia')
        ->assertSee('My Profile')
        ->assertSee('Log out');
});
