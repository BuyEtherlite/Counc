
@extends('layouts.app')

@section('page-title', 'Create Department')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏢 Create New Department</h4>
        <a href="{{ route('admin.departments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Departments
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.departments.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
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
                                    <option value="{{ $council->id }}" {{ old('council_id') == $council->id ? 'selected' : '' }}>
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
                                                   {{ in_array($module, old('modules_access', [])) ? 'checked' : '' }}>
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
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Department
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Information</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Departments are organizational units within a council that handle specific functions and services.
                    </p>
                    <ul class="list-unstyled small text-muted">
                        <li>• Department name should be descriptive</li>
                        <li>• Select appropriate modules for department access</li>
                        <li>• Inactive departments cannot access the system</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
