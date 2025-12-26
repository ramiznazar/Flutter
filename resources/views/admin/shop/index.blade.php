@extends('admin.layout')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <h4 class="page-title">Shop Management</h4>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="mt-0 header-title">Add/Edit Shop Item</h4>
                <p class="text-muted mb-4 font-14">Manage shop items with redirected links.</p>

                <form action="{{ route('admin.shop.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $editItem ? ($editItem->ID ?? $editItem->id) : '0' }}" />
                    
                    <div class="form-group mb-3">
                        <label class="mb-2">Item Name</label>
                        <input type="text" class="form-control" name="item_name" required placeholder="Enter item name" value="{{ $editItem ? old('item_name', $editItem->Title) : old('item_name') }}" />
                        @error('item_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Description (Optional)</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter item description">{{ $editItem ? old('description', $editItem->Description) : old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Price (Optional)</label>
                        <input type="number" step="0.01" class="form-control" name="price" placeholder="Enter price" min="0" value="{{ $editItem ? old('price', $editItem->Price) : old('price') }}" />
                        @error('price')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Redirect Link (URL)</label>
                        <input type="url" class="form-control" name="redirect_link" required placeholder="https://example.com" value="{{ $editItem ? old('redirect_link', $editItem->Link) : old('redirect_link') }}" />
                        @error('redirect_link')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Item Image URL (Optional)</label>
                        <input type="url" class="form-control" name="item_image" placeholder="https://example.com/image.jpg" value="{{ $editItem ? old('item_image', $editItem->Image) : old('item_image') }}" />
                        @error('item_image')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="mb-2">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="active" {{ ($editItem && $editItem->Status == 1) || old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ ($editItem && $editItem->Status == 0) || old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                            {{ $editItem ? 'Update Shop Item' : 'Save Shop Item' }}
                        </button>
                        <a href="{{ route('admin.shop.index') }}" class="btn btn-secondary waves-effect m-l-5">Cancel</a>
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
                <h4 class="mt-0 header-title">Shop Items List</h4>
                <p class="text-muted mb-4 font-14">All shop items with their redirect links.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Redirect Link</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shopItems as $index => $item)
                            <tr>
                                <th scope="row">{{ $index + 1 }}</th>
                                <td>{{ $item->Title }}</td>
                                <td>{{ Str::limit($item->Description ?? '', 50) }}</td>
                                <td>{{ $item->Price ? number_format($item->Price, 2) : 'N/A' }}</td>
                                <td><a href="{{ $item->Link }}" target="_blank">View Link</a></td>
                                <td><span class="badge badge-{{ $item->Status == 1 ? 'success' : 'secondary' }}">{{ $item->Status == 1 ? 'active' : 'inactive' }}</span></td>
                                <td>{{ $item->CreatedAt }}</td>
                                <td>
                                    <a href="{{ route('admin.shop.index', ['edit_id' => $item->ID ?? $item->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('admin.shop.destroy') }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this shop item?');">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->ID ?? $item->id }}" />
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No shop items found.</td>
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

@section('pageTitle', 'Crutox Admin - Shop Management')
