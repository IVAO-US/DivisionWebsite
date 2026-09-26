<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * An IVAO member, as the SSO would have stored them
 *
 * @param  array<string, mixed>  $attributes
 */
function createMember(array $attributes = []): User
{
    $vid = $attributes['vid'] ?? fake()->unique()->numberBetween(9000000, 9999999);

    return User::create([
        'vid' => $vid,
        'first_name' => 'Tester',
        'last_name' => 'Member',
        'email' => "member-{$vid}@example.test",
        'rating_atc' => 2,
        'rating_pilot' => 2,
        'country' => 'US',
        'division' => 'US',
        ...$attributes,
    ]);
}

/**
 * A member who is an administrator holding these permissions
 *
 * @param  list<string>  $permissions  AdminPermission values ('*' for a super administrator)
 * @param  array<string, mixed>  $attributes
 */
function createAdmin(array $permissions = [], array $attributes = []): User
{
    $user = createMember($attributes);

    Admin::create(['vid' => $user->vid, 'permissions' => $permissions]);

    return $user;
}

/**
 * The snapshot of a Livewire component as the browser receives it with a
 * page (its wire:snapshot attribute), for the current visitor or account
 */
function componentSnapshot(string $uri, string $name): string
{
    $html = test()->withoutVite()->get($uri)->assertOk()->getContent();

    preg_match_all('/wire:snapshot="([^"]*)"/', $html, $matches);

    foreach ($matches[1] as $attribute) {
        $snapshot = html_entity_decode($attribute, ENT_QUOTES);

        if ((json_decode($snapshot, true)['memo']['name'] ?? null) === $name) {
            return $snapshot;
        }
    }

    throw new RuntimeException("No {$name} component on {$uri}");
}

/**
 * One round trip to the real Livewire update endpoint, sent the way a
 * browser sends it: the updates and calls are whatever the browser chooses
 *
 * @param  array<string, mixed>  $updates
 * @param  list<array<string, mixed>>  $calls
 */
function livewireRoundTrip(string $snapshot, array $updates = [], array $calls = []): TestResponse
{
    return test()->postJson(Livewire::getUpdateUri(), [
        'components' => [[
            'snapshot' => $snapshot,
            'updates' => $updates,
            'calls' => $calls,
        ]],
    ], ['X-Livewire' => 'true']);
}
