<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AdminPermission;

class Admin extends Model
{
    protected $fillable = [
        'vid', 'permissions'
    ];
    
    protected $casts = [
        'permissions' => 'array',
    ];
    
    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vid', 'vid');
    }
    

    /** Business logics */
    
    /**
     * Check if admin
     */
    public static function isAdmin(int $vid): bool
    {
        return self::where('vid', $vid)->exists();
    }

    
    /**
     * Check for a specific permission for a specific admin (as AdminPermission)
     */
    public static function hasPermission(int $vid, AdminPermission $permission): bool
    {
        $admin = self::where('vid', $vid)->first();
        
        if (!$admin || !$admin->permissions) {
            return false;
        }
        
        foreach ($admin->permissions as $adminPermissionValue) {
            $adminPermission = AdminPermission::tryFrom($adminPermissionValue);
            
            if ($adminPermission && $adminPermission->implies($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Enhanced permission check that handles category-based access
     * This method allows access if the user has ANY permission in a category when requesting category access
     */
    public static function hasPermissionOrCategoryAccess(int $vid, AdminPermission $permission): bool
    {
        // First check if user has the exact permission (includes global permissions via implies())
        if (self::hasPermission($vid, $permission)) {
            return true;
        }

        // If the requested permission is a category permission, check for any permission in that category
        if (!str_contains($permission->value, '_') && $permission !== AdminPermission::ALL) {
            $admin = self::where('vid', $vid)->first();
            
            if (!$admin || !$admin->permissions) {
                return false;
            }
            
            foreach ($admin->permissions as $adminPermissionValue) {
                $adminPermission = AdminPermission::tryFrom($adminPermissionValue);
                
                if ($adminPermission && $adminPermission->category() === $permission->category()) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Check for a specific permission for a specific admin (as String)
     */
    public static function hasPermissionString(int $vid, string $permissionString): bool
    {
        $permission = AdminPermission::tryFrom($permissionString);
        return $permission ? self::hasPermission($vid, $permission) : false;
    }

    /**
     * Check for a specific permission or category access for a specific admin (as String)
     */
    public static function hasPermissionStringOrCategoryAccess(int $vid, string $permissionString): bool
    {
        $permission = AdminPermission::tryFrom($permissionString);
        return $permission ? self::hasPermissionOrCategoryAccess($vid, $permission) : false;
    }

    /**
     * Check if an admin has a specific permission (as AdminPermission)
     */
    public function can(AdminPermission $permission): bool
    {
        return self::hasPermission($this->vid, $permission);
    }

    /**
     * Check if an admin has a permission or category access (as AdminPermission)
     */
    public function canOrCategoryAccess(AdminPermission $permission): bool
    {
        return self::hasPermissionOrCategoryAccess($this->vid, $permission);
    }
    
    /**
     * Check if an admin has a specific permission (as String)
     */
    public function canString(string $permissionString): bool
    {
        return self::hasPermissionString($this->vid, $permissionString);
    }

    /**
     * Check if an admin has a permission or category access (as String)
     */
    public function canStringOrCategoryAccess(string $permissionString): bool
    {
        return self::hasPermissionStringOrCategoryAccess($this->vid, $permissionString);
    }

    /**
     * Get all permissions that the admin has in a specific category
     */
    public function getPermissionsInCategory(string $category): array
    {
        if (!$this->permissions) {
            return [];
        }

        $categoryPermissions = [];
        
        foreach ($this->permissions as $permissionValue) {
            $permission = AdminPermission::tryFrom($permissionValue);
            if ($permission && $permission->category() === $category) {
                $categoryPermissions[] = $permission;
            }
        }

        return $categoryPermissions;
    }

    /**
     * Check if admin has any permissions in a specific category
     */
    public function hasAnyPermissionInCategoryName(string $category): bool
    {
        return !empty($this->getPermissionsInCategory($category));
    }
    
    /**
     * Get all permissions
     */
    public function getPermissionEnums(): array
    {
        if (!$this->permissions) {
            return [];
        }
        
        return array_filter(
            array_map(
                fn($perm) => AdminPermission::tryFrom($perm),
                $this->permissions
            )
        );
    }
    
    /**
     * Add a permission
     */
    public function addPermission(AdminPermission $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        if (!in_array($permission->value, $permissions)) {
            $permissions[] = $permission->value;
            $this->permissions = $permissions;
            $this->save();
        }
    }
    
    /**
     * Remove a permission
     */
    public function removePermission(AdminPermission $permission): void
    {
        $permissions = $this->permissions ?? [];
        
        $permissions = array_filter($permissions, fn($perm) => $perm !== $permission->value);
        
        $this->permissions = array_values($permissions);
        $this->save();
    }
}