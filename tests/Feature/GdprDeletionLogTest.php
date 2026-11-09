<?php

use App\Models\GdprDeletionLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/*
 * The GDPR page lists its deletion logs on every load (MaryUI renders every
 * tab pane). It stores the number of sessions a deletion removed, which the
 * log summary counted as a list: once a deletion had removed a session, the
 * whole page answered 500.
 */

uses(DatabaseTransactions::class);

/**
 * A deletion log of the GDPR page, with the data it deleted
 */
function deletionLog(array $deletedData): GdprDeletionLog
{
    return GdprDeletionLog::create([
        'user_vid' => 1234567,
        'user_full_name' => 'Deleted Member',
        'user_email' => 'deleted-user-1234567@example.test',
        'admin_vid' => 7654321,
        'admin_name' => 'Operator',
        'control_key' => str_repeat('k', 32),
        'deleted_data' => $deletedData,
        'reason' => 'Member request',
        'executed_at' => now(),
    ]);
}

test('the GDPR page lists a deletion that removed sessions', function () {
    deletionLog(['user' => ['vid' => 1234567], 'sessions' => 2]);

    $this->actingAs(createAdmin(['app_gdpr']))
        ->withoutVite()
        ->get('/admin/app/gdpr')
        ->assertOk()
        ->assertSee('User profile, 2 session(s)');
});

test('the summary counts sessions stored as a number or as a list', function (int|array $sessions, string $summary) {
    expect(deletionLog(['sessions' => $sessions])->deleted_data_summary)->toBe($summary);
})->with([
    'a number' => [3, '3 session(s)'],
    'a list' => [[['id' => 'a'], ['id' => 'b']], '2 session(s)'],
    'none' => [0, ''],
]);
