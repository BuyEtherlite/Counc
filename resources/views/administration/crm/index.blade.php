
@extends('layouts.app')

@section('page-title', 'Administration CRM')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>💼 Administration CRM</h4>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('administration.customers') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>Customer Services</h5>
                    <p class="text-muted">Manage customer information and services</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('administration.service-requests') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-list fa-3x text-warning mb-3"></i>
                    <h5>Service Requests</h5>
                    <p class="text-muted">Track and manage service requests</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('administration.communications') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-envelope fa-3x text-info mb-3"></i>
                    <h5>Communications</h5>
                    <p class="text-muted">Manage communications and notices</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Recent Activity</h6>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <p>CRM dashboard coming soon...</p>
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
