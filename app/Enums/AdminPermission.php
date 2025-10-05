<?php

namespace App\Enums;

/**
 * AdminPermission Enum
 * 
 * This enum defines the permission system for administrators in the application.
 * It follows a hierarchical structure with global and granular permissions.
 * 
 * PERMISSION HIERARCHY:
 * - Super Admin (*): Has all permissions
 * - Global permissions (e.g., 'polls'): Grants all related granular permissions
 * - Granular permissions (e.g., 'polls_create'): Specific actions within a category
 * 
 * NAMING CONVENTION:
 * - Global permissions: lowercase category name (e.g., 'polls', 'admins')
 * - Granular permissions: category_action format (e.g., 'polls_create', 'admins_edit_permissions')
 * - Super admin: '*' (asterisk)
 * 
 * PERMISSION LOGIC:
 * - Super admin (*) implies ALL permissions
 * - Global permission (e.g., 'polls') implies ALL granular permissions in that category
 * - Granular permissions are standalone and don't imply other permissions
 * 
 * USAGE EXAMPLES:
 * 
 * / Check if admin has specific permission
 * $admin->hasPermission(AdminPermission::POLLS_CREATE);
 * 
 * / Check using string method (recommended for UI)
 * $admin->canString('polls_create');
 * 
 * / Get all permissions for a category
 * $pollsPermissions = AdminPermission::getByCategory('Polls');
 * 
 * / Check if permission implies another
 * AdminPermission::POLLS->implies(AdminPermission::POLLS_CREATE); // true
 * AdminPermission::POLLS_CREATE->implies(AdminPermission::POLLS_UPDATE); // false
 * 
 * / Get permission metadata
 * $permission = AdminPermission::POLLS_CREATE;
 * $category = $permission->category();     // 'Polls'
 * $description = $permission->description(); // 'Create polls'
 * $icon = $permission->icon();             // 'lucide.plus-circle'
 * 
 * / Get category styling
 * $color = AdminPermission::categoryColor('Polls');           // 'primary'
 * $iconName = AdminPermission::categoryIcon('Polls');         // 'lucide.vote'
 * $cssClass = AdminPermission::categoryColorProp('Polls', 'text'); // 'text-primary'
 * 
 * UI INTEGRATION:
 * - Use permission->description() for human-readable labels
 * - Use categoryIcon() and categoryColor() for consistent UI theming
 * - Filter granular permissions with str_contains($permission->value, '_') in modals
 * - Global permissions should not be shown alongside granular ones in detailed views
 * 
 * SECURITY CONSIDERATIONS:
 * - Always use the implies() method to check permission inheritance
 * - Super admin (*) should be handled with special care in UI
 * - Global permissions are convenience shortcuts for multiple granular permissions
 * - Store permissions as arrays of string values in the database
 * 
 * DATABASE STORAGE:
 * - Store as JSON array: ["polls_create", "polls_update", "admins_edit_permissions"]
 * - Or store global permission: ["polls", "admins"] (implies all granular)
 * - Super admin: ["*"] (implies everything)
 */


enum AdminPermission: string
{
    // Super Admin
    case ALL = '*';

    // Admins - Global
    case ADMINS = 'admins';
        // Admins - Granular
        case ADMINS_EDIT_PERMISSIONS = 'admins_edit_permissions';

    // App - Global
    case APP = 'app';
        // App - Granular
        case APP_GDPR = 'app_gdpr';

    // Flight Operations - Global
    case FLTOPS = 'fltops';
        // Flight Operations - Granular
        case FLTOPS_TOURS = 'fltops_tours';
        case FLTOPS_VA = 'fltops_va';

    /**
     * Get permission category
     */
    public function category(): string
    {
        return match($this) {
            self::ALL => 'System-wide',

            // Manage admins
            self::ADMINS,
            self::ADMINS_EDIT_PERMISSIONS => 'Admins',

            // App
            self::APP,
            self::APP_GDPR => 'Application',

            // Flight Operations
            self::FLTOPS,
            self::FLTOPS_TOURS,
            self::FLTOPS_VA => 'Flight Operations',
        };
    }

    /**
     * Get permission description
     */
    public function description(): string
    {
        return match($this) {
            self::ALL => 'Super Administrator (All permissions)',

            // Manage admins
            self::ADMINS => 'Manage administrators',
            self::ADMINS_EDIT_PERMISSIONS => 'Manage admins',

            // App
            self::APP => 'Manage application',
            self::APP_GDPR => 'Handle GDPR compliance',

            // Flight Operations
            self::FLTOPS => 'Manage Flight Operations',
            self::FLTOPS_TOURS => 'Manage Tours',
            self::FLTOPS_VA => 'Manage Virtual Airlines',
        };
    }

    /**
     * Get route for specific permission
     * Returns the route name associated with each granular permission
     */
    public function route(): ?string
    {
        return match($this) {
            // Admins permissions
            self::ADMINS_EDIT_PERMISSIONS => route('admin.manage'),
            
            // Application permissions
            self::APP_GDPR => route('admin.app.gdpr'),

            // Flight 
            self::FLTOPS_TOURS => route('admin.flight-ops.tours'),
            self::FLTOPS_VA => route('admin.flight-ops.virtual-airlines'),
            
            // Global and system-wide permissions have no direct route
            default => null
        };
    }

    /**
     * Get icon for this permission
     */
    public function icon(): string
    {
        return match($this) {
            self::ALL => 'phosphor.crown',
            self::ADMINS_EDIT_PERMISSIONS => 'phosphor.users',
            self::APP_GDPR => 'phosphor.biohazard',
            self::FLTOPS_TOURS => 'phosphor.globe-hemisphere-west',
            self::FLTOPS_VA => 'phosphor.airplane-takeoff',
            default => 'phosphor.check-circle'
        };
    }

    /**
     * Get icon for category
     */
    public static function categoryIcon(string $category): string
    {
        return match($category) {
            'Admins' => 'phosphor.users',
            'Application' => 'phosphor.wrench',
            'Flight Operations' => 'phosphor.airplane-tilt',
            default => 'phosphor.circle'
        };
    }

    /**
     * Get color for category
     */
    public static function categoryColor(string $category): string
    {
        return match($category) {
            'Admins' => 'accent',
            'Application' => 'secondary',
            'Flight Operations' => 'primary',
            default => 'base-300'
        };
    }

    /**
     * Get management route for category
     */
    public static function categoryRoute(string $category): ?string
    {
        return match($category) {
            'Admins' => route('admin.manage'),
            'Application' => route('admin.app.gdpr'),
            'Flight Operations' => route('admin.flight-ops'),
            default => null
        };
    }

    /**
     * Get all permissions as array
     */
    public static function getAllPermissions(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Get permissions by category
     */
    public static function getByCategory(string $category): array
    {
        return array_filter(
            self::cases(),
            fn($permission) => $permission->category() === $category
        );
    }

    /**
     * Check if permission implies another permission
     */
    public function implies(AdminPermission $permission): bool
    {
        // Super admin has all permissions
        if ($this === self::ALL) {
            return true;
        }
        
        // Same permission
        if ($this === $permission) {
            return true;
        }
        
        // DRY: using granular permissions
        // Convention : 'polls' implies 'polls_*', 'admins' implies 'admins_*', etc.
        if (!str_contains($this->value, '_') && str_starts_with($permission->value, $this->value . '_')) {
            return true;
        }
        
        return false;
    }

    /**
     * Get CSS class with color for category
     */
    public static function categoryColorProp(string $category, string $property): string
    {
        $color = self::categoryColor($category);
        return "{$property}-{$color}";
    }
}