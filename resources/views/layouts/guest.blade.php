<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Contractor') }} — Login</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * { font-family: 'Inter', sans-serif; }

            .brand-panel {
                background: #1e3a5f;
                position: relative;
                overflow: hidden;
            }

            .brand-panel::before {
                content: '';
                position: absolute;
                top: -30%;
                left: -20%;
                width: 70%;
                height: 70%;
                background: transparent;
                border-radius: 50%;
            }

            .brand-panel::after {
                content: '';
                position: absolute;
                bottom: -20%;
                right: -10%;
                width: 55%;
                height: 55%;
                background: transparent;
                border-radius: 50%;
            }

            .brand-logo-ring {
                background: #eee;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.12);
                box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            }

            .feature-item {
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.1);
                backdrop-filter: blur(6px);
                transition: background 0.2s;
            }
            .feature-item:hover { background: rgba(255,255,255,0.1); }

            .auth-card {
                background: #ffffff;
            }

            .auth-shell {
                display: flex;
                width: 100%;
                min-height: 100vh;
            }

            .brand-panel-content {
                position: relative;
                z-index: 2;
                text-align: center;
                max-width: 420px;
            }

            .auth-card-wrap {
                width: 100%;
                max-width: 480px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 3rem 2.5rem;
            }

            .auth-content {
                width: 100%;
                max-width: 360px;
            }

            .mobile-login-logo {
                display: none;
            }

            .mobile-login-logo img {
                width: auto;
                max-width: 180px;
                max-height: 72px;
                object-fit: contain;
                object-position: center;
            }

            .input-field {
                border: 1.5px solid #e2e8f0;
                border-radius: 10px;
                padding: 11px 14px;
                width: 100%;
                box-sizing: border-box;
                font-size: 0.92rem;
                color: #1e293b;
                transition: border-color 0.2s, box-shadow 0.2s;
                background: #f8fafc;
                outline: none;
            }
            .input-field:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
                background: #ffffff;
            }
            .input-field::placeholder { color: #94a3b8; }

            .btn-login {
                background: #2563eb;
                color: #ffffff;
                border: none;
                border-radius: 10px;
                padding: 12px 24px;
                font-size: 0.95rem;
                font-weight: 600;
                cursor: pointer;
                width: 100%;
                letter-spacing: 0.3px;
                transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
                box-shadow: 0 4px 14px rgba(37,99,235,0.35);
            }
            .btn-login:hover {
                background: #1d4ed8;
                transform: translateY(-1px);
                box-shadow: 0 6px 20px rgba(37,99,235,0.45);
            }
            .btn-login:active { transform: translateY(0); }

            .divider-line {
                border: none;
                border-top: 1px solid #e9edf2;
                margin: 24px 0;
            }

            .remember-checkbox {
                accent-color: #2563eb;
                width: 15px;
                height: 15px;
                cursor: pointer;
            }

            @keyframes fadeSlideIn {
                from { opacity: 0; transform: translateY(18px); }
                to   { opacity: 1; transform: translateY(0); }
            }
            .animate-in { animation: fadeSlideIn 0.5s ease forwards; }

            @media (max-width: 1024px) {
                .brand-panel {
                    padding: 2.25rem 1.5rem;
                }

                .auth-card-wrap {
                    max-width: 460px;
                    padding: 2.5rem 1.5rem;
                }
            }

            @media (max-width: 767px) {
                .brand-panel { display: none !important; }

                .auth-shell {
                    flex-direction: column;
                }

                .auth-card {
                    border-radius: 0;
                    min-height: 100vh;
                    width: 100%;
                }

                .auth-card-wrap {
                    width: 100%;
                    max-width: 100%;
                    min-height: 100vh;
                    justify-content: center;
                    padding: 2rem 1.25rem;
                    box-sizing: border-box;
                }

                .auth-content {
                    width: 100%;
                    max-width: 100%;
                    box-sizing: border-box;
                }

                .input-field {
                    width: 100%;
                    box-sizing: border-box;
                    font-size: 1rem;
                    padding: 13px 14px;
                }

                .login-form {
                    width: 100%;
                }

                .remember-row {
                    flex-wrap: wrap;
                    gap: 10px;
                }

                .btn-login {
                    padding: 14px 24px;
                    font-size: 1rem;
                }

                .mobile-login-logo {
                    display: flex;
                    width: 100%;
                    justify-content: center;
                    margin: 0 0 1.5rem;
                }

                .divider-line {
                    margin: 18px 0;
                }
            }

            @media (max-width: 400px) {
                .auth-card-wrap {
                    padding: 1.5rem 1rem;
                }

                .input-field {
                    font-size: 0.95rem;
                }
            }
        </style>
    </head>
    <body class="antialiased" style="background:#1e3a5f; min-height:100vh; display:flex; align-items:stretch;">

        <div class="auth-shell">

            {{-- ── LEFT BRAND PANEL ── --}}
            <div class="brand-panel" style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:3rem 2.5rem; position:relative; z-index:1;">
                <div class="brand-panel-content">

                    {{-- Logo --}}
                    <div class="brand-logo-ring" style="display:inline-flex; align-items:center; justify-content:center; border-radius:28px; margin-bottom:2rem;">
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }} Logo"
                             style="object-fit:contain; object-position:center; filter:drop-shadow(0 4px 12px rgba(0,0,0,0.4));">
                    </div>

                  
                    <p style="color:#93c5fd; font-size:1rem; font-weight:400; margin-bottom:2.5rem; line-height:1.6;">
                        Complete contractor management &amp; tracking system
                    </p>

                    <hr style="border:none; border-top:1px solid rgba(255,255,255,0.12); margin:0 auto 2.5rem; width:60px;">

                    {{-- Feature highlights --}}
                    <div style="display:flex; flex-direction:column; gap:12px; text-align:left;">
                        @foreach([
                            ['icon'=>'⛽','text'=>'Diesel usage &amp; rate tracking'],
                            ['icon'=>'⚙️','text'=>'Machine hours &amp; rate management'],
                            ['icon'=>'💰','text'=>'Payments &amp; expense monitoring'],
                            ['icon'=>'📊','text'=>'Reports with Excel &amp; PDF export'],
                        ] as $f)
                        <div class="feature-item" style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:10px;">
                            <span style="font-size:1.15rem; line-height:1;">{{ $f['icon'] }}</span>
                            <span style="color:#cbd5e1; font-size:0.875rem; font-weight:500;">{!! $f['text'] !!}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer credit --}}
                <p style="position:absolute; bottom:1.5rem; color:rgba(148,163,184,0.6); font-size:0.75rem; z-index:2;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>

            {{-- ── RIGHT AUTH PANEL ── --}}
            <div class="auth-card auth-card-wrap">
                <div class="mobile-login-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }} Logo">
                </div>
                <div class="animate-in auth-content">
                    {{ $slot }}
                </div>
            </div>

        </div>
    </body>
</html>
