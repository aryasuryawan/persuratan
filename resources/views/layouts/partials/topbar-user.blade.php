<li class="nav-item dropdown">
    <a href="#" class="nav-link d-flex align-items-center lh-1" data-bs-toggle="dropdown">
        <span class="avatar avatar-sm me-2">
            {{ substr(auth()->user()->name, 0, 2) }}
        </span>
        <div class="d-none d-md-block">
            <div class="fw-medium">{{ auth()->user()->name }}</div>
            <div class="small text-muted">{{ auth()->user()->role->label }}</div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <a class="dropdown-item d-flex align-items-center" href="{{ route('profile.edit') }}">
            <span class="ti ti-user me-2"></span>
            Profil
        </a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item d-flex align-items-center">
                <span class="ti ti-logout me-2"></span>
                Logout
            </button>
        </form>
    </div>
</li>
