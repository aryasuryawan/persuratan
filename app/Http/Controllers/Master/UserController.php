<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Direktorat;
use App\Models\Divisi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role', 'direktorat', 'divisi', 'departemen');

        if ($q = $request->get('q')) {
            $query->where(function ($qry) use ($q) {
                $qry->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($roleId = $request->get('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($direktoratId = $request->get('direktorat_id')) {
            $query->where('direktorat_id', $direktoratId);
        }

        if (($isActive = $request->get('is_active')) !== null && $isActive !== '') {
            $query->where('is_active', $isActive);
        }

        $users = $query->latest()->paginate($request->get('per_page', 25));

        $roles = Role::all();
        $direktorats = Direktorat::where('aktif', true)->get();

        return view('master.users.index', compact('users', 'roles', 'direktorats'));
    }

    public function search(Request $request)
    {
        $q = $request->query('q');

        $users = User::with('role')
            ->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->where('is_active', true)
            ->limit(10)
            ->get();

        return response()->json($users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_label' => $u->role?->label,
            ];
        }));
    }

    public function create()
    {
        $roles = Role::all();
        $direktorats = Direktorat::where('aktif', true)->pluck('nama', 'id');
        $divisis = Divisi::with('direktorat')->whereHas('direktorat', fn($q) => $q->where('aktif', true))->get();
        $departemens = Departemen::with('divisi.direktorat')->whereHas('divisi.direktorat', fn($q) => $q->where('aktif', true))->get();
        return view('master.users.create', compact('roles', 'direktorats', 'divisis', 'departemens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'direktorat_id' => 'required|exists:direktorats,id',
            'divisi_id' => 'nullable|exists:divisis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['divisi_id'])) {
            unset($data['divisi_id']);
        }
        if (empty($data['departemen_id'])) {
            unset($data['departemen_id']);
        }

        User::create($data);

        return redirect()->route('master.users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $direktorats = Direktorat::where('aktif', true)->pluck('nama', 'id');
        $divisis = Divisi::with('direktorat')->whereHas('direktorat', fn($q) => $q->where('aktif', true))->get();
        $departemens = Departemen::with('divisi.direktorat')->whereHas('divisi.direktorat', fn($q) => $q->where('aktif', true))->get();
        return view('master.users.edit', compact('user', 'roles', 'direktorats', 'divisis', 'departemens'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'direktorat_id' => 'required|exists:direktorats,id',
            'divisi_id' => 'nullable|exists:divisis,id',
            'departemen_id' => 'nullable|exists:departemens,id',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['divisi_id'])) {
            unset($data['divisi_id']);
        }
        if (empty($data['departemen_id'])) {
            unset($data['departemen_id']);
        }

        $user->update($data);

        return redirect()->route('master.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        abort_unless(request()->user()->isAdmin(), 403, 'Hanya administrator yang dapat menonaktifkan user.');

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('master.users.index')
            ->with('success', 'User dinonaktifkan.');
    }
}