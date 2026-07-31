<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->latest()->paginate(request('per_page', 25));
        return view('master.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('master.roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:roles',
            'label' => 'required|string|max:255',
        ]);

        Role::create($data);

        return redirect()->route('master.roles.index')
            ->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        return view('master.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:50|unique:roles,kode,' . $role->id,
            'label' => 'required|string|max:255',
        ]);

        $role->update($data);

        return redirect()->route('master.roles.index')
            ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih memiliki user.');
        }

        $role->delete();

        return redirect()->route('master.roles.index')
            ->with('success', 'Role berhasil dihapus.');
    }
}
