<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\Fixtures\FailingComponent;

/*
 * A forged Livewire request, whose updates or calls Livewire (or the
 * container) refuses before any code of the site runs, gets a 419 and is
 * not logged (App\Http\ForgedLivewireRequest). An error of the site's own
 * code is still answered 500 and logged. The requests go through the real
 * update endpoint, and the log is spied where production writes it.
 */

uses(DatabaseTransactions::class);

beforeEach(function () {
    // As in production: debug pages show and log everything
    config(['app.debug' => false]);

    Log::spy();
});

/**
 * The calls of a round trip that runs this method
 */
function forgedCall(string $method, array $params = []): array
{
    return [['path' => '', 'method' => $method, 'params' => $params]];
}

test('a forged update gets an unlogged 419', function (array $updates) {
    livewireRoundTrip(componentSnapshot('/', 'navbar'), updates: $updates)->assertStatus(419);

    Log::shouldNotHaveReceived('error');
})->with([
    'a deep write into a scalar' => [['mobileMenuOpen.x' => 1]],
    'an unknown property' => [['nope' => 1]],
    'a locked property' => [['isAdmin' => true]],
]);

test('a forged call gets an unlogged 419', function (array $calls) {
    livewireRoundTrip(componentSnapshot('/', 'navbar'), calls: $calls)->assertStatus(419);

    Log::shouldNotHaveReceived('error');
})->with([
    'an unknown method' => [forgedCall('nope')],
    'a lifecycle hook' => [forgedCall('mount')],
    'an event nobody listens to' => [forgedCall('__dispatch', ['nope', []])],
]);

test('a forged parameter of an action gets an unlogged 419', function (array $params) {
    $this->actingAs(createAdmin(['admins_edit_permissions']));

    livewireRoundTrip(componentSnapshot('/admin/manage', 'admins-list-table'), calls: forgedCall('editAdmin', $params))
        ->assertStatus(419);

    Log::shouldNotHaveReceived('error');
})->with([
    'a missing parameter' => [[]],
    'a parameter of the wrong type' => [['x']],
]);

test('an error of the site code is still answered 500 and logged', function (string $method) {
    Livewire::component('failing-component', FailingComponent::class);
    Route::middleware('web')->get('/failing-component', fn () => Blade::render('<livewire:failing-component />'));

    livewireRoundTrip(componentSnapshot('/failing-component', 'failing-component'), calls: forgedCall($method))
        ->assertStatus(500);

    Log::shouldHaveReceived('error')->once();
})->with([
    'in an action' => ['fail'],
    'once the action has run' => ['dispatchNowhere'],
    'an action typed with an unbound interface' => ['useService'],
]);

test('in debug mode, a forged request keeps its detailed error and is logged', function () {
    config(['app.debug' => true]);

    livewireRoundTrip(componentSnapshot('/', 'navbar'), updates: ['mobileMenuOpen.x' => 1])->assertStatus(500);

    Log::shouldHaveReceived('error')->once();
});
