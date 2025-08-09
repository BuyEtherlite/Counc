
@extends('layouts.app')

@section('page-title', 'Create Office')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏢 Create New Office</h4>
        <a href="{{ route('admin.offices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Offices
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.offices.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Office Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="contact_info" class="form-label">Contact Information</label>
                            <textarea class="form-control @error('contact_info') is-invalid @enderror" 
                                      id="contact_info" name="contact_info" rows="2" 
                                      placeholder="Phone, Email, etc.">{{ old('contact_info') }}</textarea>
                            @error('contact_info')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
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
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Department</label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id">
                                        <option value="">Select Department (Optional)</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active Office
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.offices.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Office
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
                        Offices are physical locations where council services are provided to the public.
                    </p>
                    <ul class="list-unstyled small text-muted">
                        <li>• Office name should be location-specific</li>
                        <li>• Address should be complete and accurate</li>
                        <li>• Contact information helps residents reach the office</li>
                        <li>• Department assignment is optional</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
