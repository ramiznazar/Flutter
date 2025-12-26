@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Mining Speed Settings</h4>
        </div>
    </div>
</div>

@if(session('message') && session('messageType'))
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-{{ session('messageType') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Mining Speed Configuration</h4>
                <p class="text-muted mb-4 font-14">Configure mining speed, base rate, and maximum speed settings.</p>

                <form action="{{ route('admin.mining-settings.update') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">Mining Speed</label>
                        <input type="number" step="0.01" class="form-control" name="mining_speed" required placeholder="Enter mining speed" min="0" value="{{ old('mining_speed', $currentSettings['mining_speed']) }}" />
                        <small class="form-text text-muted">Base mining speed multiplier.</small>
                        @error('mining_speed')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Base Mining Rate</label>
                        <input type="number" step="0.01" class="form-control" name="base_rate" required placeholder="Enter base mining rate" min="0" value="{{ old('base_rate', $currentSettings['base_mining_rate']) }}" />
                        <small class="form-text text-muted">Base rate for mining calculations.</small>
                        @error('base_rate')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Maximum Mining Speed</label>
                        <input type="number" step="0.01" class="form-control" name="max_speed" required placeholder="Enter maximum mining speed" min="0" value="{{ old('max_speed', $currentSettings['max_mining_speed']) }}" />
                        <small class="form-text text-muted">Maximum allowed mining speed.</small>
                        @error('max_speed')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Update Settings
                        </button>
                        <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                            Reset
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Individual User Coin Speed Control</h4>
                <p class="text-muted mb-4 font-14">Set custom coin speed for a specific user. Leave empty to remove custom speed and use overall settings.</p>

                <form action="{{ route('admin.mining-settings.user-coin-speed') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">User Identifier</label>
                        <input type="text" class="form-control" name="user_identifier" required placeholder="Enter User ID, Email, or Username" value="{{ old('user_identifier') }}" />
                        <small class="form-text text-muted">Enter user's ID, email address, or username.</small>
                        @error('user_identifier')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Custom Coin Speed</label>
                        <input type="number" step="0.01" class="form-control" name="coin_speed" placeholder="Enter custom coin speed (leave empty to use overall settings)" min="0" value="{{ old('coin_speed') }}" />
                        <small class="form-text text-muted">Set a custom coin speed for this user. Leave empty to remove custom speed and use overall settings.</small>
                        @error('coin_speed')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Update User Coin Speed
                        </button>
                        <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                            Reset
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - Mining Settings')
