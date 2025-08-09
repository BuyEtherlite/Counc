
@extends('layouts.app')

@section('page-title', 'Department Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏢 Department Management</h4>
        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Department
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($departments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Council</th>
                            <th>Modules Access</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $department)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2">
                                        {{ strtoupper(substr($department->name, 0, 1)) }}
                                    </div>
                                    <strong>{{ $department->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $department->description ?? 'N/A' }}</td>
                            <td>{{ $department->council->name ?? 'N/A' }}</td>
                            <td>
                                @if($department->modules_access)
                                    @php
                                        $modules = is_string($department->modules_access) 
                                            ? json_decode($department->modules_access, true) 
                                            : $department->modules_access;
                                    @endphp
                                    @if($modules && count($modules) > 0)
                                        <span class="badge bg-info">{{ count($modules) }} modules</span>
                                    @else
                                        <span class="text-muted">No modules</span>
                                    @endif
                                @else
                                    <span class="text-muted">No modules</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $department->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.departments.show', $department) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('Are you sure you want to delete this department?')">
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
                {{ $departments->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5>No Departments Found</h5>
                <p class="text-muted">Start by creating your first department.</p>
                <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create First Department
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
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection
