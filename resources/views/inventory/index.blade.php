
@extends('layouts.app')

@section('page-title', 'Inventory Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📦 Inventory Management</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Item
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('inventory.items') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-3x text-primary mb-3"></i>
                    <h5>Items</h5>
                    <p class="text-muted">Inventory items management</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('inventory.stock') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-warehouse fa-3x text-warning mb-3"></i>
                    <h5>Stock</h5>
                    <p class="text-muted">Stock levels and tracking</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('inventory.suppliers') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-truck fa-3x text-success mb-3"></i>
                    <h5>Suppliers</h5>
                    <p class="text-muted">Supplier management</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Inventory Overview</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">450</h3>
                        <small class="text-muted">Total Items</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">25</h3>
                        <small class="text-muted">Low Stock</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-danger">5</h3>
                        <small class="text-muted">Out of Stock</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">R125K</h3>
                        <small class="text-muted">Total Value</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.module-card {
    cursor: pointer;
    transition: transform 0.2s;
}
.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endsection
