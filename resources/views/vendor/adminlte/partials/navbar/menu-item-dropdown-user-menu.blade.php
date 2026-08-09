<li class="nav-item" style="position:relative;">

    <a href="#" class="nav-link" id="nhcUserBtn" onclick="nhcToggleMenu(event)">
        <i class="fas fa-user-circle fa-lg"></i>
        <span class="d-none d-md-inline ml-1">{{ Auth::user()->name }}</span>
        <i class="fas fa-caret-down ml-1"></i>
    </a>

    <ul id="nhcUserMenu" style="
        display: none;
        position: fixed;
        top: 57px;
        right: 16px;
        z-index: 99999;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        min-width: 200px;
        padding: 4px 0;
        list-style: none;
        margin: 0;
    ">
        <li>
            <a href="{{ route('profile.edit') }}" style="display:block; padding:10px 16px; color:#444; text-decoration:none;">
                <i class="fas fa-cog mr-2"></i> Profile Settings
            </a>
        </li>
        <li style="border-top:1px solid #eee;"></li>
        <li>
            <a href="#" onclick="event.preventDefault(); document.getElementById('nhc-logout-form').submit();"
               style="display:block; padding:10px 16px; color:#e53935; text-decoration:none;">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </a>
        </li>
    </ul>

    <form id="nhc-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>

</li>

<script>
function nhcToggleMenu(e) {
    e.preventDefault();
    e.stopPropagation();
    var m = document.getElementById('nhcUserMenu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    var btn = document.getElementById('nhcUserBtn');
    var m = document.getElementById('nhcUserMenu');
    if (m && btn && !btn.contains(e.target)) m.style.display = 'none';
});
</script>
