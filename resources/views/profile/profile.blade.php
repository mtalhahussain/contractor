@extends('adminlte::page')

@section('title', 'Profile Settings')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Profile Settings</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Profile Information Card -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-circle mr-2"></i>Profile Information
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <!-- Name Field -->
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" 
                                name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Email Field -->
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>

                        @if (session('status') === 'profile-updated')
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <i class="fas fa-check-circle mr-2"></i>Profile updated successfully!
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lock mr-2"></i>Change Password
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="form-group">
                            <label for="update_password_current_password">Current Password</label>
                            <input type="password" id="update_password_current_password" 
                                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                name="current_password" required>
                            @error('current_password', 'updatePassword')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="form-group">
                            <label for="update_password_password">New Password</label>
                            <input type="password" id="update_password_password" 
                                class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                name="password" required>
                            @error('password', 'updatePassword')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label for="update_password_password_confirmation">Confirm Password</label>
                            <input type="password" id="update_password_password_confirmation" 
                                class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                                name="password_confirmation" required>
                            @error('password_confirmation', 'updatePassword')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key mr-2"></i>Update Password
                            </button>
                        </div>

                        @if (session('status') === 'password-updated')
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <i class="fas fa-check-circle mr-2"></i>Password updated successfully!
                                <button type="button" class="close" data-dismiss="alert">
                                    <span>&times;</span>
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Card -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trash-alt mr-2"></i>Delete Account
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Once your account is deleted, there is no going back. Please be certain.</p>
                    <button class="btn btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
                        <i class="fas fa-times-circle mr-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you absolutely sure you want to delete your account? This action cannot be undone.</p>
                <form id="deleteForm" method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                            name="password" placeholder="Enter your password" required>
                        @error('password', 'userDeletion')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('deleteForm').submit();">
                    <i class="fas fa-check mr-2"></i>Delete Account
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .card {
        border-top: 3px solid;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .card-primary {
        border-top-color: var(--nhc-primary, #2f80ed);
    }
    
    .card-warning {
        border-top-color: var(--nhc-warning, #b45309);
    }
    
    .card-danger {
        border-top-color: var(--nhc-danger, #dc2626);
    }
    
    .form-group label {
        font-weight: 600;
        color: var(--nhc-text-strong, #444050);
    }
    
    .form-control {
        border: 1px solid #dbdae3;
        border-radius: 6px;
    }
    
    .form-control:focus {
        border-color: var(--nhc-primary, #2f80ed);
        box-shadow: 0 0 0 0.2rem rgba(47, 128, 237, 0.25);
    }
    
    .btn {
        border-radius: 6px;
        font-weight: 600;
    }
    
    .space-y-6 > * + * {
        margin-top: 1.5rem;
    }
</style>
@stop
