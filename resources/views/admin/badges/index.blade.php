@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Badges Management</h4>
        </div>
    </div>
</div>

@if(session('message'))
<div class="alert alert-{{ session('messageType', 'info') }} alert-dismissible fade show" role="alert">
    {{ session('message') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Add New Badge</h4>
                <p class="text-muted mb-4 font-14">
                    Create a new badge. Set only ONE requirement type per badge (mining sessions, spins, invites, wallet balance, or social media tasks).
                </p>

                <form action="{{ route('admin.badges.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">Badge Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="badge_name" required placeholder="e.g., Mining Novice" value="{{ old('badge_name') }}" />
                        @error('badge_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Badge Icon</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Upload Icon File</label>
                                <input type="file" class="form-control" name="icon_file" accept="image/*" />
                                <small class="form-text text-muted">Upload PNG, JPG, GIF, SVG, or WEBP (max 2MB)</small>
                            </div>
                            <div class="col-md-6">
                                <label>Or Enter Icon URL</label>
                                <input type="url" class="form-control" name="badges_icon" placeholder="https://crutox.com/badges/badge.png" value="{{ old('badges_icon') }}" />
                                <small class="form-text text-muted">Enter full URL to badge image</small>
                            </div>
                        </div>
                        @error('icon_file')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        @error('badges_icon')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="mb-2">Mining Sessions Required</label>
                                <input type="number" class="form-control" name="mining_sessions_required" placeholder="e.g., 30" min="0" value="{{ old('mining_sessions_required') }}" />
                                <small class="form-text text-muted">Leave empty if not required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="mb-2">Spin Wheel Required</label>
                                <input type="number" class="form-control" name="spin_wheel_required" placeholder="e.g., 60" min="0" value="{{ old('spin_wheel_required') }}" />
                                <small class="form-text text-muted">Leave empty if not required</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="mb-2">Invite Friends Required</label>
                                <input type="number" class="form-control" name="invite_friends_required" placeholder="e.g., 5" min="0" value="{{ old('invite_friends_required') }}" />
                                <small class="form-text text-muted">Leave empty if not required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="mb-2">Crutox in Wallet Required</label>
                                <input type="number" step="0.01" class="form-control" name="crutox_in_wallet_required" placeholder="e.g., 10" min="0" value="{{ old('crutox_in_wallet_required') }}" />
                                <small class="form-text text-muted">Leave empty if not required</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="social_media_task_completed" name="social_media_task_completed" value="1" {{ old('social_media_task_completed') ? 'checked' : '' }} />
                            <label class="custom-control-label" for="social_media_task_completed">Social Media Tasks Completed (All tasks)</label>
                        </div>
                        <small class="form-text text-muted">Check if badge requires all social media tasks to be completed</small>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Create Badge
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
                <h4 class="mt-0 header-title">All Badges</h4>
                <p class="text-muted mb-4 font-14">Manage existing badges. Click edit to update badge details.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Badge Name</th>
                                <th>Icon</th>
                                <th>Requirements</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($badges as $badge)
                            <tr>
                                <td>{{ $badge->id }}</td>
                                <td>{{ $badge->badge_name }}</td>
                                <td>
                                    @if($badge->badges_icon)
                                        <img src="{{ $badge->badges_icon }}" alt="Badge Icon" style="max-width: 50px; max-height: 50px; object-fit: contain;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                                        <span style="display:none; color: #999;">No Image</span>
                                    @else
                                        <span class="text-muted">No Icon</span>
                                    @endif
                                </td>
                                <td>
                                    @if($badge->mining_sessions_required)
                                        <span class="badge badge-info">Mining: {{ $badge->mining_sessions_required }}</span>
                                    @elseif($badge->spin_wheel_required)
                                        <span class="badge badge-warning">Spins: {{ $badge->spin_wheel_required }}</span>
                                    @elseif($badge->invite_friends_required)
                                        <span class="badge badge-success">Invites: {{ $badge->invite_friends_required }}</span>
                                    @elseif($badge->crutox_in_wallet_required)
                                        <span class="badge badge-primary">Wallet: {{ $badge->crutox_in_wallet_required }}</span>
                                    @elseif($badge->social_media_task_completed)
                                        <span class="badge badge-purple">All Social Tasks</span>
                                    @else
                                        <span class="badge badge-secondary">Account Created</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary waves-effect waves-light" data-toggle="modal" data-target="#editBadgeModal{{ $badge->id }}">
                                        <i class="mdi mdi-pencil"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.badges.destroy', $badge->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this badge?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger waves-effect waves-light">
                                            <i class="mdi mdi-delete"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editBadgeModal{{ $badge->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Badge: {{ $badge->badge_name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('admin.badges.update', $badge->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Badge Name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="badge_name" required value="{{ $badge->badge_name }}" />
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="mb-2">Current Icon</label>
                                                    @if($badge->badges_icon)
                                                        <div class="mb-2">
                                                            <img src="{{ $badge->badges_icon }}" alt="Badge Icon" style="max-width: 100px; max-height: 100px; object-fit: contain;" onerror="this.style.display='none';">
                                                        </div>
                                                    @endif
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Upload New Icon File</label>
                                                            <input type="file" class="form-control" name="icon_file" accept="image/*" />
                                                            <small class="form-text text-muted">Leave empty to keep current icon</small>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Or Update Icon URL</label>
                                                            <input type="url" class="form-control" name="badges_icon" placeholder="https://crutox.com/badges/badge.png" value="{{ $badge->badges_icon }}" />
                                                            <small class="form-text text-muted">Enter full URL to badge image</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Mining Sessions Required</label>
                                                            <input type="number" class="form-control" name="mining_sessions_required" placeholder="e.g., 30" min="0" value="{{ $badge->mining_sessions_required }}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Spin Wheel Required</label>
                                                            <input type="number" class="form-control" name="spin_wheel_required" placeholder="e.g., 60" min="0" value="{{ $badge->spin_wheel_required }}" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Invite Friends Required</label>
                                                            <input type="number" class="form-control" name="invite_friends_required" placeholder="e.g., 5" min="0" value="{{ $badge->invite_friends_required }}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label class="mb-2">Crutox in Wallet Required</label>
                                                            <input type="number" step="0.01" class="form-control" name="crutox_in_wallet_required" placeholder="e.g., 10" min="0" value="{{ $badge->crutox_in_wallet_required }}" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input" id="social_media_task_completed_edit{{ $badge->id }}" name="social_media_task_completed" value="1" {{ $badge->social_media_task_completed ? 'checked' : '' }} />
                                                        <label class="custom-control-label" for="social_media_task_completed_edit{{ $badge->id }}">Social Media Tasks Completed (All tasks)</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Badge</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No badges found. Create your first badge above.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - Badges Management')
