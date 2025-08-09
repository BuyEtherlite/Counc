
@extends('layouts.app')

@section('page-title', 'Property Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏘️ Property Management</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Property
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('property.valuations') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                    <h5>Valuations</h5>
                    <p class="text-muted">Property valuations and assessments</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('property.leases') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-file-contract fa-3x text-warning mb-3"></i>
                    <h5>Leases</h5>
                    <p class="text-muted">Lease agreements and management</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('property.land-records') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-map fa-3x text-success mb-3"></i>
                    <h5>Land Records</h5>
                    <p class="text-muted">Land ownership and records</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Property Portfolio</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">45</h3>
                        <small class="text-muted">Total Properties</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">R2.5M</h3>
                        <small class="text-muted">Total Value</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">12</h3>
                        <small class="text-muted">Active Leases</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">R45K</h3>
                        <small class="text-muted">Monthly Income</small>
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
