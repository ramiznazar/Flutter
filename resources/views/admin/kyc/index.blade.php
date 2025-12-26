@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">KYC Management</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">KYC Submissions</h4>
                <p class="text-muted mb-4 font-14">Review and manage KYC submissions from users.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>User Email</th>
                                <th>Full Name</th>
                                <th>Date of Birth</th>
                                <th>Front Image</th>
                                <th>Back Image</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kycSubmissions as $kyc)
                            <tr>
                                <td>{{ $kyc['id'] }}</td>
                                <td>{{ $kyc['user_id'] }}</td>
                                <td>{{ $kyc['user_email'] }}</td>
                                <td>{{ $kyc['full_name'] }}</td>
                                <td>{{ $kyc['dob'] }}</td>
                                <td><a href="{{ $kyc['front_image'] }}" target="_blank" class="btn btn-sm btn-info">View</a></td>
                                <td><a href="{{ $kyc['back_image'] }}" target="_blank" class="btn btn-sm btn-info">View</a></td>
                                <td><span class="badge badge-{{ $kyc['status'] === 'approved' ? 'success' : ($kyc['status'] === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($kyc['status']) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($kyc['created_at'])->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.kyc.index', ['edit_id' => $kyc['id']]) }}" class="btn btn-sm btn-primary">View/Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No KYC submissions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@if($editKYC)
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">KYC Details</h4>
                <form action="{{ route('admin.kyc.update-status') }}" method="POST">
                    @csrf
                    <input type="hidden" name="kyc_id" value="{{ $editKYC->id }}" />
                    
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" class="form-control" value="{{ $editKYC->user_id }}" readonly />
                    </div>
                    
                    <div class="form-group">
                        <label>User Email</label>
                        <input type="text" class="form-control" value="{{ $editKYC->user ? $editKYC->user->email : 'N/A' }}" readonly />
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" class="form-control" value="{{ $editKYC->full_name }}" readonly />
                    </div>
                    
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="text" class="form-control" value="{{ $editKYC->dob }}" readonly />
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Front Image</label>
                                <div><img src="{{ $editKYC->front_image }}" class="img-fluid" style="max-height: 200px; border: 1px solid #ddd; padding: 5px;" alt="Front Image" /></div>
                                <a href="{{ $editKYC->front_image }}" target="_blank" class="btn btn-sm btn-info mt-2">View Full Size</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Back Image</label>
                                <div><img src="{{ $editKYC->back_image }}" class="img-fluid" style="max-height: 200px; border: 1px solid #ddd; padding: 5px;" alt="Back Image" /></div>
                                <a href="{{ $editKYC->back_image }}" target="_blank" class="btn btn-sm btn-info mt-2">View Full Size</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control" name="status" required>
                            <option value="pending" {{ $editKYC->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $editKYC->status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $editKYC->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Admin Notes</label>
                        <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add notes about this KYC submission">{{ old('admin_notes', $editKYC->admin_notes ?? '') }}</textarea>
                        @error('admin_notes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Update Status</button>
                        <a href="{{ route('admin.kyc.index') }}" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('pageTitle', 'Crutox Admin - KYC Management')
