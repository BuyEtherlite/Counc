
@extends('layouts.app')

@section('page-title', 'Office Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏢 Office Management</h4>
        <a href="{{ route('admin.offices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Office
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="council_id" class="form-select">
                        <option value="">All Councils</option>
                        @foreach(\App\Models\Council::all() as $council)
                            <option value="{{ $council->id }}" {{ request('council_id') == $council->id ? 'selected' : '' }}>
                                {{ $council->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="department_id" class="form-select">
                        <option value="">All Departments</option>
                        @foreach(\App\Models\Department::all() as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary w-100">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($offices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Office Name</th>
                            <th>Address</th>
                            <th>Council</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($offices as $office)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2">
                                        {{ strtoupper(substr($office->name, 0, 1)) }}
                                    </div>
                                    <strong>{{ $office->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $office->address }}</td>
                            <td>{{ $office->council->name ?? 'N/A' }}</td>
                            <td>{{ $office->department->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $office->is_active ? 'success' : 'secondary' }}">
                                    {{ $office->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $office->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.offices.show', $office) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.offices.edit', $office) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.offices.destroy', $office) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('Are you sure you want to delete this office?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $offices->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>No Offices Found</h5>
                <p class="text-muted">Start by creating your first office.</p>
                <a href="{{ route('admin.offices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create First Office
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection
