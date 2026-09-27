<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\Fixtures\ToastingComponent;

/*
 * MaryUI's Toast trait adds toast(), success(), warning(), error() and
 * info() as public methods to every component using it, and the browser
 * can call any public method. toast() compiles its icon with Blade::render():
 * a forged icon was a Blade template run on the server, from the homepage,
 * without an account. The browser may not call these methods any more
 * (AppServiceProvider); a component's own actions still show their toasts.
 */

beforeEach(function () {
    // As in production: Livewire answers the refusal with an unlogged 419
    config(['app.debug' => false]);

    Log::spy();
});

/**
 * The calls of a round trip that runs this method
 */
function toastCall(string $method, array $params = []): array
{
    return [['path' => '', 'method' => $method, 'params' => $params]];
}

test('the browser cannot call a toast method', function (string $method, array $params) {
    livewireRoundTrip(componentSnapshot('/', 'pages::home'), calls: toastCall($method, $params))
        ->assertStatus(419);

    Log::shouldNotHaveReceived('error');
})->with([
    'toast' => ['toast', ['info', 'Forged']],
    'success' => ['success', ['Forged']],
    'warning' => ['warning', ['Forged']],
    'error' => ['error', ['Forged']],
    'info' => ['info', ['Forged']],
]);

test('a forged icon is never compiled', function () {
    // A harmless expression: its result only shows if Blade evaluated it
    $icon = "o-bell' /> {{ 'ssti'.(6 * 7).'probe' }} <x-mary-icon name='o-bell";

    livewireRoundTrip(componentSnapshot('/', 'pages::home'), calls: toastCall('toast', ['info', 'Forged', null, null, $icon]))
        ->assertStatus(419)
        ->assertDontSee('ssti42probe');

    Log::shouldNotHaveReceived('error');
});

test('an action still shows its own toast', function () {
    Livewire::component('toasting-component', ToastingComponent::class);
    Route::middleware('web')->get('/toasting-component', fn () => Blade::render('<livewire:toasting-component />'));
    $snapshot = componentSnapshot('/toasting-component', 'toasting-component');

    $response = livewireRoundTrip($snapshot, calls: toastCall('notify'))->assertOk();

    expect(json_encode($response->json('components.0.effects')))->toContain('Saved');

    livewireRoundTrip($snapshot, calls: toastCall('success', ['Forged']))->assertStatus(419);
});
