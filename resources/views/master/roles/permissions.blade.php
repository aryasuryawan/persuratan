@extends('layouts.app')

@section('page-title')
    Permissions: {{ $role->label }}
@endsection

@section('page-header')
@endsection

@section('page-actions')
<a href="{{ route('master.roles.index') }}" class="btn btn-secondary">Kembali ke Role</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('master.roles.permissions.update', $role) }}">
            @csrf @method('PUT')
            <div class="mb-3">
                <label class="form-label">Permissions</label>
                @foreach($permissionsByGroup as $group => $permissions)
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small mb-2">{{ $group }}</h6>
                        <div class="row">
                            @foreach($permissions as $permission)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission['id'] }}" id="perm_{{ $permission['id'] }}" {{ in_array($permission['id'], $rolePermissions) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="perm_{{ $permission['id'] }}">
                                            {{ $permission['label'] }}
                                            @if($permission['description'])
                                                <span class="text-muted ms-1" title="{{ $permission['description'] }}">(?)</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-footer">
                <button type="submit" class="btn btn-primary">Simpan Permission</button>
                <a href="{{ route('master.roles.index') }}" class="btn btn-link link-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection