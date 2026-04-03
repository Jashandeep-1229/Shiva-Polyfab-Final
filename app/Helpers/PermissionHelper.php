<?php

namespace App\Helpers;

use App\Models\MenuPermission;
use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Check if the logged-in user has permission for a specific menu and action.
     *
     * @param string $menu_key
     * @param string $action (view, add, edit)
     * @return bool
     */
    public static function check($menu_key, $action = 'view')
    {
        $user = Auth::user();
        if (!$user) return false;

        // Admin has all permissions
        if ($user->role_as == 'Admin') return true;

        // Try user-specific permission first
        $permission = MenuPermission::where('user_id', $user->id)
            ->where('menu_key', $menu_key)
            ->first();

        // Fallback to role-based permission if no specific user record (or if it exists but we want to allow role fallback - though usually user-specific implies a manual override)
        // If we want user-specific to EXCLUSIVELY override, we should only fallback if $permission is null.
        if (!$permission) {
            $userRole = strtolower(trim($user->role_as));
            $permission = MenuPermission::where('menu_key', $menu_key)
                ->whereNull('user_id')
                ->whereRaw('LOWER(TRIM(role_name)) = ?', [$userRole])
                ->first();
        }

        if (!$permission) return false;

        $column = 'can_' . $action;
        return isset($permission->$column) && $permission->$column == 1;
    }

    /**
     * Get the data access mode (all vs owned) for the logged-in user.
     *
     * @param string $menu_key
     * @return string (all, owned)
     */
    public static function accessMode($menu_key)
    {
        $user = Auth::user();
        if (!$user || $user->role_as == 'Admin') return 'all';

        // Try user-specific permission first
        $permission = MenuPermission::where('user_id', $user->id)
            ->where('menu_key', $menu_key)
            ->first();

        // Fallback to role-based permission
        if (!$permission) {
            $permission = MenuPermission::whereRaw('LOWER(TRIM(role_name)) = ?', [strtolower(trim($user->role_as))])
                ->where('menu_key', $menu_key)
                ->first();
        }

        return $permission ? $permission->data_access : 'all';
    }
}
