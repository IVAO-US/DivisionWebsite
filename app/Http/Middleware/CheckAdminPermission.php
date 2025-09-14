<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Enums\AdminPermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        // User shall always be logged in using the IVAO SSO prior to being checked as an admin
        $user = Auth::user();
        
        if (!$user) {
            return $next($request);
        }
        
        /* Make sure permission exists */
        $adminPermission = AdminPermission::tryFrom($permission);
        if (!$adminPermission) {
            abort(500, "Invalid permission: {$permission}");
        }

        $hasPermission = Admin::hasPermissionOrCategoryAccess($user->vid, $adminPermission);
        
        if (!$hasPermission) {
            Session::put('session_toast', [
                'type' => 'error',
                'title' => 'Insufficient Permissions',
                'description' => $this->getPermissionErrorMessage($adminPermission),
                'position' => 'toast-top toast-end', 
                'icon' => 'phosphor.lock',
                'css' => 'alert-error',
                'timeout' => 5000,
                'redirectTo' => null
            ]);
            return redirect()->route('home');
        }
        
        return $next($request);
    }

    /**
     * Get appropriate error message based on permission type
     */
    private function getPermissionErrorMessage(AdminPermission $permission): string
    {
        // Check if the permission is a category-wide permission
        if (!str_contains($permission->value, '_') && $permission !== AdminPermission::ALL) {
            return "You need permissions in the '{$permission->category()}' category to access this area.";
        }
        
        return "You need '{$permission->description()}' permission to access this area.";
    }
}