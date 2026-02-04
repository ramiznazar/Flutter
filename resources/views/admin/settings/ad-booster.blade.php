@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Ad Booster Settings</h4>
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
                <h4 class="mt-0 header-title">Watch Ad for Booster</h4>
                <p class="text-muted mb-4 font-14">
                    Users can watch an ad to get a speed booster (e.g. 3x for 1 hour). Total boosters per day and cooldown between claims are configurable. After first booster is used, an 8-hour (or custom) cooldown applies before the next claim.
                </p>

                <form action="{{ route('admin.ad-booster.update') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="enabled" name="enabled" value="1" {{ old('enabled', $currentSettings['enabled']) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enabled">Enable Ad Booster</label>
                        </div>
                        <small class="form-text text-muted">When enabled, users can watch an ad to claim a speed booster.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Cooldown (Hours)</label>
                        <input type="number" step="0.1" class="form-control" name="cooldown_hours" required min="0" max="168" value="{{ old('cooldown_hours', $currentSettings['cooldown_hours']) }}" />
                        <small class="form-text text-muted">Hours user must wait after claiming one booster before claiming the next (e.g. 8 = 8 hours).</small>
                        @error('cooldown_hours')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Booster Duration (Hours)</label>
                        <input type="number" step="0.1" class="form-control" name="duration_hours" required min="0.1" max="168" value="{{ old('duration_hours', $currentSettings['duration_hours']) }}" />
                        <small class="form-text text-muted">How long the booster lasts (e.g. 1 = 1 hour).</small>
                        @error('duration_hours')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Booster Speed</label>
                        <select class="form-control" name="booster_type" required>
                            <option value="2x" {{ old('booster_type', $currentSettings['booster_type']) === '2x' ? 'selected' : '' }}>2x</option>
                            <option value="3x" {{ old('booster_type', $currentSettings['booster_type']) === '3x' ? 'selected' : '' }}>3x</option>
                            <option value="5x" {{ old('booster_type', $currentSettings['booster_type']) === '5x' ? 'selected' : '' }}>5x</option>
                        </select>
                        <small class="form-text text-muted">Mining speed multiplier (e.g. 3x for 1 hour).</small>
                        @error('booster_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Max Boosters Per Day</label>
                        <input type="number" class="form-control" name="max_per_day" required min="1" max="10" value="{{ old('max_per_day', $currentSettings['max_per_day']) }}" />
                        <small class="form-text text-muted">Maximum number of ad boosters a user can claim per day (e.g. 3).</small>
                        @error('max_per_day')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - Ad Booster Settings')
