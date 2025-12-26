@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="btn-group float-right">
                <ol class="breadcrumb hide-phone p-0 m-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Crutox</a></li>
                    <li class="breadcrumb-item active">News Management</li>
                </ol>
            </div>
            <h4 class="page-title">News Management</h4>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Add/Edit News</h4>
                <p class="text-muted mb-4 font-14">Manage news articles with redirected links. These will be displayed in the app.</p>

                <form action="{{ route('admin.news.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="save" />
                    <input type="hidden" name="news_id" value="{{ $editNews ? ($editNews->ID ?? $editNews->id) : '0' }}" />
                    
                    <div class="form-group mb-3">
                        <label class="mb-2">News Title</label>
                        <input type="text" class="form-control" name="news_title" required placeholder="Enter news title" value="{{ $editNews ? old('news_title', $editNews->Title) : old('news_title') }}" />
                        @error('news_title')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">News Content</label>
                        <textarea class="form-control" name="news_content" rows="5" required placeholder="Enter news content">{{ $editNews ? old('news_content', $editNews->Description) : old('news_content') }}</textarea>
                        @error('news_content')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Redirect Link (URL)</label>
                        <input type="url" class="form-control" name="redirect_link" required placeholder="https://example.com" value="{{ $editNews ? old('redirect_link', $editNews->Link) : old('redirect_link') }}" />
                        @error('redirect_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Image URL (Optional)</label>
                        <input type="url" class="form-control" name="image" placeholder="https://example.com/image.jpg" value="{{ $editNews ? old('image', $editNews->Image) : old('image') }}" />
                        @error('image')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="active" {{ ($editNews && $editNews->Status == 1) || old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($editNews && $editNews->Status == 0) || old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            {{ $editNews ? 'Update News' : 'Save News' }}
                        </button>
                        <a href="{{ route('admin.news.index') }}" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
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
                <h4 class="mt-0 header-title">News List</h4>
                <p class="text-muted mb-4 font-14">All news articles with their redirect links.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Redirect Link</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($newsItems as $index => $news)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $news->Title }}</td>
                                <td>{{ Str::limit($news->Description, 50) }}</td>
                                <td><a href="{{ $news->Link ?? '#' }}" target="_blank">View Link</a></td>
                                <td><span class="badge badge-{{ $news->Status == 1 ? 'success' : 'secondary' }}">{{ $news->Status == 1 ? 'active' : 'inactive' }}</span></td>
                                <td>{{ $news->CreatedAt }}</td>
                                <td>
                                    <a href="{{ route('admin.news.index', ['edit_id' => $news->ID ?? $news->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.news.destroy') }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this news?');">
                                        @csrf
                                        <input type="hidden" name="news_id" value="{{ $news->ID ?? $news->id }}" />
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No news found.</td>
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

@section('pageTitle', 'Crutox Admin - News Management')
