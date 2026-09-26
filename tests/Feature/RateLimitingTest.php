<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * The rate limits of routes/web.php (CLAUDE.md, "Rate limiting"): each named
 * limiter has a counter of its own, per account, or per IP address for a
 * guest. An unnamed throttle:N,1 counts under one key per account (per IP
 * address), whatever the route, so the SEO files spent the pages' budget.
 * The probes are redirects of the pages group: no view, no database.
 */

uses(DatabaseTransactions::class);

/**
 * Spends this many requests of the pages budget
 */
function spendPages(int $times): void
{
    foreach (range(1, $times) as $request) {
        test()->get('/division')->assertRedirect();
    }
}

/**
 * Fetches robots.txt this many times
 */
function spendRobots(int $times): void
{
    foreach (range(1, $times) as $request) {
        test()->get('/robots.txt')->assertOk();
    }
}

test('the SEO files do not spend the pages budget of a member', function () {
    $this->actingAs(createMember());

    spendRobots(60);

    $this->get('/users')->assertRedirect();
});

test('the SEO files do not spend the pages budget of a guest', function () {
    spendRobots(60);

    $this->get('/users')->assertRedirect();
});

test('the 61st page of the minute is refused', function () {
    spendPages(60);

    $this->get('/users')->assertStatus(429);
});

test('the 101st robots.txt of the minute is refused', function () {
    spendRobots(100);

    $this->get('/robots.txt')->assertStatus(429);
});

test('each member has a counter of their own', function () {
    $this->actingAs(createMember());
    spendPages(60);
    $this->get('/users')->assertStatus(429);

    $this->actingAs(createMember());
    $this->get('/users')->assertRedirect();
});

test('Livewire round trips spend no page budget', function () {
    // The update endpoint has no throttle, on purpose (bootstrap/app.php)
    $snapshot = componentSnapshot('/', 'auth-button');

    foreach (range(1, 61) as $roundTrip) {
        livewireRoundTrip($snapshot, calls: [['path' => '', 'method' => '$refresh', 'params' => []]])->assertOk();
    }

    $this->get('/users')->assertRedirect();
});
