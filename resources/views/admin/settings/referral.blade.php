@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Referral Rewards Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Referral Rewards Configuration</h4>
                <p class="text-muted mb-4 font-14">Configure referral rewards, bonuses, and limits.</p>

                <form action="{{ route('admin.referral-settings.update') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">Referrer Reward</label>
                        <input type="number" class="form-control" name="referrer_reward" required placeholder="Enter referrer reward" min="0" value="{{ old('referrer_reward', $currentSettings['referrer_reward']) }}" />
                        <small class="form-text text-muted">Reward given to the person who refers.</small>
                        @error('referrer_reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Referee Reward</label>
                        <input type="number" class="form-control" name="referee_reward" required placeholder="Enter referee reward" min="0" value="{{ old('referee_reward', $currentSettings['referee_reward']) }}" />
                        <small class="form-text text-muted">Reward given to the person who is referred.</small>
                        @error('referee_reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Maximum Referrals</label>
                        <input type="number" class="form-control" name="max_referrals" required placeholder="Enter maximum referrals" min="0" value="{{ old('max_referrals', $currentSettings['max_referrals']) }}" />
                        <small class="form-text text-muted">Maximum number of referrals allowed per user.</small>
                        @error('max_referrals')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Bonus Reward</label>
                        <input type="number" class="form-control" name="bonus_reward" required placeholder="Enter bonus reward" min="0" value="{{ old('bonus_reward', $currentSettings['bonus_reward']) }}" />
                        <small class="form-text text-muted">Bonus reward for reaching referral milestones.</small>
                        @error('bonus_reward')
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
@endsection

@section('pageTitle', 'Crutox Admin - Referral Settings')
