<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private PermissionService $permissionService
    ) {}

    public function index()
    {
        $roles = Role::withCount('users')->latest()->paginate(request('per_page', 25));
        return view('master.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissionsByGroup = $this->permissionService->getPermissionsByGroup();
        return view('master.roles.create', compact('permissionsByGroup'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:roles',
            'label' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($data);
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('master.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $permissionsByGroup = $this->permissionService->getPermissionsByGroup();
        $rolePermissions = $this->permissionService->getRolePermissions($role);
        return view('master.roles.edit', compact('role', 'permissionsByGroup', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:roles,kode,' . $role->id,
            'label' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($data);
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('master.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menghapus data master.');

        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih memiliki user.');
        }

        $role->delete();

        return redirect()->route('master.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }

    public function permissions(Role $role)
    {
        $permissionsByGroup = $this->permissionService->getPermissionsByGroup();
        $rolePermissions = $this->permissionService->getRolePermissions($role);
        return view('master.roles.permissions', compact('role', 'permissionsByGroup', 'rolePermissions'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->input('permissions', []));
        $this->permissionService->clearRoleCache($role);

        return redirect()->route('master.roles.permissions', $role)
            ->with('success', 'Permission role berhasil diperbarui.');
    }
}