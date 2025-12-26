@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Profile</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">My Profile</h4>
                <p class="text-muted mb-4 font-14">Update your profile information and password.</p>

                @if(session('message'))
                    <div class="alert alert-{{ session('messageType', 'success') }} alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required value="{{ old('username', $admin->username ?? '') }}">
                        @error('username')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="name">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="{{ old('name', $admin->name ?? '') }}">
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="{{ old('email', $admin->email ?? '') }}">
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label>Account Information</label>
                        <div class="form-control" style="height: auto; background-color: #f8f9fa;">
                            <small class="text-muted">
                                <strong>Created:</strong> 
                                @if(isset($admin->created_at) && $admin->created_at)
                                    {{ \Carbon\Carbon::parse($admin->created_at)->format('F j, Y, g:i a') }}
                                @else
                                    N/A
                                @endif
                                <br>
                                <strong>Last Login:</strong> 
                                @if(isset($admin->last_login) && $admin->last_login)
                                    {{ \Carbon\Carbon::parse($admin->last_login)->format('F j, Y, g:i a') }}
                                @else
                                    Never
                                @endif
                            </small>
                        </div>
                    </div>

                    <hr>

                    <h5>Change Password</h5>
                    <p class="text-muted">Leave blank if you don't want to change your password.</p>

                    <div class="form-group mb-3">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password">
                        @error('current_password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password (min 6 characters)">
                        @error('new_password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        @error('confirm_password')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            <i class="mdi mdi-content-save"></i> Update Profile
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary waves-effect waves-light">
                            <i class="mdi mdi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - Profile')















