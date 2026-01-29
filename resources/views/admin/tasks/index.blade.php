@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Tasks Management</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Daily Tasks Settings (3 Tasks)</h4>
                <p class="text-muted mb-4 font-14">
                    <strong>Daily Tasks:</strong> Manage the 3 daily tasks that reset every 24 hours. 
                    <br>• Users start a task and get a <strong>5-minute timer</strong>
                    <br>• After 5 minutes, users can claim their reward
                    <br>• Tasks reset every 24 hours automatically
                    <br>• All 3 tasks can be manually changed from this panel
                </p>

                <form action="{{ route('admin.tasks.store-daily') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label class="mb-2">Task 1 Name</label>
                        <input type="text" class="form-control" name="task1_name" required placeholder="e.g., Follow Instagram" value="{{ old('task1_name', $dailyTasks[0]->Name ?? '') }}" />
                        @error('task1_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 1 Reward (Coins)</label>
                        <input type="number" class="form-control" name="task1_reward" required placeholder="e.g., 2" min="0" value="{{ old('task1_reward', $dailyTasks[0]->Token ?? '') }}" />
                        @error('task1_reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 1 Redirect Link</label>
                        <input type="url" class="form-control" name="task1_link" required placeholder="https://instagram.com/..." value="{{ old('task1_link', $dailyTasks[0]->Link ?? '') }}" />
                        @error('task1_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 1 Icon URL (Optional)</label>
                        <input type="url" class="form-control" name="task1_icon" placeholder="https://..." value="{{ old('task1_icon', $dailyTasks[0]->Icon ?? '') }}" />
                    </div>

                    <hr>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 2 Name</label>
                        <input type="text" class="form-control" name="task2_name" required placeholder="e.g., Tweet on Twitter" value="{{ old('task2_name', $dailyTasks[1]->Name ?? '') }}" />
                        @error('task2_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 2 Reward (Coins)</label>
                        <input type="number" class="form-control" name="task2_reward" required placeholder="e.g., 2" min="0" value="{{ old('task2_reward', $dailyTasks[1]->Token ?? '') }}" />
                        @error('task2_reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 2 Redirect Link</label>
                        <input type="url" class="form-control" name="task2_link" required placeholder="https://twitter.com/..." value="{{ old('task2_link', $dailyTasks[1]->Link ?? '') }}" />
                        @error('task2_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 2 Icon URL (Optional)</label>
                        <input type="url" class="form-control" name="task2_icon" placeholder="https://..." value="{{ old('task2_icon', $dailyTasks[1]->Icon ?? '') }}" />
                    </div>

                    <hr>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 3 Name</label>
                        <input type="text" class="form-control" name="task3_name" required placeholder="e.g., Watch Ad" value="{{ old('task3_name', $dailyTasks[2]->Name ?? '') }}" />
                        @error('task3_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 3 Reward (Coins)</label>
                        <input type="number" class="form-control" name="task3_reward" required placeholder="e.g., 2" min="0" value="{{ old('task3_reward', $dailyTasks[2]->Token ?? '') }}" />
                        @error('task3_reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 3 Redirect Link</label>
                        <input type="url" class="form-control" name="task3_link" required placeholder="https://..." value="{{ old('task3_link', $dailyTasks[2]->Link ?? '') }}" />
                        @error('task3_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Task 3 Icon URL (Optional)</label>
                        <input type="url" class="form-control" name="task3_icon" placeholder="https://..." value="{{ old('task3_icon', $dailyTasks[2]->Icon ?? '') }}" />
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Next Reset Time (24 hours cycle)</label>
                        <input type="datetime-local" class="form-control" name="reset_time" required value="{{ old('reset_time', $resetTime ? \Carbon\Carbon::parse($resetTime)->format('Y-m-d\TH:i') : \Carbon\Carbon::now()->addHours(24)->format('Y-m-d\TH:i')) }}" />
                        <small class="form-text text-muted">
                            Set when daily tasks should reset next. Tasks will reset every 24 hours after this time.
                            @if($resetTime)
                                <br><strong>Current Reset Time:</strong> {{ \Carbon\Carbon::parse($resetTime)->format('Y-m-d H:i:s') }}
                                @php
                                    $nextReset = \Carbon\Carbon::parse($resetTime)->addHours(24);
                                    $now = \Carbon\Carbon::now();
                                    if ($now < $nextReset) {
                                        $hoursUntil = $now->diffInHours($nextReset);
                                        echo "<br><strong>Next Reset In:</strong> " . round($hoursUntil, 1) . " hours";
                                    }
                                @endphp
                            @endif
                        </small>
                        @error('reset_time')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            Save Daily Tasks
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
                <h4 class="mt-0 header-title">One-Time Tasks</h4>
                <p class="text-muted mb-4 font-14">
                    <strong>One-Time Tasks:</strong> Tasks that can only be completed once per user.
                    <br>• Users start a task and get a <strong>1-hour timer</strong>
                    <br>• Timer shows that system will check if task is completed
                    <br>• After 1 hour, users get reward <strong>automatically regardless of completion</strong>
                    <br>• Rewards are distributed automatically via cron job
                </p>

                @if($editTask)
                <div class="alert alert-info">
                    <strong>Editing Task:</strong> {{ $editTask->Name }}
                </div>
                @endif

                <form id="one-time-form" action="{{ $editTask ? route('admin.tasks.update-onetime') : route('admin.tasks.store-onetime') }}" method="POST">
                    @csrf
                    @if($editTask)
                        <input type="hidden" name="task_id" value="{{ $editTask->getKey() }}" />
                    @endif
                    
                    <div class="form-group mb-3">
                        <label class="mb-2">Task Name</label>
                        <input type="text" class="form-control" name="task_name" required placeholder="Enter task name" value="{{ old('task_name', $editTask ? $editTask->Name : '') }}" />
                        @error('task_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Reward (Coins)</label>
                        <input type="number" class="form-control" name="reward" required placeholder="Enter reward" min="0" value="{{ old('reward', $editTask ? $editTask->Token : '') }}" />
                        @error('reward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Redirect Link</label>
                        <input type="url" class="form-control" name="redirect_link" required placeholder="https://..." value="{{ old('redirect_link', $editTask ? $editTask->Link : '') }}" />
                        @error('redirect_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Icon URL (Optional)</label>
                        <input type="url" class="form-control" name="icon" placeholder="https://..." value="{{ old('icon', $editTask ? $editTask->Icon : '') }}" />
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Status</label>
                        <select class="form-control" name="status">
                            <option value="active" {{ ($editTask && $editTask->Status == 1) || old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($editTask && $editTask->Status == 0) || old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            {{ $editTask ? 'Update Task' : 'Create Task' }}
                        </button>
                        @if($editTask)
                            <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
                        @endif
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
                <h4 class="mt-0 header-title">One-Time Tasks List</h4>
                <p class="text-muted mb-4 font-14">All one-time tasks. Daily tasks cannot be deleted from here.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Reward</th>
                                <th>Link</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($onetimeTasks as $index => $task)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $task->Name }}</td>
                                <td>{{ $task->Token }} coins</td>
                                <td><a href="{{ $task->Link }}" target="_blank">View Link</a></td>
                                <td><span class="badge badge-{{ $task->Status == 1 ? 'success' : 'secondary' }}">{{ $task->Status == 1 ? 'active' : 'inactive' }}</span></td>
                                <td>
                                    <a href="{{ route('admin.tasks.index', ['edit_id' => $task->getKey()]) }}#one-time-form" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.tasks.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                        @csrf
                                        <input type="hidden" name="task_id" value="{{ $task->getKey() }}" />
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No one-time tasks found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">User Task Completions</h4>
                <p class="text-muted mb-4 font-14">View all user task completion records. Shows who completed which tasks and when rewards were claimed.</p>

                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Task Name</th>
                                <th>Task Type</th>
                                <th>Started At</th>
                                <th>Reward Available At</th>
                                <th>Reward Claimed</th>
                                <th>Reward Claimed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($taskCompletions as $index => $completion)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>
                                    {{ $completion->user->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $completion->user->username ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $completion->user->email ?? 'N/A' }}</td>
                                <td>{{ $completion->task->Name ?? 'Task #' . $completion->task_id }}</td>
                                <td>
                                    <span class="badge badge-{{ $completion->task_type === 'daily' ? 'primary' : 'info' }}">
                                        {{ ucfirst($completion->task_type ?? 'N/A') }}
                                    </span>
                                </td>
                                <td>
                                    @if($completion->started_at)
                                        {{ \Carbon\Carbon::parse($completion->started_at)->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($completion->reward_available_at)
                                        {{ \Carbon\Carbon::parse($completion->reward_available_at)->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($completion->reward_claimed)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-warning">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($completion->reward_claimed_at)
                                        {{ \Carbon\Carbon::parse($completion->reward_claimed_at)->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center">No task completions found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($taskCompletions->count() >= 500)
                <div class="alert alert-info mt-3">
                    <strong>Note:</strong> Showing the last 500 task completions. For more records, please filter by specific user or task.
                </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection

@section('pageTitle', 'Crutox Admin - Tasks Management')
