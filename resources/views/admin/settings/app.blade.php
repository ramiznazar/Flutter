@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">App Settings (Maintenance &amp; Update)</h4>
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
                <h4 class="mt-0 header-title">Maintenance Mode</h4>
                <p class="text-muted mb-4 font-14">
                    Control whether the app should show a maintenance message. The mobile app reads these values via the settings API.
                </p>

                <form action="{{ route('admin.app-settings.update') }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="maintenanceSwitch"
                                   name="maintenance"
                                   value="1"
                                   {{ old('maintenance', $appSettings['maintenance']) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="maintenanceSwitch">
                                Enable Maintenance Mode
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            When enabled, the app can show a maintenance message to users (depending on frontend logic).
                        </small>
                        @error('maintenance')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Maintenance Message</label>
                        <textarea class="form-control" name="maintenance_message" rows="3" placeholder="Enter maintenance message">{{ old('maintenance_message', $appSettings['maintenance_message']) }}</textarea>
                        @error('maintenance_message')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <h4 class="mt-0 header-title">Force Update</h4>
                    <p class="text-muted mb-4 font-14">
                        Configure the version and message shown when you require users to update the app.
                    </p>

                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="forceUpdateSwitch"
                                   name="force_update"
                                   value="1"
                                   {{ old('force_update', $appSettings['force_update']) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="forceUpdateSwitch">
                                Enable Force Update
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            When enabled, the app can force users to update based on the version below (depending on frontend logic).
                        </small>
                        @error('force_update')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Required App Version</label>
                        <input type="text"
                               class="form-control"
                               name="update_version"
                               placeholder="e.g. 1.2.0"
                               value="{{ old('update_version', $appSettings['update_version']) }}">
                        <small class="form-text text-muted">
                            The app compares this to its installed version to decide whether to show an update prompt.
                        </small>
                        @error('update_version')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Update Message</label>
                        <textarea class="form-control" name="update_message" rows="3" placeholder="Enter update message">{{ old('update_message', $appSettings['update_message']) }}</textarea>
                        @error('update_message')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Update Link (Store / Download URL)</label>
                        <input type="text"
                               class="form-control"
                               name="update_link"
                               placeholder="https://play.google.com/store/apps/details?id=..."
                               value="{{ old('update_link', $appSettings['update_link']) }}">
                        @error('update_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Save App Settings
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

@section('pageTitle', 'Crutox Admin - App Settings')

