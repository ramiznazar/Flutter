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
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">KYC Settings</h4>
                <p class="text-muted mb-4">Configure KYC requirements and eligibility criteria.</p>
                
                <div class="alert alert-info">
                    <strong>Note:</strong> This page will be fully implemented. 
                    For now, use the API endpoints at <code>/api/admin/settings_manage</code> with <code>settings_type=kyc</code>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - KYC Settings')
















