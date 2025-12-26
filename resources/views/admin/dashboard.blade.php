@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="btn-group float-right">
                <ol class="breadcrumb hide-phone p-0 m-0">
                    <li class="breadcrumb-item"><a href="#">Crutox</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
            <h4 class="page-title">Dashboard</h4>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
<!-- end page title end breadcrumb -->

<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-row">
                    <div class="col-3 align-self-center">
                        <div class="round">
                            <i class="mdi mdi-account-multiple"></i>
                        </div>
                    </div>
                    <div class="col-9 align-self-center text-right">
                        <div class="m-l-10">
                            <h5 class="mt-0">{{ number_format($currentSettings['current_users']) }}</h5>
                            <p class="mb-0 text-muted">Display Users (Fake)</p>
                            <small class="text-muted">Real: {{ number_format($currentSettings['real_users']) }}</small>
                        </div>
                    </div>                                                                                          
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-row">
                    <div class="col-3 align-self-center">
                        <div class="round">
                            <i class="mdi mdi-target"></i>
                        </div>
                    </div>
                    <div class="col-9 align-self-center text-right">
                        <div class="m-l-10">
                            <h5 class="mt-0">{{ number_format($currentSettings['goal_users']) }}</h5>
                            <p class="mb-0 text-muted">Goal Users</p>
                        </div>
                    </div>                                                                                          
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-row">
                    <div class="col-3 align-self-center">
                        <div class="round">
                            <i class="mdi mdi-percent"></i>
                        </div>
                    </div>
                    <div class="col-9 align-self-center text-right">
                        <div class="m-l-10">
                            <h5 class="mt-0">{{ number_format($progressPercent, 1) }}%</h5>
                            <p class="mb-0 text-muted">Progress</p>
                        </div>
                    </div>                                                                                          
                </div>
                <div class="progress mt-3" style="height:3px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%;" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">User Count Management</h4>
                <p class="text-muted mb-4 font-14">Manually set the display user count and goal. This will be shown on the app. You can set any number you want (e.g., 99,000/1,000,000) regardless of actual registered users.</p>

                <form action="{{ route('admin.dashboard.update') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">Real Users (Actual Registered)</label>
                        <input type="text" class="form-control" readonly value="{{ number_format($currentSettings['real_users']) }}" />
                        <small class="form-text text-muted">This is the actual number of registered users in the database. This value is read-only and automatically calculated.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Display Users (Fake/Manual)</label>
                        <input type="number" class="form-control" name="current_users" required placeholder="Enter display user count" min="0" value="{{ $currentSettings['current_users'] }}" />
                        <small class="form-text text-muted">Manually set the user count to display in the app (e.g., 99,000). This can be different from real registered users.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Goal Users</label>
                        <input type="number" class="form-control" name="goal_users" required placeholder="Enter goal user count" min="1" value="{{ $currentSettings['goal_users'] }}" />
                        <small class="form-text text-muted">Target number of users to reach (e.g., 1,000,000).</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Progress Display</label>
                        <div class="alert alert-info">
                            <strong>Display Progress (Fake):</strong> 
                            {{ number_format($currentSettings['current_users']) }} / {{ number_format($currentSettings['goal_users']) }} users ({{ number_format($progressPercent, 1) }}%)
                            <br>
                            <strong>Real Users:</strong> {{ number_format($currentSettings['real_users']) }} registered users
                        </div>
                        <div class="progress" style="height:25px;">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progressPercent }}%;" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($progressPercent, 1) }}%
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Update User Count
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

@section('pageTitle', 'Crutox Admin - Dashboard')



















