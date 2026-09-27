<?php

namespace App\Services;

use App\Enums\AdminPermission;
use App\Models\Admin;
use Exception;

/**
 * Administrators and the ceiling of rights (CLAUDE.md, "Admin permission
 * system"): an administrator acts only within the permissions they hold.
 * They grant or withdraw only those, and they edit or remove another
 * administrator only when they hold every permission of that administrator,
 * before and after the change. Holding follows AdminPermission::implies(): a
 * category (app) holds its granular permissions (app_gdpr), not the reverse,
 * and a super administrator (*) holds them all.
 */
class AdminService
{
    /**
     * Message of the AccessDeniedHttpException a screen throws for a request
     * beyond the rights of its administrator: the screens offer nothing
     * beyond them, so such a request was forged (a 403, not logged)
     */
    public const BEYOND_RIGHTS = 'Beyond the rights of the acting administrator';

    /**
     * Permission values $actor holds: all it may grant or withdraw, and the
     * ceiling of the administrators it may act on
     *
     * @return list<string>
     */
    public function grantable(?Admin $actor): array
    {
        $held = collect($actor?->getPermissionEnums() ?? []);

        return collect(AdminPermission::cases())
            ->filter(fn (AdminPermission $permission) => $held->contains(fn (AdminPermission $holding) => $holding->implies($permission)))
            ->map(fn (AdminPermission $permission) => $permission->value)
            ->values()
            ->all();
    }

    /**
     * Whether $actor holds every one of these permissions. A value that is no
     * AdminPermission grants nothing (Admin::hasPermission() skips it), so it
     * is no right to hold: a stale value blocks nobody.
     *
     * @param  iterable<mixed>  $permissions
     */
    public function withinRights(?Admin $actor, iterable $permissions): bool
    {
        $grantable = $this->grantable($actor);

        return collect($permissions)
            ->filter(fn (mixed $value) => is_string($value) && AdminPermission::tryFrom($value) !== null)
            ->every(fn (string $value) => in_array($value, $grantable, true));
    }

    /**
     * Whether $actor may edit or remove this administrator: it holds every
     * permission of the administrator
     */
    public function canActOnAdmin(?Admin $actor, Admin $target): bool
    {
        return $actor !== null && $this->withinRights($actor, $target->permissions ?? []);
    }

    /**
     * Gives $target exactly these permissions, within the rights of $actor
     *
     * @param  list<string>  $permissions
     *
     * @throws Exception when $target is $actor, or is or would be beyond the rights of $actor
     */
    public function updatePermissions(Admin $target, array $permissions, Admin $actor): void
    {
        $target->refresh();

        if ($target->is($actor)) {
            throw new Exception('You cannot modify your own permissions');
        }

        // The administrator as they are and as they would be: both within the actor's rights
        if (! $this->canActOnAdmin($actor, $target) || ! $this->withinRights($actor, $permissions)) {
            throw new Exception('You can only grant or withdraw the permissions you hold yourself.');
        }

        $target->update(['permissions' => array_values($permissions)]);
    }

    /**
     * Removes $target from the administrators, within the rights of $actor
     *
     * @throws Exception when $target is $actor, or is beyond the rights of $actor
     */
    public function removeAdmin(Admin $target, Admin $actor): void
    {
        $target->refresh();

        if ($target->is($actor)) {
            throw new Exception('You cannot remove yourself');
        }

        if (! $this->canActOnAdmin($actor, $target)) {
            throw new Exception('You can only remove an administrator whose permissions you all hold.');
        }

        $target->delete();
    }
}
