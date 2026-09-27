<?php

/*
 * BreadcrumbsTrait reaches every page through HasSEO. Its getBreadcrumbs()
 * was public, so the browser could call it on any page: harmless (read
 * only, return value never sent), but only what the browser calls stays
 * public. Every caller uses it from inside a class using the trait.
 */

beforeEach(function () {
    // As in production: Livewire answers an unknown method with a 419
    config(['app.debug' => false]);
});

test('the browser cannot call getBreadcrumbs on a page', function () {
    livewireRoundTrip(
        componentSnapshot('/division/transfer', 'pages::division.transfer'),
        calls: [['path' => '', 'method' => 'getBreadcrumbs', 'params' => []]],
    )->assertStatus(419);
});

test('a page still shows its breadcrumbs', function () {
    $this->withoutVite()
        ->get('/division/transfer')
        ->assertOk()
        ->assertSeeInOrder(['Division', 'Transfer']);
});
