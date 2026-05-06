<x-guest-layout>

    {{-- Header --}}
    <div style="margin-bottom:2rem;">
        <h2 style="font-size:1.6rem; font-weight:800; color:#0f172a; letter-spacing:-0.4px; margin-bottom:6px;">
            Welcome back
        </h2>
        <p style="font-size:0.875rem; color:#64748b; font-weight:400;">
            Sign in to your account to continue
        </p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:10px 14px; margin-bottom:18px; font-size:0.85rem; color:#166534;">
            {{ session('status') }}
        </div>
    @endif

    {{-- Validation errors summary --}}
    @if ($errors->any())
        <div style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:10px 14px; margin-bottom:18px; font-size:0.85rem; color:#991b1b;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="display:flex; flex-direction:column; gap:18px;">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px; letter-spacing:0.2px;">
                Email Address
            </label>
            <div style="position:relative;">
                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:1rem; pointer-events:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1zm13 2.383-4.708 2.825L15 11.105zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741M1 11.105l4.708-2.897L1 5.383z"/>
                    </svg>
                </span>
                <input id="email" class="input-field" type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autofocus autocomplete="username"
                       style="padding-left:40px;">
            </div>
        </div>

        {{-- Password --}}
        <div>
            <label for="password" style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px; letter-spacing:0.2px;">
                Password
            </label>
            <div style="position:relative;">
                <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:1rem; pointer-events:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2m3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2"/>
                    </svg>
                </span>
                <input id="password" class="input-field" type="password" name="password"
                       placeholder="••••••••"
                       required autocomplete="current-password"
                       style="padding-left:40px; padding-right:42px;">
                <button type="button" id="togglePassword"
                        onclick="togglePass()"
                        style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#94a3b8; padding:0; display:flex; align-items:center;">
                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Remember me & Forgot password --}}
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input id="remember_me" type="checkbox" class="remember-checkbox" name="remember">
                <span style="font-size:0.83rem; color:#64748b; font-weight:500;">Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   style="font-size:0.83rem; color:#2563eb; font-weight:500; text-decoration:none; transition:color 0.15s;"
                   onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#2563eb'">
                    Forgot password?
                </a>
            @endif
        </div>

        <hr class="divider-line">

        {{-- Submit --}}
        <button type="submit" class="btn-login">
            Sign In
        </button>

    </form>

    <script>
        function togglePass() {
            const p = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (p.type === 'password') {
                p.type = 'text';
                icon.innerHTML = '<path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7 7 0 0 0 2.79-.588M5.21 3.088A7 7 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474z"/><path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12z"/>';
            } else {
                p.type = 'password';
                icon.innerHTML = '<path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>';
            }
        }
    </script>

</x-guest-layout>
