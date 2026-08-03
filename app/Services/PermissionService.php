<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function userCan(User $user, string $permissionKode): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $this->roleCan($user->role, $permissionKode);
    }

    public function roleCan(Role $role, string $permissionKode): bool
    {
        return Cache::remember("role_permission:{$role->id}:{$permissionKode}", 3600, function () use ($role, $permissionKode) {
            return $role->hasPermission($permissionKode);
        });
    }

    public function getPermissionsByGroup(): array
    {
        return Permission::orderBy('group')->orderBy('label')->get()->groupBy('group')->toArray();
    }

    public function getAllPermissions(): array
    {
        return Permission::orderBy('group')->orderBy('label')->get()->toArray();
    }

    public function syncRolePermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
        $this->clearRoleCache($role);
    }

    public function clearRoleCache(Role $role): void
    {
        Cache::flush();
    }

    public function getRolePermissions(Role $role): array
    {
        return $role->permissions()->pluck('permissions.id')->toArray();
    }
}