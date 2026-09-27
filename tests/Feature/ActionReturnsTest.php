<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * Livewire returns the value of every method a request calls, and any
 * public method can be called: with(), a legacy get*Property() or a public
 * helper would hand out the models they return, every attribute included.
 * No return value reaches the browser (AppServiceProvider). The requests go
 * through the real update endpoint.
 */

uses(DatabaseTransactions::class);

test('a forged call hands out no data', function (string $method) {
    $this->actingAs(createAdmin(['admins_edit_permissions']));
    createAdmin([], ['email' => 'hidden-admin@example.test']);

    $response = livewireRoundTrip(
        componentSnapshot('/admin/manage', 'admins-list-table'),
        calls: [['path' => '', 'method' => $method, 'params' => []]],
    );

    $response->assertOk()->assertDontSee('hidden-admin@example.test');
    expect($response->json('components.0.effects.returns'))->toBe([null]);
})->with(['with', 'getAdminsProperty']);

test('an action that redirects still redirects', function () {
    $this->actingAs(createMember());

    $response = livewireRoundTrip(
        componentSnapshot('/', 'auth-button'),
        calls: [['path' => '', 'method' => 'logout', 'params' => []]],
    );

    $response->assertOk();
    expect($response->json('components.0.effects.redirect'))->toBe(route('home'));
});
