
@extends('layouts.app')

@section('page-title', 'Inventory Management')

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ number_format($stats['total_items']) }}</h4>
                            <small>Total Items</small>
                        </div>
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">R{{ number_format($stats['total_value'], 2) }}</h4>
                            <small>Total Value</small>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['low_stock_items'] }}</h4>
                            <small>Low Stock Items</small>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['expiring_soon'] }}</h4>
                            <small>Expiring Soon</small>
                        </div>
                        <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Header and Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📦 Inventory Management</h4>
        <div>
            <a href="{{ route('inventory.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i>Add New Item
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    Reports
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('inventory.low-stock') }}">Low Stock Report</a></li>
                    <li><a class="dropdown-item" href="{{ route('inventory.expiring') }}">Expiring Items</a></li>
                    <li><a class="dropdown-item" href="{{ route('inventory.reports') }}">Full Reports</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search items..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="stock_status" class="form-select">
                        <option value="">All Stock Levels</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="normal" {{ request('stock_status') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="over" {{ request('stock_status') == 'over' ? 'selected' : '' }}>Overstock</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="discontinued" {{ request('status') == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card">
        <div class="card-body">
            @if($items->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Min/Max</th>
                            <th>Unit Cost</th>
                            <th>Total Value</th>
                            <th>Stock Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $item->item_code }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $item->name }}</strong><br>
                                    @if($item->description)
                                    <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ ucfirst($item->category) }}</span>
                            </td>
                            <td>
                                <strong class="{{ $item->isLowStock() ? 'text-danger' : 'text-success' }}">
                                    {{ $item->current_stock }}
                                </strong>
                                <small class="d-block text-muted">{{ $item->unit_of_measure }}</small>
                            </td>
                            <td>
                                <small>
                                    Min: {{ $item->minimum_stock }}<br>
                                    Max: {{ $item->maximum_stock }}
                                </small>
                            </td>
                            <td>
                                R{{ number_format($item->unit_cost, 2) }}
                            </td>
                            <td>
                                <strong>R{{ number_format($item->total_value, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->stock_status_color }}">
                                    @if($item->stock_status === 'low')
                                        Low Stock
                                    @elseif($item->stock_status === 'over')
                                        Overstock
                                    @else
                                        Normal
                                    @endif
                                </span>
                                @if($item->isExpiringSoon())
                                <br><small class="text-warning"><i class="fas fa-clock"></i> Expiring Soon</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('inventory.show', $item) }}" 
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#stockInModal{{ $item->id }}"
                                            title="Stock In">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="btn btn-outline-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#stockOutModal{{ $item->id }}"
                                            title="Stock Out">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <a href="{{ route('inventory.edit', $item) }}" 
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $items->appends(request()->query())->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-boxes fa-3x text-muted mb-3"></i>
                <h5>No Items Found</h5>
                <p class="text-muted">Start by adding your first inventory item.</p>
                <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add First Item
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Stock In Modals -->
@foreach($items as $item)
<div class="modal fade" id="stockInModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Stock: {{ $item->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inventory.stock-in', $item) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $item->current_stock }} {{ $item->unit_of_measure }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" name="quantity" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason" class="form-select" required>
                            <option value="">Select reason</option>
                            <option value="Purchase">Purchase</option>
                            <option value="Return">Return</option>
                            <option value="Transfer In">Transfer In</option>
                            <option value="Adjustment">Adjustment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stock Out Modal -->
<div class="modal fade" id="stockOutModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remove Stock: {{ $item->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inventory.stock-out', $item) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $item->current_stock }} {{ $item->unit_of_measure }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Remove</label>
                        <input type="number" name="quantity" class="form-control" min="1" max="{{ $item->current_stock }}" required>
                        <small class="text-muted">Available: {{ $item->current_stock }}</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason" class="form-select" required>
                            <option value="">Select reason</option>
                            <option value="Usage">Usage</option>
                            <option value="Sale">Sale</option>
                            <option value="Damage">Damage</option>
                            <option value="Transfer Out">Transfer Out</option>
                            <option value="Expired">Expired</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Remove Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
