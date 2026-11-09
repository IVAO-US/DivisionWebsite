<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;

/*
 * The rate limits of routes/web.php (CLAUDE.md, "Rate limiting"): each named
 * limiter has a counter of its own, per account, or per visitor address for
 * a guest. An unnamed throttle:N,1 counts under one key per account (per IP
 * address), whatever the route, so the SEO files spent the pages' budget.
 * The probes are redirects of the pages group: no view, no database.
 *
 * Guests reach the site through Cloudflare, then the host's local proxy:
 * PHP sees 127.0.0.1 and the visitor's address in X-Forwarded-For
 * (bootstrap/app.php). The visitors below use public addresses: Symfony
 * counts the documentation ranges as private, hence as no address at all.
 */

uses(DatabaseTransactions::class);

afterEach(function () {
    Checksum::disableRateLimitingForTesting();
});

/**
 * The call of a Livewire round trip that only renders the component again
 */
const REFRESH = [['path' => '', 'method' => '$refresh', 'params' => []]];

/**
 * Sends the next requests as this visitor, through Cloudflare and the
 * host's local proxy
 */
function asVisitor(string $address): void
{
    test()->withServerVariables([
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_X_FORWARDED_FOR' => "{$address}, 173.245.48.10",
    ]);
}

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
    asVisitor('1.2.3.4');

    spendRobots(60);

    $this->get('/users')->assertRedirect();
});

test('the 61st page of the minute is refused', function () {
    asVisitor('1.2.3.4');

    spendPages(60);

    $this->get('/users')->assertStatus(429);
});

test('the 101st robots.txt of the minute is refused', function () {
    asVisitor('1.2.3.4');

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

test('each visitor has a counter of their own', function () {
    asVisitor('1.2.3.4');
    spendPages(60);
    $this->get('/users')->assertStatus(429);

    asVisitor('1.2.3.5');
    $this->get('/users')->assertRedirect();
});

test('a visitor cannot pick another counter with X-Forwarded-For', function () {
    // Cloudflare appends the address it sees to the header the browser sent
    foreach (range(1, 60) as $request) {
        $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_FOR' => "5.6.7.{$request}, 1.2.3.4, 173.245.48.10",
        ])->get('/division')->assertRedirect();
    }

    $this->get('/users')->assertStatus(429);
});

test('a client that reaches PHP directly is counted by its own address', function () {
    // Its address is no trusted proxy: the header it sends is ignored
    foreach (range(1, 60) as $request) {
        $this->withServerVariables([
            'REMOTE_ADDR' => '9.9.9.9',
            'HTTP_X_FORWARDED_FOR' => "5.6.7.{$request}",
        ])->get('/division')->assertRedirect();
    }

    $this->get('/users')->assertStatus(429);
});

test('a visitor whose address is unknown is not limited', function () {
    // No X-Forwarded-For: the proxy's address would be every guest's counter
    spendPages(61);

    $this->get('/users')->assertRedirect();
});

test('the sessions table stores no IP address', function () {
    config(['session.driver' => 'database']);

    $this->withServerVariables([
        'REMOTE_ADDR' => '127.0.0.1',
        'HTTP_X_FORWARDED_FOR' => '1.2.3.4, 173.245.48.10',
        'HTTP_USER_AGENT' => 'session-address-probe',
    ])->get('/division')->assertRedirect();

    $session = DB::table('sessions')->where('user_agent', 'session-address-probe')->first();

    expect($session)->not->toBeNull()
        ->and($session->ip_address)->toBeNull();
});

test('Livewire round trips spend no page budget', function () {
    asVisitor('1.2.3.4');
    $snapshot = componentSnapshot('/', 'auth-button');

    foreach (range(1, 61) as $roundTrip) {
        livewireRoundTrip($snapshot, calls: REFRESH)->assertOk();
    }

    $this->get('/users')->assertRedirect();
});

test('the Livewire update route has a limit of its own', function () {
    $route = app('router')->getRoutes()->getByName('livewire.update');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('throttle:livewire-update')
        ->and(Livewire::getUpdateUri())->toBe('/'.$route->uri());
});

test('the 601st Livewire round trip of the minute is refused', function () {
    asVisitor('1.2.3.4');
    $snapshot = componentSnapshot('/', 'auth-button');

    foreach (range(1, 600) as $roundTrip) {
        livewireRoundTrip($snapshot, calls: REFRESH)->assertOk();
    }

    livewireRoundTrip($snapshot, calls: REFRESH)->assertStatus(429);

    asVisitor('1.2.3.5');
    livewireRoundTrip($snapshot, calls: REFRESH)->assertOk();
});

test('ten forged checksums turn nobody away', function () {
    // Livewire answered 429 to every Livewire request of that address for 10 minutes
    config(['app.debug' => false]);
    Checksum::enableRateLimitingForTesting();

    $snapshot = componentSnapshot('/', 'auth-button');
    $forged = json_encode(['checksum' => str_repeat('0', 64)] + json_decode($snapshot, true));

    foreach (range(1, 10) as $request) {
        livewireRoundTrip($forged, calls: REFRESH)->assertStatus(419);
    }

    $this->actingAs(createMember());

    livewireRoundTrip($snapshot, calls: REFRESH)->assertOk();
});
