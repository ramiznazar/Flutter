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
                <p class="text-muted mb-4 font-14">
                    Configure {{ $boxType }} mystery box. Choose reward type: <strong>Booster</strong> (2x, 3x, 5x with configurable duration) or <strong>Coins</strong>. Uncheck "Show in app" to hide this box from the API.
                </p>

                <form action="{{ route('admin.mystery-box.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="box_type" value="{{ $boxType }}" />

                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="enabled_{{ $boxType }}"
                                   name="enabled"
                                   value="1"
                                   {{ old('enabled', $boxSettings[$boxType]['enabled'] ?? 1) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="enabled_{{ $boxType }}">
                                Show in app (include in API)
                            </label>
                        </div>
                        <small class="form-text text-muted">When unchecked, this box type is hidden from the app and not sent in the API.</small>
                    </div>
                    
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

                    {{-- Reward Type: Booster or Coins (all box types) --}}
                    <div class="form-group mb-3">
                        <label class="mb-2">Reward Type</label>
                        <select class="form-control" name="reward_type" id="{{ $boxType }}_reward_type" required onchange="toggleRewardFields('{{ $boxType }}')">
                            <option value="booster" {{ old('reward_type', $boxSettings[$boxType]['reward_type'] ?? 'booster') === 'booster' ? 'selected' : '' }}>Booster</option>
                            <option value="coins" {{ old('reward_type', $boxSettings[$boxType]['reward_type'] ?? 'booster') === 'coins' ? 'selected' : '' }}>Coins</option>
                        </select>
                        <small class="form-text text-muted">Booster: user gets a random multiplier (e.g. 2x, 3x, 5x) for a set duration. Coins: random coin reward.</small>
                        @error('reward_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div id="{{ $boxType }}_booster_fields" class="reward-fields" style="{{ ($boxSettings[$boxType]['reward_type'] ?? 'booster') === 'coins' ? 'display:none' : '' }}">
                        <div class="form-group mb-3">
                            <label class="mb-2">Available Booster Types</label>
                            <input type="text" class="form-control" name="booster_types" placeholder="2x,3x,5x" value="{{ old('booster_types', $boxSettings[$boxType]['booster_types'] ?? '2x,3x,5x') }}" />
                            <small class="form-text text-muted">Comma-separated (e.g. 2x,3x,5x). One is randomly selected when box is opened.</small>
                            @error('booster_types')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="mb-2">Booster Duration (Hours)</label>
                            <input type="number" step="0.1" class="form-control" name="booster_duration" placeholder="10" min="0.1" max="168" value="{{ old('booster_duration', $boxSettings[$boxType]['booster_duration'] ?? 10.00) }}" />
                            <small class="form-text text-muted">How long the booster lasts (max 168 hours = 7 days).</small>
                            @error('booster_duration')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="{{ $boxType }}_coins_fields" class="reward-fields" style="{{ ($boxSettings[$boxType]['reward_type'] ?? 'booster') === 'coins' ? '' : 'display:none' }}">
                        <div class="form-group mb-3">
                            <label class="mb-2">Minimum Coins</label>
                            <input type="number" step="0.01" class="form-control" name="min_coins" placeholder="Min coins" min="0" value="{{ old('min_coins', $boxSettings[$boxType]['min_coins']) }}" />
                            <small class="form-text text-muted">Minimum reward coins (when reward type is Coins).</small>
                            @error('min_coins')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="mb-2">Maximum Coins</label>
                            <input type="number" step="0.01" class="form-control" name="max_coins" placeholder="Max coins" min="0" value="{{ old('max_coins', $boxSettings[$boxType]['max_coins']) }}" />
                            <small class="form-text text-muted">Maximum reward coins (when reward type is Coins).</small>
                            @error('max_coins')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
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

@push('scripts')
<script>
function toggleRewardFields(boxType) {
    var sel = document.getElementById(boxType + '_reward_type');
    var boosterEl = document.getElementById(boxType + '_booster_fields');
    var coinsEl = document.getElementById(boxType + '_coins_fields');
    if (!sel || !boosterEl || !coinsEl) return;
    var isBooster = sel.value === 'booster';
    boosterEl.style.display = isBooster ? 'block' : 'none';
    coinsEl.style.display = isBooster ? 'none' : 'block';
    var bt = boosterEl.querySelector('input[name="booster_types"]');
    var bd = boosterEl.querySelector('input[name="booster_duration"]');
    var mn = coinsEl.querySelector('input[name="min_coins"]');
    var mx = coinsEl.querySelector('input[name="max_coins"]');
    if (bt) bt.required = isBooster;
    if (bd) bd.required = isBooster;
    if (mn) mn.required = !isBooster;
    if (mx) mx.required = !isBooster;
}
document.addEventListener('DOMContentLoaded', function() {
    ['common', 'rare', 'epic', 'legendary'].forEach(function(boxType) {
        toggleRewardFields(boxType);
    });
});
</script>
@endpush
@endsection

@section('pageTitle', 'Crutox Admin - Mystery Box Settings')
