
@extends('layouts.app')

@section('page-title', 'Property Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                            <small>Total Properties</small>
                        </div>
                        <i class="fas fa-home fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['available'] }}</h4>
                            <small>Available</small>
                        </div>
                        <i class="fas fa-key fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['occupied'] }}</h4>
                            <small>Occupied</small>
                        </div>
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['maintenance'] }}</h4>
                            <small>Maintenance</small>
                        </div>
                        <i class="fas fa-tools fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header and Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏠 Property Management</h4>
        <a href="{{ route('housing.properties.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Property
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by code or address..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="occupied" {{ request('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="house" {{ request('type') == 'house' ? 'selected' : '' }}>House</option>
                        <option value="flat" {{ request('type') == 'flat' ? 'selected' : '' }}>Flat</option>
                        <option value="townhouse" {{ request('type') == 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                        <option value="room" {{ request('type') == 'room' ? 'selected' : '' }}>Room</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('housing.properties.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="card">
        <div class="card-body">
            @if($properties->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Property Code</th>
                            <th>Address</th>
                            <th>Type</th>
                            <th>Bed/Bath</th>
                            <th>Rental Amount</th>
                            <th>Status</th>
                            <th>Current Tenant</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $property)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $property->property_code }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $property->address }}</strong><br>
                                    <small class="text-muted">{{ $property->suburb }}, {{ $property->city }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ ucfirst($property->property_type) }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    <i class="fas fa-bed"></i> {{ $property->bedrooms }}<br>
                                    <i class="fas fa-bath"></i> {{ $property->bathrooms }}
                                </small>
                            </td>
                            <td>
                                <strong>R{{ number_format($property->rental_amount, 2) }}</strong>
                                <small class="d-block text-muted">per month</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $property->status_color }}">
                                    {{ ucfirst($property->status) }}
                                </span>
                            </td>
                            <td>
                                @if($property->currentAllocation)
                                    <div>
                                        <strong>{{ $property->currentAllocation->tenant->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $property->currentAllocation->tenant->phone ?? '' }}</small>
                                    </div>
                                @else
                                    <span class="text-muted">No tenant</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('housing.properties.show', $property) }}" 
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('housing.properties.edit', $property) }}" 
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($property->status === 'available')
                                    <button class="btn btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#allocateModal{{ $property->id }}"
                                            title="Allocate">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    @endif
                                    <form method="POST" action="{{ route('housing.properties.destroy', $property) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('Are you sure you want to delete this property?')"
                                                title="Delete">
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
                {{ $properties->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                <h5>No Properties Found</h5>
                <p class="text-muted">Start by adding your first property to the system.</p>
                <a href="{{ route('housing.properties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add First Property
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Allocation Modals -->
@foreach($properties->where('status', 'available') as $property)
<div class="modal fade" id="allocateModal{{ $property->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Allocate Property: {{ $property->property_code }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('housing.properties.allocate', $property) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tenant</label>
                        <select name="tenant_id" class="form-select" required>
                            <option value="">Select a tenant</option>
                            <!-- This would be populated with available tenants -->
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date (Optional)</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Monthly Rent</label>
                                <input type="number" name="monthly_rent" class="form-control" 
                                       step="0.01" value="{{ $property->rental_amount }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Deposit Paid</label>
                                <input type="number" name="deposit_paid" class="form-control" 
                                       step="0.01" value="{{ $property->deposit_amount }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate Property</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
