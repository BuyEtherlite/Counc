@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-1">Welcome back, {{ auth()->user()->name }}!</h3>
                            <p class="mb-0 opacity-75">{{ now()->format('l, F j, Y') }} - Here's what's happening in your council today.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-city fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Housing Properties</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Housing\Property::count() }}
                            </div>
                            <div class="mt-1">
                                <small class="text-success">
                                    <i class="fas fa-home"></i> 
                                    {{ \App\Models\Housing\Property::where('status', 'available')->count() }} Available
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Monthly Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                R{{ number_format(\App\Models\Finance\Invoice::where('status', 'paid')->whereMonth('paid_at', now())->sum('total_amount') ?? 0, 2) }}
                            </div>
                            <div class="mt-1">
                                <small class="text-info">
                                    <i class="fas fa-file-invoice"></i> 
                                    {{ \App\Models\Finance\Invoice::whereMonth('created_at', now())->count() }} Invoices
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Inventory Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Inventory\Item::whereColumn('current_stock', '<=', 'minimum_stock')->count() }}
                            </div>
                            <div class="mt-1">
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Low Stock Alert
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Departments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ \App\Models\Department::where('is_active', true)->count() }}
                            </div>
                            <div class="mt-1">
                                <small class="text-info">
                                    <i class="fas fa-building"></i> 
                                    {{ \App\Models\Office::where('is_active', true)->count() }} Offices
                                </small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and Recent Activity -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 font-weight-bold text-primary">Recent Activity</h6>
                    <small class="text-muted">Last 7 days</small>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse(\App\Models\Finance\Invoice::latest()->take(5)->get() as $invoice)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Invoice {{ $invoice->invoice_number }} created</h6>
                                <p class="mb-1 text-sm">Customer: {{ $invoice->customer_name }} - R{{ number_format($invoice->total_amount, 2) }}</p>
                                <small class="text-muted">{{ $invoice->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No recent activity</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('housing.properties.create') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Add Property
                        </a>
                        <a href="{{ route('finance.create-invoice') }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-invoice me-2"></i>Create Invoice
                        </a>
                        <a href="{{ route('admin.departments.create') }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-building me-2"></i>Add Department
                        </a>
                        <a href="{{ route('inventory.create') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-boxes me-2"></i>Add Inventory Item
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alerts and Notifications -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 font-weight-bold text-danger">Alerts & Notifications</h6>
                </div>
                <div class="card-body">
                    @php
                        $overdueInvoices = \App\Models\Finance\Invoice::where('status', 'overdue')->count();
                        $lowStockItems = class_exists('\App\Models\Inventory\Item') ? 
                            \App\Models\Inventory\Item::whereRaw('current_stock <= minimum_stock')->count() : 0;
                    @endphp

                    @if($overdueInvoices > 0)
                    <div class="alert alert-danger alert-sm">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>{{ $overdueInvoices }}</strong> overdue invoice{{ $overdueInvoices > 1 ? 's' : '' }}
                        <a href="{{ route('finance.invoices', ['status' => 'overdue']) }}" class="alert-link">View</a>
                    </div>
                    @endif

                    @if($lowStockItems > 0)
                    <div class="alert alert-warning alert-sm">
                        <i class="fas fa-box me-2"></i>
                        <strong>{{ $lowStockItems }}</strong> item{{ $lowStockItems > 1 ? 's' : '' }} low in stock
                        <a href="{{ route('inventory.low-stock') }}" class="alert-link">View</a>
                    </div>
                    @endif

                    @if($overdueInvoices == 0 && $lowStockItems == 0)
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted mb-0">All systems operating normally</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Module Overview Cards -->
    <div class="row">
        <div class="col-12">
            <h5 class="mb-3 font-weight-bold">Module Overview</h5>
        </div>

        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary text-white me-3">
                            <i class="fas fa-home"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Housing Management</h6>
                            <small class="text-muted">Properties & Allocations</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h6 mb-0">{{ \App\Models\Housing\Property::count() }}</div>
                                <small class="text-muted">Properties</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h6 mb-0">{{ \App\Models\Housing\Property::where('status', 'available')->count() }}</div>
                                <small class="text-muted">Available</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="h6 mb-0">{{ \App\Models\Housing\Property::where('status', 'occupied')->count() }}</div>
                            <small class="text-muted">Occupied</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('housing.properties.index') }}" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-success text-white me-3">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Finance Management</h6>
                            <small class="text-muted">Invoices & Payments</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h6 mb-0">{{ \App\Models\Finance\Invoice::count() }}</div>
                                <small class="text-muted">Invoices</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h6 mb-0">{{ \App\Models\Finance\Invoice::where('status', 'paid')->count() }}</div>
                                <small class="text-muted">Paid</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="h6 mb-0">R{{ number_format(\App\Models\Finance\Invoice::where('status', 'paid')->sum('total_amount') ?? 0) }}</div>
                            <small class="text-muted">Revenue</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('finance.index') }}" class="btn btn-success btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-warning text-white me-3">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Inventory Management</h6>
                            <small class="text-muted">Stock & Supplies</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-right">
                                @php $itemCount = class_exists('\App\Models\Inventory\Item') ? \App\Models\Inventory\Item::count() : 0; @endphp
                                <div class="h6 mb-0">{{ $itemCount }}</div>
                                <small class="text-muted">Items</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-right">
                                @php $lowStock = class_exists('\App\Models\Inventory\Item') ? \App\Models\Inventory\Item::whereColumn('current_stock', '<=', 'minimum_stock')->count() : 0; @endphp
                                <div class="h6 mb-0">{{ $lowStock }}</div>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                        <div class="col-4">
                            @php $totalValue = class_exists('\App\Models\Inventory\Item') ? \App\Models\Inventory\Item::sum('total_value') : 0; @endphp
                            <div class="h6 mb-0">R{{ number_format($totalValue ?? 0) }}</div>
                            <small class="text-muted">Value</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('inventory.index') }}" class="btn btn-warning btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}
.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}
.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}
.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}
.icon-circle {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.timeline {
    position: relative;
}
.timeline-item {
    display: flex;
    margin-bottom: 1rem;
}
.timeline-marker {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 1rem;
    margin-top: 0.25rem;
    flex-shrink: 0;
}
.timeline-content {
    flex-grow: 1;
}
</style>
@endsection