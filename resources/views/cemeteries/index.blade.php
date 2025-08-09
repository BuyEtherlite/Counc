
@extends('layouts.app')

@section('page-title', 'Cemeteries Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>⚰️ Cemeteries Management</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Record
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('cemeteries.grave-register') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-3x text-primary mb-3"></i>
                    <h5>Grave Register</h5>
                    <p class="text-muted">Manage grave plots and records</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('cemeteries.burials') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-cross fa-3x text-warning mb-3"></i>
                    <h5>Burials</h5>
                    <p class="text-muted">Burial records and services</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('cemeteries.maintenance') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-tools fa-3x text-success mb-3"></i>
                    <h5>Maintenance</h5>
                    <p class="text-muted">Cemetery maintenance schedules</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Cemetery Statistics</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">150</h3>
                        <small class="text-muted">Total Plots</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">120</h3>
                        <small class="text-muted">Occupied</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">30</h3>
                        <small class="text-muted">Available</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">5</h3>
                        <small class="text-muted">Reserved</small>
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
