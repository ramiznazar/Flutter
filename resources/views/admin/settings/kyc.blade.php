@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">KYC Settings</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">KYC Eligibility Requirements</h4>
                <p class="text-muted mb-4 font-14">Configure the requirements users must meet to be eligible for KYC submission.</p>

                <form action="{{ route('admin.kyc-settings.update') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">Mining Sessions Required</label>
                        <input type="number" class="form-control" name="kyc_mining_sessions" required placeholder="Enter mining sessions required" min="0" value="{{ old('kyc_mining_sessions', $currentSettings['kyc_mining_sessions']) }}" />
                        <small class="form-text text-muted">Minimum number of mining sessions required for KYC eligibility.</small>
                        @error('kyc_mining_sessions')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Referrals Required</label>
                        <input type="number" class="form-control" name="kyc_referrals_required" required placeholder="Enter referrals required" min="0" value="{{ old('kyc_referrals_required', $currentSettings['kyc_referrals_required']) }}" />
                        <small class="form-text text-muted">Minimum number of referrals required for KYC eligibility.</small>
                        @error('kyc_referrals_required')
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

@section('pageTitle', 'Crutox Admin - KYC Settings')















