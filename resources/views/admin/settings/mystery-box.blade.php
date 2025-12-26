@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Mystery Box Settings</h4>
        </div>
    </div>
</div>

@foreach(['common', 'rare', 'epic', 'legendary'] as $boxType)
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">{{ ucfirst($boxType) }} Mystery Box Settings</h4>
                <p class="text-muted mb-4 font-14">Configure {{ $boxType }} mystery box rewards and cooldowns.</p>

                <form action="{{ route('admin.mystery-box.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="box_type" value="{{ $boxType }}" />
                    
                    <div class="form-group mb-3">
                        <label class="mb-2">Cooldown (Minutes)</label>
                        <input type="number" class="form-control" name="cooldown" required placeholder="Enter cooldown in minutes" min="0" value="{{ old('cooldown', $boxSettings[$boxType]['cooldown']) }}" />
                        <small class="form-text text-muted">Time between ad watches in minutes.</small>
                        @error('cooldown')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Ads Required</label>
                        <input type="number" class="form-control" name="ads_required" required placeholder="Enter number of ads required" min="1" value="{{ old('ads_required', $boxSettings[$boxType]['ads']) }}" />
                        <small class="form-text text-muted">Number of ads user must watch to open the box.</small>
                        @error('ads_required')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Minimum Coins</label>
                        <input type="number" step="0.01" class="form-control" name="min_coins" required placeholder="Enter minimum coins" min="0" value="{{ old('min_coins', $boxSettings[$boxType]['min_coins']) }}" />
                        <small class="form-text text-muted">Minimum reward coins.</small>
                        @error('min_coins')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Maximum Coins</label>
                        <input type="number" step="0.01" class="form-control" name="max_coins" required placeholder="Enter maximum coins" min="0" value="{{ old('max_coins', $boxSettings[$boxType]['max_coins']) }}" />
                        <small class="form-text text-muted">Maximum reward coins.</small>
                        @error('max_coins')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Update {{ ucfirst($boxType) }} Settings
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('pageTitle', 'Crutox Admin - Mystery Box Settings')
