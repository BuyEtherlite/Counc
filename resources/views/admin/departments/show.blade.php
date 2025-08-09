
@extends('layouts.app')

@section('page-title', 'Department Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏢 {{ $department->name }}</h4>
        <div>
            <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Departments
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Department Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Name:</strong></div>
                        <div class="col-md-9">{{ $department->name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Description:</strong></div>
                        <div class="col-md-9">{{ $department->description ?? 'No description provided' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Council:</strong></div>
                        <div class="col-md-9">{{ $department->council->name ?? 'N/A' }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Status:</strong></div>
                        <div class="col-md-9">
                            <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Created:</strong></div>
                        <div class="col-md-9">{{ $department->created_at->format('M d, Y H:i') }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Last Updated:</strong></div>
                        <div class="col-md-9">{{ $department->updated_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
            </div>

            @if($department->modules_access && count($department->modules_access) > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Module Access</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($department->modules_access as $module)
                            <div class="col-md-6 mb-2">
                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $module)) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($department->offices->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Associated Offices ({{ $department->offices->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($department->offices as $office)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $office->name }}</h6>
                                    <small class="text-muted">{{ $office->address }}</small>
                                </div>
                                <span class="badge bg-{{ $office->is_active ? 'success' : 'secondary' }}">
                                    {{ $office->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="h4 mb-0">{{ $department->users->count() }}</div>
                                <small class="text-muted">Users</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0">{{ $department->offices->count() }}</div>
                            <small class="text-muted">Offices</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit Department
                        </a>
                        
                        @if($department->users->count() == 0 && $department->offices->count() == 0)
                        <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                    onclick="return confirm('Are you sure you want to delete this department?')">
                                <i class="fas fa-trash me-1"></i>Delete Department
                            </button>
                        </form>
                        @else
                        <button class="btn btn-outline-secondary btn-sm w-100" disabled>
                            <i class="fas fa-info-circle me-1"></i>Cannot Delete (Has Dependencies)
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
