
@extends('layouts.app')

@section('page-title', 'Edit Department')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏢 Edit Department: {{ $department->name }}</h4>
        <div>
            <a href="{{ route('admin.departments.show', $department) }}" class="btn btn-outline-info me-2">
                <i class="fas fa-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Departments
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $department->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $department->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="council_id" class="form-label">Council <span class="text-danger">*</span></label>
                            <select class="form-select @error('council_id') is-invalid @enderror" 
                                    id="council_id" name="council_id" required>
                                <option value="">Select Council</option>
                                @foreach($councils as $council)
                                    <option value="{{ $council->id }}" {{ old('council_id', $department->council_id) == $council->id ? 'selected' : '' }}>
                                        {{ $council->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('council_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Modules Access</label>
                            <div class="row">
                                @foreach($availableModules as $module => $label)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="modules_access[]" value="{{ $module }}" 
                                                   id="module_{{ $module }}"
                                                   {{ in_array($module, old('modules_access', $department->modules_access ?? [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="module_{{ $module }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Department
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Department Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Created:</strong> {{ $department->created_at->format('M d, Y H:i') }}</p>
                    <p><strong>Last Updated:</strong> {{ $department->updated_at->format('M d, Y H:i') }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                            {{ $department->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>

            @if($department->users_count > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">⚠️ Warning</h6>
                </div>
                <div class="card-body">
                    <p class="text-warning mb-0">
                        This department has {{ $department->users_count }} associated users. 
                        Deactivating this department may affect user access.
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
