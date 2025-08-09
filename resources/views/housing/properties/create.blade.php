
@extends('layouts.app')

@section('page-title', 'Add Property')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Property</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('housing.properties.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Property Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <option value="house" {{ old('type') === 'house' ? 'selected' : '' }}>House</option>
                                    <option value="apartment" {{ old('type') === 'apartment' ? 'selected' : '' }}>Apartment</option>
                                    <option value="flat" {{ old('type') === 'flat' ? 'selected' : '' }}>Flat</option>
                                    <option value="room" {{ old('type') === 'room' ? 'selected' : '' }}>Room</option>
                                </select>
                                @error('type')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3" required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bedrooms</label>
                                <input type="number" name="bedrooms" class="form-control" value="{{ old('bedrooms', 1) }}" min="1" required>
                                @error('bedrooms')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Bathrooms</label>
                                <input type="number" name="bathrooms" class="form-control" value="{{ old('bathrooms', 1) }}" min="1" required>
                                @error('bathrooms')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Rent Amount (R)</label>
                                <input type="number" name="rent_amount" class="form-control" value="{{ old('rent_amount') }}" step="0.01" min="0" required>
                                @error('rent_amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
                                    <option value="occupied" {{ old('status') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('housing.properties.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
