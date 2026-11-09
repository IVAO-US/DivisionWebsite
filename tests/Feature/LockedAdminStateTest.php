<?php

use App\Models\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

/*
 * The admin pages keep state the server sets and the browser never writes:
 * the dashboard's account and permissions, the manual-entry flag of the
 * add-admin form, the GDPR page's selected member, control key, search
 * results and page size. Locked, a forged write is refused with a 419
 * instead of being accepted. The requests go through the real update
 * endpoint.
 */

uses(DatabaseTransactions::class);

beforeEach(function () {
    // As in production: Livewire answers a refused write with a 419
    config(['app.debug' => false]);
});

test('a forged write of an admin page state is refused', function (string $uri, string $component, string $permission, array $updates) {
    $this->actingAs(createAdmin([$permission]));

    livewireRoundTrip(componentSnapshot($uri, $component), updates: $updates)->assertStatus(419);
})->with([
    'the dashboard account' => ['/admin/dashboard', 'pages::protected.admin.index', 'app_headline', ['user' => null]],
    'the dashboard admin record' => ['/admin/dashboard', 'pages::protected.admin.index', 'app_headline', ['admin' => null]],
    'the dashboard permissions' => ['/admin/dashboard', 'pages::protected.admin.index', 'app_headline', ['categorizedPermissions' => []]],
    'the manual-entry flag' => ['/admin/manage', 'pages::protected.admin.manage', 'admins_edit_permissions', ['isManualEntry' => true]],
    'the acting VID the page used to keep' => ['/admin/manage', 'pages::protected.admin.manage', 'admins_edit_permissions', ['currentUserVid' => 1]],
    'the GDPR selected member' => ['/admin/app/gdpr', 'pages::protected.admin.app.gdpr', 'app_gdpr', ['selectedUser' => null]],
    'the GDPR control key' => ['/admin/app/gdpr', 'pages::protected.admin.app.gdpr', 'app_gdpr', ['controlKey' => 'forged']],
    'the GDPR search results' => ['/admin/app/gdpr', 'pages::protected.admin.app.gdpr', 'app_gdpr', ['searchResults' => [['vid' => 1]]]],
    'the GDPR page size' => ['/admin/app/gdpr', 'pages::protected.admin.app.gdpr', 'app_gdpr', ['perPage' => 100000]],
]);

test('an administrator still adds a member as admin', function () {
    $this->actingAs(createAdmin(['admins_edit_permissions']));
    $member = createMember();

    Livewire::test('pages::protected.admin.manage')
        ->set('selectedVid', $member->vid)
        ->assertSet('selectedName', $member->full_name)
        ->assertSet('isManualEntry', false)
        ->call('addAdmin')
        ->assertHasNoErrors();

    expect(Admin::where('vid', $member->vid)->exists())->toBeTrue();
});

test('an unknown VID still switches the form to manual entry', function () {
    $this->actingAs(createAdmin(['admins_edit_permissions']));

    Livewire::test('pages::protected.admin.manage')
        ->set('selectedVid', 1234567)
        ->assertSet('isManualEntry', true);
});

test('an administrator without the permission cannot add an admin', function () {
    $this->actingAs(createAdmin(['app_headline']));
    $member = createMember();

    Livewire::test('pages::protected.admin.manage')
        ->set('selectedVid', $member->vid)
        ->call('addAdmin');

    expect(Admin::where('vid', $member->vid)->exists())->toBeFalse();
});

test('a GDPR operator still selects a member and confirms the key', function () {
    $this->actingAs(createAdmin(['app_gdpr']));
    $member = createMember();

    $page = Livewire::test('pages::protected.admin.app.gdpr')->call('selectUser', $member->vid);

    $page->set('controlKeyInput', $page->get('controlKey'))
        ->call('openConfirmationModal')
        ->assertSet('showConfirmationModal', true);
});
