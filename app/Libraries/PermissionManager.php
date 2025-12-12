<?php

namespace App\Libraries;

class PermissionManager
{
    private static $rolePermissions = [
        'admin' => [
            'patients' => ['view', 'create', 'edit', 'delete', 'assign_doctor', 'view_all'],
            'appointments' => ['view', 'create', 'edit', 'delete', 'reschedule', 'view_all'],
            'staff' => ['view', 'create', 'edit', 'delete', 'manage_roles'],
            'users' => ['view', 'create', 'edit', 'delete', 'reset_password'],
            'resources' => ['view', 'create', 'edit', 'delete', 'view_all'],
            'shifts' => ['view', 'create', 'edit', 'delete'],
<<<<<<< HEAD
            'prescriptions' => ['view', 'create', 'edit', 'delete', 'view_all'], // Admin can create prescriptions
=======
            'prescriptions' => ['view', 'create', 'edit', 'delete', 'view_all'],
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
            'reports' => ['view', 'generate', 'export'],
            'system' => ['settings', 'backup', 'maintenance']
        ],
        'doctor' => [
<<<<<<< HEAD
            'patients' => ['view', 'edit', 'view_assigned'],
=======
            'patients' => ['view', 'create', 'edit', 'view_assigned'],
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
            'appointments' => ['view', 'create', 'edit', 'reschedule', 'view_own'],
            'resources' => ['view', 'view_assigned'],
            'shifts' => ['view', 'edit', 'view_own', 'edit_own'],
            'prescriptions' => ['view', 'create', 'edit', 'view_own'],
<<<<<<< HEAD
        
=======
            'reports' => ['view', 'generate_own']
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
        ],
        'receptionist' => [
            'patients' => ['view', 'create', 'edit', 'assign_doctor', 'view_all'],
            'appointments' => ['view', 'create', 'edit', 'reschedule', 'view_all'],
            'resources' => ['view'],
            'shifts' => ['view', 'view_all'],
<<<<<<< HEAD
           
        ],
        'nurse' => [
            'patients' => ['view', 'view_all'], // Nurses can view all patients to add vital signs, no edit
            'vital_signs' => ['create'], // Nurses can only add vital signs
            'resources' => ['view', 'view_all'],
            'shifts' => ['view', 'view_department'],
=======
            'reports' => ['view']
        ],
        'nurse' => [
            'patients' => ['view', 'edit', 'view_assigned'],
            'appointments' => ['view', 'view_assigned'],
            'resources' => ['view', 'view_assigned'],
            'shifts' => ['view', 'view_department'],
            'prescriptions' => ['view', 'view_assigned']
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
        ],
        'pharmacist' => [
            'prescriptions' => ['view', 'edit', 'fulfill', 'view_all'],
            'patients' => ['view'],
            'resources' => ['view', 'view_assigned']
        ],
        'accountant' => [
            'patients' => ['view'],
<<<<<<< HEAD
            'appointments' => ['view', 'view_all'], // View-only for billing purposes
=======
            'appointments' => ['view'],
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
            'billing' => ['view', 'create', 'edit', 'process'],
            'reports' => ['view', 'generate', 'export']
        ],
        'laboratorist' => [
            'patients' => ['view'],
<<<<<<< HEAD
=======
            'appointments' => ['view'],
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
            'resources' => ['view', 'view_assigned'],
            'reports' => ['view', 'generate']
        ],
        'it_staff' => [
            'patients' => ['view', 'create', 'edit', 'delete', 'assign_doctor', 'view_all'],
<<<<<<< HEAD
=======
            'appointments' => ['view', 'create', 'edit', 'delete', 'reschedule', 'view_all'],
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
            'staff' => ['view', 'create', 'edit', 'delete', 'manage_roles'],
            'users' => ['view', 'create', 'edit', 'delete', 'reset_password'],
            'resources' => ['view', 'create', 'edit', 'delete', 'view_all'],
            'shifts' => ['view', 'create', 'edit', 'delete'],
<<<<<<< HEAD
            'prescriptions' => ['view', 'edit', 'delete', 'view_all'], // Removed 'create' - IT staff should not medically prescribe
=======
            'prescriptions' => ['view', 'create', 'edit', 'delete', 'view_all'],
>>>>>>> 03d4e70 (COMMITenter the commit message for your changes. Lines starting)
            'reports' => ['view', 'generate', 'export'],
            'system' => ['settings', 'backup', 'maintenance', 'database_management']
        ]
    ];

    /**
     * Check if user has specific permission
     */
    public static function hasPermission(string $role, string $module, string $action): bool
    {
        if (!isset(self::$rolePermissions[$role])) {
            return false;
        }

        if (!isset(self::$rolePermissions[$role][$module])) {
            return false;
        }

        return in_array($action, self::$rolePermissions[$role][$module]);
    }

    /**
     * Get all permissions for a role
     */
    public static function getRolePermissions(string $role): array
    {
        return self::$rolePermissions[$role] ?? [];
    }

    /**
     * Get permissions for a specific module
     */
    public static function getModulePermissions(string $role, string $module): array
    {
        return self::$rolePermissions[$role][$module] ?? [];
    }

    /**
     * Check multiple permissions at once
     */
    public static function hasAnyPermission(string $role, string $module, array $actions): bool
    {
        foreach ($actions as $action) {
            if (self::hasPermission($role, $module, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all specified permissions
     */
    public static function hasAllPermissions(string $role, string $module, array $actions): bool
    {
        foreach ($actions as $action) {
            if (!self::hasPermission($role, $module, $action)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get user permissions formatted for frontend
     */
    public static function getUserPermissions(string $role): array
    {
        $permissions = self::getRolePermissions($role);
        $formatted = [];

        foreach ($permissions as $module => $actions) {
            $formatted[$module] = [
                'can_view' => in_array('view', $actions),
                'can_create' => in_array('create', $actions),
                'can_edit' => in_array('edit', $actions),
                'can_delete' => in_array('delete', $actions),
                'can_assign' => in_array('assign_doctor', $actions),
                'can_reschedule' => in_array('reschedule', $actions),
                'view_scope' => self::getViewScope($actions)
            ];
        }

        return $formatted;
    }

    /**
     * Determine view scope based on permissions
     */
    private static function getViewScope(array $actions): string
    {
        if (in_array('view_all', $actions)) {
            return 'all';
        } elseif (in_array('view_assigned', $actions)) {
            return 'assigned';
        } elseif (in_array('view_own', $actions)) {
            return 'own';
        }
        return 'none';
    }
}