<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthorizationService
{
    /**
     * Check if user has permission
     */
    public function checkPermission($user, string $permission, ?string $resource = null): bool
    {
        if (!$user) {
            return false;
        }

        $cacheKey = "user_permission:{$user->id}:{$permission}" . ($resource ? ":{$resource}" : '');
        
        return Cache::remember($cacheKey, 300, function () use ($user, $permission, $resource) {
            // Check direct permissions
            if ($this->hasDirectPermission($user, $permission, $resource)) {
                return true;
            }

            // Check role permissions
            if ($this->hasRolePermission($user, $permission, $resource)) {
                return true;
            }

            return false;
        });
    }

    /**
     * Check if user has role
     */
    public function checkRole($user, string $role): bool
    {
        if (!$user) {
            return false;
        }

        $cacheKey = "user_role:{$user->id}:{$role}";
        
        return Cache::remember($cacheKey, 300, function () use ($user, $role) {
            return $user->roles()->where('name', $role)->exists();
        });
    }

    /**
     * Get user permissions
     */
    public function getUserPermissions($user): array
    {
        if (!$user) {
            return [];
        }

        $cacheKey = "user_permissions:{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            $permissions = collect();

            // Get direct permissions
            $directPermissions = $user->permissions()->get();
            $permissions = $permissions->merge($directPermissions);

            // Get role permissions
            $rolePermissions = $user->roles()->with('permissions')->get()
                ->pluck('permissions')
                ->flatten();
            $permissions = $permissions->merge($rolePermissions);

            return $permissions->unique('id')->values()->toArray();
        });
    }

    /**
     * Get user roles
     */
    public function getUserRoles($user): array
    {
        if (!$user) {
            return [];
        }

        $cacheKey = "user_roles:{$user->id}";
        
        return Cache::remember($cacheKey, 300, function () use ($user) {
            return $user->roles()->get()->toArray();
        });
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, string $roleName): void
    {
        $user = User::findOrFail($userId);
        $role = Role::where('name', $roleName)->firstOrFail();

        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
            
            Log::info('Role assigned to user', [
                'user_id' => $userId,
                'role' => $roleName
            ]);

            $this->clearUserCache($userId);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(int $userId, string $roleName): void
    {
        $user = User::findOrFail($userId);
        $role = Role::where('name', $roleName)->firstOrFail();

        $user->roles()->detach($role->id);
        
        Log::info('Role removed from user', [
            'user_id' => $userId,
            'role' => $roleName
        ]);

        $this->clearUserCache($userId);
    }

    /**
     * Assign permission to user
     */
    public function assignPermission(int $userId, string $permissionName): void
    {
        $user = User::findOrFail($userId);
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        if (!$user->permissions()->where('permission_id', $permission->id)->exists()) {
            $user->permissions()->attach($permission->id);
            
            Log::info('Permission assigned to user', [
                'user_id' => $userId,
                'permission' => $permissionName
            ]);

            $this->clearUserCache($userId);
        }
    }

    /**
     * Remove permission from user
     */
    public function removePermission(int $userId, string $permissionName): void
    {
        $user = User::findOrFail($userId);
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        $user->permissions()->detach($permission->id);
        
        Log::info('Permission removed from user', [
            'user_id' => $userId,
            'permission' => $permissionName
        ]);

        $this->clearUserCache($userId);
    }

    /**
     * Check if user has direct permission
     */
    private function hasDirectPermission($user, string $permission, ?string $resource = null): bool
    {
        $query = $user->permissions()->where('name', $permission);
        
        if ($resource) {
            $query->where('resource', $resource);
        }

        return $query->exists();
    }

    /**
     * Check if user has role permission
     */
    private function hasRolePermission($user, string $permission, ?string $resource = null): bool
    {
        $query = $user->roles()->whereHas('permissions', function ($q) use ($permission, $resource) {
            $q->where('name', $permission);
            
            if ($resource) {
                $q->where('resource', $resource);
            }
        });

        return $query->exists();
    }

    /**
     * Clear user cache
     */
    private function clearUserCache(int $userId): void
    {
        Cache::forget("user_permissions:{$userId}");
        Cache::forget("user_roles:{$userId}");
        
        // Clear permission-specific cache
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            Cache::forget("user_permission:{$userId}:{$permission->name}");
        }
        
        // Clear role-specific cache
        $roles = Role::all();
        foreach ($roles as $role) {
            Cache::forget("user_role:{$userId}:{$role->name}");
        }
    }

    /**
     * Get authorization statistics
     */
    public function getAuthorizationStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'users_with_roles' => User::whereHas('roles')->count(),
            'users_with_permissions' => User::whereHas('permissions')->count(),
            'role_assignments' => \DB::table('user_roles')->count(),
            'permission_assignments' => \DB::table('user_permissions')->count(),
            'cache_enabled' => true,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }

    /**
     * Create role
     */
    public function createRole(string $name, string $description = null): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description
        ]);

        Log::info('Role created', [
            'role_id' => $role->id,
            'name' => $name
        ]);

        return $role;
    }

    /**
     * Create permission
     */
    public function createPermission(string $name, string $description = null, ?string $resource = null): Permission
    {
        $permission = Permission::create([
            'name' => $name,
            'description' => $description,
            'resource' => $resource
        ]);

        Log::info('Permission created', [
            'permission_id' => $permission->id,
            'name' => $name,
            'resource' => $resource
        ]);

        return $permission;
    }

    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(string $roleName, string $permissionName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
            $role->permissions()->attach($permission->id);
            
            Log::info('Permission assigned to role', [
                'role' => $roleName,
                'permission' => $permissionName
            ]);
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermissionFromRole(string $roleName, string $permissionName): void
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $permission = Permission::where('name', $permissionName)->firstOrFail();

        $role->permissions()->detach($permission->id);
        
        Log::info('Permission removed from role', [
            'role' => $roleName,
            'permission' => $permissionName
        ]);
    }
}
