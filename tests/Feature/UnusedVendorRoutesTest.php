<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * The vendor routes the site does not use answer 404, like any unknown URL
 * (App\Http\Middleware\BlockUnusedVendorRoutes): MaryUI's upload, spotlight
 * and sidebar toggle, and Livewire's file upload and preview. No component
 * of the site uploads a file.
 */

uses(DatabaseTransactions::class);

test('an unused vendor route answers 404', function (string $method, string $route, array $parameters = []) {
    $this->actingAs(createMember());

    $this->call($method, route($route, $parameters))->assertNotFound();
})->with([
    'MaryUI upload' => ['POST', 'mary.upload'],
    'MaryUI spotlight' => ['GET', 'mary.spotlight'],
    'MaryUI sidebar toggle' => ['GET', 'mary.toogle-sidebar'],
    'Livewire upload' => ['POST', 'livewire.upload-file'],
    'Livewire preview' => ['GET', 'livewire.preview-file', ['filename' => 'probe.png']],
]);
