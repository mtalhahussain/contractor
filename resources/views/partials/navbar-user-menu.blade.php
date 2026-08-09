{{-- Simple User Dropdown Menu --}}
<li class="nav-item dropdown user-menu">
    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <i class="fas fa-user-circle"></i>
        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
    </a>

    {{-- Simple 2-item dropdown menu --}}
    <ul class="dropdown-menu dropdown-menu-right">
        {{-- Profile --}}
        <li>
            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                <i class="fas fa-cog mr-2"></i> Profile Settings
            </a>
        </li>

        {{-- Logout --}}
        <li>
            <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </li>
    </ul>

    {{-- Logout form (hidden) --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</li>
