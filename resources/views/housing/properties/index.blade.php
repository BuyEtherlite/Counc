
@extends('layouts.app')

@section('page-title', 'Properties Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏘️ Properties Management</h4>
        <a href="{{ route('housing.properties.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add New Property
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($properties->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Address</th>
                            <th>Type</th>
                            <th>Rooms</th>
                            <th>Rent Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($properties as $property)
                        <tr>
                            <td><strong>{{ $property->name }}</strong></td>
                            <td>{{ $property->address }}</td>
                            <td>{{ ucfirst($property->type) }}</td>
                            <td>{{ $property->bedrooms }}BR / {{ $property->bathrooms }}BA</td>
                            <td>R {{ number_format($property->rent_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $property->status === 'available' ? 'success' : ($property->status === 'occupied' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($property->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('housing.properties.show', $property) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('housing.properties.edit', $property) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('housing.properties.destroy', $property) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" 
                                                onclick="return confirm('Are you sure?')">
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

            {{ $properties->links() }}
            @else
            <div class="text-center py-5">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                <h5>No Properties Found</h5>
                <p class="text-muted">Start by adding your first property.</p>
                <a href="{{ route('housing.properties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add First Property
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
