
@extends('layouts.app')

@section('page-title', 'Water Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>💧 Water Management</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Connection
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('water.connections') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-tint fa-3x text-primary mb-3"></i>
                    <h5>Connections</h5>
                    <p class="text-muted">Water connection management</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('water.metering') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-gauge fa-3x text-info mb-3"></i>
                    <h5>Metering</h5>
                    <p class="text-muted">Water meter readings</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('water.billing') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-receipt fa-3x text-success mb-3"></i>
                    <h5>Billing</h5>
                    <p class="text-muted">Water usage billing</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Water Management Overview</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">1,250</h3>
                        <small class="text-muted">Active Connections</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">45,000L</h3>
                        <small class="text-muted">Daily Usage</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">25</h3>
                        <small class="text-muted">Pending Readings</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">R125K</h3>
                        <small class="text-muted">Monthly Revenue</small>
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
