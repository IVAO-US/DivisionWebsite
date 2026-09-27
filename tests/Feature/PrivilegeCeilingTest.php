<?php

use App\Enums\AdminPermission;
use App\Models\Admin;
use App\Models\User;
use App\Services\AdminService;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/*
 * The ceiling of rights (CLAUDE.md, "Admin permission system"): an
 * administrator acts only within the permissions they hold, on permissions
 * and on other administrators. The admins table offers nothing beyond them;
 * a forged request beyond them gets a 403 and writes nothing. Super
 * administrators (*) hold every permission, so nothing changes for them.
 */

uses(DatabaseTransactions::class);

/**
 * The admin record of an account
 */
function adminOf(User $user): Admin
{
    return Admin::where('vid', $user->vid)->sole();
}

/**
 * The permissions stored for an account
 *
 * @return list<string>
 */
function storedPermissions(User $user): array
{
    return adminOf($user)->permissions ?? [];
}

/**
 * The admins table, as the given administrator sees it
 */
function adminsTable(User $actor): Testable
{
    return Livewire::actingAs($actor)->test('admins-list-table');
}

/**
 * Whether the checkbox of this permission value is disabled in the HTML
 */
function permissionCheckboxDisabled(string $html, string $value): bool
{
    $input = HTMLDocument::createFromString($html, LIBXML_NOERROR)
        ->querySelector('input[type="checkbox"][value="'.$value.'"]');

    if ($input === null) {
        throw new RuntimeException("No checkbox for {$value}");
    }

    return $input->hasAttribute('disabled');
}

test('an administrator may grant only the permissions they hold', function () {
    $service = app(AdminService::class);
    $actor = adminOf(createAdmin(['admins_edit_permissions', 'app_headline']));

    expect($service->grantable($actor))->toEqualCanonicalizing(['admins_edit_permissions', 'app_headline'])
        ->and($service->grantable(adminOf(createAdmin(['app']))))->toEqualCanonicalizing(['app', 'app_gdpr', 'app_headline'])
        ->and($service->withinRights($actor, ['app_headline']))->toBeTrue()
        ->and($service->withinRights($actor, ['app_headline', 'app_gdpr']))->toBeFalse()
        // A value that is no permission grants nothing, so it blocks nobody
        ->and($service->withinRights($actor, ['app_headline', 'polls_create']))->toBeTrue()
        ->and($service->grantable(null))->toBe([])
        ->and($service->withinRights(null, ['app_headline']))->toBeFalse()
        ->and($service->canActOnAdmin(null, adminOf(createAdmin())))->toBeFalse();
});

test('a super administrator acts on every administrator', function () {
    $service = app(AdminService::class);
    $super = adminOf(createAdmin(['*']));

    expect($service->grantable($super))->toEqualCanonicalizing(AdminPermission::getAllPermissions())
        ->and($service->canActOnAdmin($super, adminOf(createAdmin(['*']))))->toBeTrue();
});

test('AdminService refuses on its own what goes beyond the rights of its author', function () {
    $service = app(AdminService::class);
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);
    $member = createAdmin();
    $super = createAdmin(['*']);

    expect(fn () => $service->updatePermissions(adminOf($member), ['app_gdpr'], adminOf($actor)))
        ->toThrow(Exception::class, 'You can only grant or withdraw the permissions you hold yourself.')
        ->and(fn () => $service->updatePermissions(adminOf($super), [], adminOf($actor)))
        ->toThrow(Exception::class, 'You can only grant or withdraw the permissions you hold yourself.')
        ->and(fn () => $service->removeAdmin(adminOf($super), adminOf($actor)))
        ->toThrow(Exception::class, 'You can only remove an administrator whose permissions you all hold.')
        ->and(fn () => $service->updatePermissions(adminOf($actor), ['admins_edit_permissions'], adminOf($actor)))
        ->toThrow(Exception::class, 'You cannot modify your own permissions')
        ->and(fn () => $service->removeAdmin(adminOf($actor), adminOf($actor)))
        ->toThrow(Exception::class, 'You cannot remove yourself');

    expect(storedPermissions($member))->toBe([])
        ->and(storedPermissions($super))->toBe(['*'])
        ->and(storedPermissions($actor))->toEqualCanonicalizing(['admins_edit_permissions', 'app_headline']);
});

test('the edit window ticks only the permissions its author holds', function () {
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);
    $member = createAdmin([], ['first_name' => 'Wilbur', 'last_name' => 'Wright']);

    $table = adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->assertSet('editModal', true)
        ->assertSee('Some permissions are beyond your rights');

    $html = $table->html();

    expect(permissionCheckboxDisabled($html, 'app_headline'))->toBeFalse()
        ->and(permissionCheckboxDisabled($html, 'app_gdpr'))->toBeTrue()
        ->and(permissionCheckboxDisabled($html, '*'))->toBeTrue()
        // The window names the administrator it edits
        ->and(trim(HTMLDocument::createFromString($html, LIBXML_NOERROR)->querySelector('dialog h5')?->textContent ?? ''))->toBe('Wilbur Wright');

    // The header of a category ticks, then unticks, only what the author holds
    $table->call('toggleCategoryPermissions', 'Application')
        ->assertSet('selectedPermissions', ['app_headline'])
        ->call('toggleCategoryPermissions', 'Application')
        ->assertSet('selectedPermissions', []);
});

test('the edit window refuses what it does not offer', function (string $method, string $argument) {
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);
    $member = createAdmin();

    adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->call($method, $argument)
        ->assertForbidden();

    expect(storedPermissions($member))->toBe([]);
})->with([
    'the super administrator permission' => ['togglePermission', '*'],
    'a permission its author lacks' => ['togglePermission', 'app_gdpr'],
    'the super administrator category' => ['toggleCategoryPermissions', 'System-wide'],
    'a category its author holds nothing of' => ['toggleCategoryPermissions', 'Flight Operations'],
]);

test('the ticked permissions are locked against the browser', function () {
    $actor = createAdmin(['admins_edit_permissions']);
    $member = createAdmin();

    adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->set('selectedPermissions', ['*']);
})->throws(CannotUpdateLockedPropertyException::class);

test('an administrator grants, withdraws and removes within their rights', function () {
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);
    $member = createAdmin(['app_headline']);
    $empty = createAdmin();

    adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->call('togglePermission', 'app_headline')
        ->call('togglePermission', 'admins_edit_permissions')
        ->call('savePermissions')
        ->assertOk()
        ->assertSet('editModal', false);

    expect(storedPermissions($member))->toBe(['admins_edit_permissions']);

    // An administrator without permissions is always within reach
    adminsTable($actor)
        ->call('confirmDelete', adminOf($empty)->id)
        ->assertSet('deleteModal', true)
        ->call('deleteAdmin')
        ->assertOk();

    expect(Admin::where('vid', $empty->vid)->exists())->toBeFalse();
});

test('an administrator beyond the author rights is shown, never edited nor removed', function (string $method) {
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);
    $super = createAdmin(['*']);
    $member = createAdmin(['app_headline']);

    $this->actingAs($actor)->withoutVite()->get('/admin/manage')->assertOk()->assertSee('Beyond your rights');

    expect(adminsTable($actor)->viewData('beyond'))->toContain(adminOf($super)->id)
        ->not->toContain(adminOf($member)->id);

    adminsTable($actor)
        ->call($method, adminOf($super)->id)
        ->assertForbidden();

    expect(storedPermissions($super))->toBe(['*']);
})->with(['editAdmin', 'confirmDelete']);

test('an administrator cannot edit nor remove their own record, even by forged calls', function () {
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);

    adminsTable($actor)
        ->call('editAdmin', adminOf($actor)->id)
        ->assertSet('editModal', false)
        ->call('togglePermission', 'app_headline')
        ->call('savePermissions');

    adminsTable($actor)
        ->call('confirmDelete', adminOf($actor)->id)
        ->assertSet('deleteModal', false)
        ->call('deleteAdmin');

    expect(storedPermissions($actor))->toEqualCanonicalizing(['admins_edit_permissions', 'app_headline']);
});

test('an administrator who gains rights while their window is open is not saved', function () {
    $actor = createAdmin(['admins_edit_permissions', 'app_headline']);
    $member = createAdmin();

    $table = adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->call('togglePermission', 'app_headline');

    adminOf($member)->update(['permissions' => ['fltops']]);

    $table->call('savePermissions')->assertForbidden();

    expect(storedPermissions($member))->toBe(['fltops']);
});

test('an administrator who gains rights while their removal is confirmed is not removed', function () {
    $actor = createAdmin(['admins_edit_permissions']);
    $member = createAdmin();

    $table = adminsTable($actor)->call('confirmDelete', adminOf($member)->id);

    adminOf($member)->update(['permissions' => ['*']]);

    $table->call('deleteAdmin')->assertForbidden();

    expect(Admin::where('vid', $member->vid)->exists())->toBeTrue();
});

test('the rights of the author are read again when saving', function () {
    $actor = createAdmin(['admins_edit_permissions', 'app_gdpr']);
    $member = createAdmin();

    $table = adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->call('togglePermission', 'app_gdpr');

    adminOf($actor)->update(['permissions' => ['admins_edit_permissions']]);

    $table->call('savePermissions')->assertForbidden();

    expect(storedPermissions($member))->toBe([]);
});

test('the super administrator permission is granted by a super administrator only', function () {
    // A category holds its granular permissions: this one manages administrators
    $actor = createAdmin(['admins']);
    $member = createAdmin();

    adminsTable($actor)
        ->call('editAdmin', adminOf($member)->id)
        ->call('togglePermission', '*')
        ->assertForbidden();

    expect(storedPermissions($member))->toBe([]);

    adminsTable(createAdmin(['*']))
        ->call('editAdmin', adminOf($member)->id)
        ->call('togglePermission', '*')
        ->call('savePermissions')
        ->assertOk();

    expect(storedPermissions($member))->toBe(['*']);
});

test('a GDPR operator cannot erase an administrator', function () {
    $operator = createAdmin(['app_gdpr']);
    $admin = createAdmin(['fltops_tours'], ['first_name' => 'Orville']);

    $screen = Livewire::actingAs($operator)
        ->test('pages::protected.admin.app.gdpr')
        ->call('selectUser', $admin->vid);

    $screen->set('controlKeyInput', $screen->get('controlKey'))
        ->call('openConfirmationModal')
        ->assertSet('showConfirmationModal', false)
        ->call('executeDeletion');

    expect($admin->fresh()->first_name)->toBe('Orville');
});
