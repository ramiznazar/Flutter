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
                    @if($boxType === 'legendary')
                        Configure {{ $boxType }} mystery box. <strong>Legendary boxes reward boosters (2x, 3x, 5x) instead of coins.</strong>
                    @else
                        Configure {{ $boxType }} mystery box rewards and cooldowns.
                    @endif
                </p>

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

                    @if($boxType === 'legendary')
                        {{-- Legendary Box: Booster Configuration --}}
                        <div class="form-group mb-3">
                            <label class="mb-2">Reward Type</label>
                            <select class="form-control" name="reward_type" id="legendary_reward_type" required onchange="toggleLegendaryRewardFields()">
                                <option value="booster" {{ old('reward_type', $boxSettings[$boxType]['reward_type'] ?? 'booster') === 'booster' ? 'selected' : '' }}>Booster (Recommended)</option>
                                <option value="coins" {{ old('reward_type', $boxSettings[$boxType]['reward_type'] ?? 'booster') === 'coins' ? 'selected' : '' }}>Coins</option>
                            </select>
                            <small class="form-text text-muted">Legendary boxes should reward boosters (2x, 3x, 5x) for better user experience.</small>
                            @error('reward_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="legendary_booster_fields">
                            <div class="form-group mb-3">
                                <label class="mb-2">Available Booster Types</label>
                                <input type="text" class="form-control" name="booster_types" required placeholder="2x,3x,5x" value="{{ old('booster_types', $boxSettings[$boxType]['booster_types'] ?? '2x,3x,5x') }}" />
                                <small class="form-text text-muted">Comma-separated list of booster types (e.g., 2x,3x,5x). One will be randomly selected when box is opened.</small>
                                @error('booster_types')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="mb-2">Booster Duration (Hours)</label>
                                <input type="number" step="0.1" class="form-control" name="booster_duration" required placeholder="10" min="0.1" max="168" value="{{ old('booster_duration', $boxSettings[$boxType]['booster_duration'] ?? 10.00) }}" />
                                <small class="form-text text-muted">How long the booster will last (default: 10 hours). Maximum: 168 hours (7 days).</small>
                                @error('booster_duration')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div id="legendary_coins_fields" style="display: none;">
                            <div class="form-group mb-3">
                                <label class="mb-2">Minimum Coins</label>
                                <input type="number" step="0.01" class="form-control" name="min_coins" placeholder="Enter minimum coins" min="0" value="{{ old('min_coins', $boxSettings[$boxType]['min_coins'] ?? 50.00) }}" />
                                <small class="form-text text-muted">Minimum reward coins (only used if reward type is set to coins).</small>
                                @error('min_coins')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label class="mb-2">Maximum Coins</label>
                                <input type="number" step="0.01" class="form-control" name="max_coins" placeholder="Enter maximum coins" min="0" value="{{ old('max_coins', $boxSettings[$boxType]['max_coins'] ?? 200.00) }}" />
                                <small class="form-text text-muted">Maximum reward coins (only used if reward type is set to coins).</small>
                                @error('max_coins')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @else
                        {{-- Other Boxes: Coins Configuration --}}
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
                    @endif

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
function toggleLegendaryRewardFields() {
    const rewardType = document.getElementById('legendary_reward_type').value;
    const boosterFields = document.getElementById('legendary_booster_fields');
    const coinsFields = document.getElementById('legendary_coins_fields');
    
    if (rewardType === 'booster') {
        boosterFields.style.display = 'block';
        coinsFields.style.display = 'none';
        // Make booster fields required
        boosterFields.querySelector('input[name="booster_types"]').setAttribute('required', 'required');
        boosterFields.querySelector('input[name="booster_duration"]').setAttribute('required', 'required');
        // Remove required from coins fields
        coinsFields.querySelector('input[name="min_coins"]').removeAttribute('required');
        coinsFields.querySelector('input[name="max_coins"]').removeAttribute('required');
    } else {
        boosterFields.style.display = 'none';
        coinsFields.style.display = 'block';
        // Remove required from booster fields
        boosterFields.querySelector('input[name="booster_types"]').removeAttribute('required');
        boosterFields.querySelector('input[name="booster_duration"]').removeAttribute('required');
        // Make coins fields required
        coinsFields.querySelector('input[name="min_coins"]').setAttribute('required', 'required');
        coinsFields.querySelector('input[name="max_coins"]').setAttribute('required', 'required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleLegendaryRewardFields();
});
</script>
@endpush
@endsection

@section('pageTitle', 'Crutox Admin - Mystery Box Settings')
