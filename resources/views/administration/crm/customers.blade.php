
@extends('layouts.app')

@section('page-title', 'Customer Services')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>👥 Customer Services</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('administration.index') }}">Administration</a></li>
                    <li class="breadcrumb-item active">Customers</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Customer
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5>Customer Management</h5>
                <p class="text-muted">Customer service management system coming soon...</p>
            </div>
        </div>
    </div>
</div>
@endsection
