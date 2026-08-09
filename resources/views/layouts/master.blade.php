@extends('adminlte::master')

@section('adminlte_navbar')
<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    {{-- Sidebar toggle --}}
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

    {{-- Right navbar --}}
    <ul class="navbar-nav ml-auto">
        {{-- Simple User Dropdown --}}
        @auth
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-user-circle"></i>
                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
            </a>

            {{-- Dropdown Menu --}}
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i> Profile Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>

            {{-- Logout form --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </li>
        @endauth
    </ul>

</nav>
@stop
