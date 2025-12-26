@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Giveaway Management</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Add/Edit Giveaway</h4>
                <p class="text-muted mb-4 font-14">Manage giveaways with redirected links.</p>

                <form action="{{ route('admin.giveaway.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="giveaway_id" value="{{ $editGiveaway ? $editGiveaway->id : '' }}" />
                    
                    <div class="form-group mb-3">
                        <label class="mb-2">Giveaway Title</label>
                        <input type="text" class="form-control" name="giveaway_title" required placeholder="Enter giveaway title" value="{{ $editGiveaway ? old('giveaway_title', $editGiveaway->title) : old('giveaway_title') }}" />
                        @error('giveaway_title')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Description</label>
                        <textarea class="form-control" name="giveaway_description" rows="3" placeholder="Enter giveaway description">{{ $editGiveaway ? old('giveaway_description', $editGiveaway->description) : old('giveaway_description') }}</textarea>
                        @error('giveaway_description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Reward (Optional)</label>
                        <input type="number" step="0.01" class="form-control" name="reward" placeholder="Enter reward amount" min="0" value="{{ $editGiveaway ? old('reward', $editGiveaway->reward) : old('reward') }}" />
                        @error('reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Redirect Link (URL)</label>
                        <input type="url" class="form-control" name="redirect_link" required placeholder="https://example.com" value="{{ $editGiveaway ? old('redirect_link', $editGiveaway->link) : old('redirect_link') }}" />
                        @error('redirect_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Icon URL (Optional)</label>
                        <input type="url" class="form-control" name="icon" placeholder="https://img.icons8.com/color/48/000000/gift.png" value="{{ $editGiveaway ? old('icon', $editGiveaway->icon) : old('icon') }}" />
                        @error('icon')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Start Date (Optional)</label>
                        <input type="datetime-local" class="form-control" name="start_date" value="{{ $editGiveaway && $editGiveaway->start_date ? \Carbon\Carbon::parse($editGiveaway->start_date)->format('Y-m-d\TH:i') : old('start_date') }}" />
                        @error('start_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">End Date (Optional)</label>
                        <input type="datetime-local" class="form-control" name="end_date" value="{{ $editGiveaway && $editGiveaway->end_date ? \Carbon\Carbon::parse($editGiveaway->end_date)->format('Y-m-d\TH:i') : old('end_date') }}" />
                        @error('end_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Status</label>
                        <select class="form-control" name="status">
                            <option value="active" {{ ($editGiveaway && $editGiveaway->status === 'active') || old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($editGiveaway && $editGiveaway->status === 'inactive') || old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            {{ $editGiveaway ? 'Update Giveaway' : 'Save Giveaway' }}
                        </button>
                        <a href="{{ route('admin.giveaway.index') }}" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
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
                <h4 class="mt-0 header-title">Giveaways List</h4>
                <p class="text-muted mb-4 font-14">All giveaways with their redirect links.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Reward</th>
                                <th>Redirect Link</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($giveaways as $index => $giveaway)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $giveaway->title }}</td>
                                <td>{{ Str::limit($giveaway->description ?? '', 50) }}</td>
                                <td>{{ $giveaway->reward ? number_format($giveaway->reward, 2) : 'N/A' }}</td>
                                <td><a href="{{ $giveaway->link }}" target="_blank">View Link</a></td>
                                <td><span class="badge badge-{{ $giveaway->status === 'active' ? 'success' : 'secondary' }}">{{ $giveaway->status ?? 'active' }}</span></td>
                                <td>{{ $giveaway->start_date ? \Carbon\Carbon::parse($giveaway->start_date)->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td>{{ $giveaway->end_date ? \Carbon\Carbon::parse($giveaway->end_date)->format('Y-m-d H:i') : 'N/A' }}</td>
                                <td>{{ $giveaway->created_at ? \Carbon\Carbon::parse($giveaway->created_at)->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('admin.giveaway.index', ['edit_id' => $giveaway->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.giveaway.destroy') }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this giveaway?');">
                                        @csrf
                                        <input type="hidden" name="giveaway_id" value="{{ $giveaway->id }}" />
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No giveaways found.</td>
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

@section('pageTitle', 'Crutox Admin - Giveaway Management')
