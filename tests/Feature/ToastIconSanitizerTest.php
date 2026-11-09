<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;
use Tests\Fixtures\ToastRelayComponent;

/*
 * MaryUI compiles a toast's icon with Blade::render(). AppServiceProvider
 * refuses the browser's direct calls of the toast methods, but a component
 * relaying a browser value to an icon, from a listener or an action, calls
 * them itself: a call the guard never sees. App\Support\Toast, which every
 * component uses instead of MaryUI's trait, replaces an icon that is no
 * icon name before it reaches Blade. The requests go through the real
 * update endpoint.
 */

beforeEach(function () {
    // As in production
    config(['app.debug' => false]);

    Livewire::component('toast-relay', ToastRelayComponent::class);
    Route::middleware('web')->get('/toast-relay', fn () => Blade::render('<livewire:toast-relay />'));
});

/**
 * Sends this icon to the relay's listener, as the browser would
 */
function relayIcon(string $icon)
{
    return livewireRoundTrip(
        componentSnapshot('/toast-relay', 'toast-relay'),
        calls: [['path' => '', 'method' => '__dispatch', 'params' => ['show-toast', ['icon' => $icon]]]],
    );
}

test('an icon relayed from the browser is never compiled', function () {
    // A harmless expression: its result only shows if Blade evaluated it
    $response = relayIcon("o-bell' /> {{ 'relay'.(91 * 7).'probe' }} <x-mary-icon name='o-bell")
        ->assertOk()
        ->assertDontSee('relay637probe');

    // The toast still shows, with the default icon
    expect(json_encode($response->json('components.0.effects')))->toContain('Relayed')->toContain('svg');
});

test('a real icon name still shows', function () {
    $response = relayIcon('phosphor.info')->assertOk();

    expect(json_encode($response->json('components.0.effects')))->toContain('Relayed')->toContain('svg');
});

test('no component uses the MaryUI trait directly', function () {
    // Only the browser guard names it, to recognise every component using it
    $files = Finder::create()->files()->in([resource_path('views'), app_path()])->contains('use Mary\Traits\Toast;');

    expect(collect($files)->map(fn ($file) => $file->getRelativePathname())->values()->all())
        ->toBe(['Providers/AppServiceProvider.php']);
});
