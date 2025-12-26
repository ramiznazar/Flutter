@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Users & Coins Management</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Give Crutox Coins to User</h4>
                <p class="text-muted mb-4 font-14">Add or remove Crutox coins from users.</p>

                <form action="{{ route('admin.users.give-coins') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">User ID / Username / Email</label>
                        <input type="text" class="form-control" name="user_identifier" required placeholder="Enter user ID, username, or email" value="{{ old('user_identifier') }}" />
                        @error('user_identifier')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Amount (Coins)</label>
                        <input type="number" step="0.01" class="form-control" name="coin_amount" required placeholder="Enter coin amount" value="{{ old('coin_amount') }}" />
                        <small class="form-text text-muted">Use positive number to add, negative to remove coins.</small>
                        @error('coin_amount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Give Coins
                        </button>
                        <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                            Reset
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Give Booster to User</h4>
                <p class="text-muted mb-4 font-14">Assign mining boosters to users. Boosters multiply mining speed.</p>

                <form action="{{ route('admin.users.give-booster') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">User ID / Username / Email</label>
                        <input type="text" class="form-control" name="booster_user_identifier" required placeholder="Enter user ID, username, or email" value="{{ old('booster_user_identifier') }}" />
                        @error('booster_user_identifier')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Booster Type</label>
                        <select class="form-control" name="booster_type" required>
                            <option value="2x">2x Booster (Double Mining Speed)</option>
                            <option value="3x">3x Booster (Triple Mining Speed)</option>
                            <option value="5x">5x Booster (5x Mining Speed)</option>
                        </select>
                        <small class="form-text text-muted">Select the booster multiplier.</small>
                        @error('booster_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Duration (Hours)</label>
                        <input type="number" step="0.1" class="form-control" name="booster_duration" required placeholder="Enter duration in hours" min="0.1" max="24" value="{{ old('booster_duration', '1') }}" />
                        <small class="form-text text-muted">Duration must be between 0.1 and 24 hours.</small>
                        @error('booster_duration')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-success waves-effect waves-light">
                            Give Booster
                        </button>
                        <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                            Reset
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Reset Mystery Box Data</h4>
                <p class="text-muted mb-4 font-14">Reset mystery box clicks, ads watched, and progress for users.</p>

                <form action="{{ route('admin.users.reset-mystery-box') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset mystery box data? This action cannot be undone.');">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">User ID / Username / Email</label>
                        <input type="text" class="form-control" name="mystery_box_user_identifier" required placeholder="Enter user ID, username, or email" value="{{ old('mystery_box_user_identifier') }}" />
                        @error('mystery_box_user_identifier')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Box Type</label>
                        <select class="form-control" name="mystery_box_type" required>
                            <option value="all">All Box Types</option>
                            <option value="common">Common Box</option>
                            <option value="rare">Rare Box</option>
                            <option value="epic">Epic Box</option>
                            <option value="legendary">Legendary Box</option>
                        </select>
                        <small class="form-text text-muted">Select which box type to reset, or all types.</small>
                        @error('mystery_box_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-warning waves-effect waves-light">
                            Reset Mystery Box
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

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Search Users</h4>
                <p class="text-muted mb-4 font-14">Search and view user details.</p>

                <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Search by ID, email, username, or name" value="{{ $search }}" />
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Search</button>
                                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User ID</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Coins</th>
                                <th>Active Booster</th>
                                <th>Mystery Box Data</th>
                                <th>Join Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $index => $user)
                            <tr>
                                <th scope="row">{{ $index + 1 + (($page - 1) * $perPage) }}</th>
                                <td>USR{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username ?: 'N/A' }}</td>
                                <td>{{ number_format((float)$user->coin, 2) }}</td>
                                <td>
                                    @if($user->active_booster)
                                        <span class="badge badge-success">
                                            {{ $user->active_booster->booster_type }}
                                        </span><br>
                                        <small class="text-muted">
                                            Expires: {{ \Carbon\Carbon::parse($user->active_booster->expires_at)->format('Y-m-d H:i') }}
                                        </small>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->mystery_box_data && $user->mystery_box_data->count() > 0)
                                        <div style="max-width: 300px;">
                                            @foreach($user->mystery_box_data as $mbData)
                                                <div class="mb-2 p-2 border rounded" style="background-color: #f8f9fa;">
                                                    <strong>{{ ucfirst($mbData['box_type']) }}:</strong><br>
                                                    <small>
                                                        Clicks: {{ $mbData['clicks'] }} | 
                                                        Ads: {{ $mbData['ads_watched'] }}/{{ $mbData['ads_required'] }}<br>
                                                        @if($mbData['box_opened'])
                                                            <span class="badge badge-success">Opened</span>
                                                            @if($mbData['reward_coins'])
                                                                ({{ number_format($mbData['reward_coins'], 2) }} coins)
                                                            @endif
                                                        @else
                                                            <span class="badge badge-warning">Not Opened</span>
                                                        @endif
                                                        @if($mbData['last_clicked_at'])
                                                            <br><small class="text-muted">Last Click: {{ \Carbon\Carbon::parse($mbData['last_clicked_at'])->format('Y-m-d H:i') }}</small>
                                                        @endif
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No data</span>
                                    @endif
                                </td>
                                <td>{{ $user->join_date }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($total > $perPage)
                <div class="mt-3">
                    <nav>
                        <ul class="pagination justify-content-center">
                            @for($i = 1; $i <= ceil($total / $perPage); $i++)
                            <li class="page-item {{ $i == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ route('admin.users.index', ['page' => $i, 'search' => $search]) }}">{{ $i }}</a>
                            </li>
                            @endfor
                        </ul>
                    </nav>
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - Users Management')
