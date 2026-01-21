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
                                <th>Didit Status</th>
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
                                <td>
                                    @if($kyc['didit_status'])
                                        <span class="badge badge-{{ $kyc['didit_status'] === 'APPROVED' ? 'success' : ($kyc['didit_status'] === 'DECLINED' ? 'danger' : 'warning') }}">
                                            {{ $kyc['didit_status'] }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($kyc['created_at'])->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.kyc.index', ['edit_id' => $kyc['id']]) }}" class="btn btn-sm btn-primary">View/Edit</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No KYC submissions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(isset($total) && $total > $perPage)
                <div class="mt-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            @php
                                $totalPages = ceil($total / $perPage);
                                $currentPage = $page;
                                $showPages = 5; // Number of page numbers to show around current page
                                
                                // Calculate start and end page numbers
                                $startPage = max(1, $currentPage - floor($showPages / 2));
                                $endPage = min($totalPages, $startPage + $showPages - 1);
                                
                                // Adjust start if we're near the end
                                if ($endPage - $startPage < $showPages - 1) {
                                    $startPage = max(1, $endPage - $showPages + 1);
                                }
                            @endphp

                            {{-- Previous Button --}}
                            <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ route('admin.kyc.index', ['page' => $currentPage - 1, 'edit_id' => request('edit_id')]) }}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                    <span class="sr-only">Previous</span>
                                </a>
                            </li>

                            {{-- First Page --}}
                            @if($startPage > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ route('admin.kyc.index', ['page' => 1, 'edit_id' => request('edit_id')]) }}">1</a>
                                </li>
                                @if($startPage > 2)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                            @endif

                            {{-- Page Numbers --}}
                            @for($i = $startPage; $i <= $endPage; $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="{{ route('admin.kyc.index', ['page' => $i, 'edit_id' => request('edit_id')]) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            {{-- Last Page --}}
                            @if($endPage < $totalPages)
                                @if($endPage < $totalPages - 1)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ route('admin.kyc.index', ['page' => $totalPages, 'edit_id' => request('edit_id')]) }}">{{ $totalPages }}</a>
                                </li>
                            @endif

                            {{-- Next Button --}}
                            <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ route('admin.kyc.index', ['page' => $currentPage + 1, 'edit_id' => request('edit_id')]) }}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                    <span class="sr-only">Next</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                @endif

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
                        @if($editKYC->didit_status)
                            <div class="alert alert-info mb-2" style="font-size: 12px;">
                                <i class="mdi mdi-information"></i> 
                                <strong>Auto-synced:</strong> Status automatically follows Didit status. 
                                You can manually override it below if needed.
                            </div>
                        @endif
                        <select class="form-control" name="status" required>
                            <option value="pending" {{ $editKYC->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $editKYC->status === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $editKYC->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    @if($editKYC->didit_request_id)
                    <div class="form-group">
                        <label>Didit Verification Status</label>
                        <div class="alert alert-info">
                            <strong>Didit Request ID:</strong> {{ $editKYC->didit_request_id }}<br>
                            <strong>Didit Status:</strong> 
                            <span class="badge badge-{{ $editKYC->didit_status === 'APPROVED' ? 'success' : ($editKYC->didit_status === 'DECLINED' ? 'danger' : 'warning') }}">
                                {{ $editKYC->didit_status ?? 'Pending' }}
                            </span><br>
                            @if($editKYC->didit_verified_at)
                            <strong>Verified At:</strong> {{ \Carbon\Carbon::parse($editKYC->didit_verified_at)->format('Y-m-d H:i:s') }}
                            @endif
                        </div>
                        @if($editKYC->didit_verification_data)
                        <details class="mt-2">
                            <summary class="btn btn-sm btn-secondary">View Didit Verification Data</summary>
                            <pre class="mt-2 p-2 bg-light" style="max-height: 200px; overflow-y: auto;">{{ json_encode(json_decode($editKYC->didit_verification_data), JSON_PRETTY_PRINT) }}</pre>
                        </details>
                        @endif
                    </div>
                    @endif
                    
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
