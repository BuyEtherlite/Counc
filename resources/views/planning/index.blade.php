
@extends('layouts.app')

@section('page-title', 'Town Planning')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏗️ Town Planning</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Application
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('planning.applications') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                    <h5>Applications</h5>
                    <p class="text-muted">Planning applications and submissions</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('planning.approvals') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>Approvals</h5>
                    <p class="text-muted">Approved planning permits</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('planning.zoning') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-map-marked-alt fa-3x text-warning mb-3"></i>
                    <h5>Zoning</h5>
                    <p class="text-muted">Zoning maps and regulations</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Planning Statistics</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">25</h3>
                        <small class="text-muted">Pending Applications</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">18</h3>
                        <small class="text-muted">Approved This Month</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">7</h3>
                        <small class="text-muted">Under Review</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">3</h3>
                        <small class="text-muted">Zones Defined</small>
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
