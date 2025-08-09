
@extends('layouts.app')

@section('page-title', 'Finance Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>💰 Finance Management</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Transaction
        </button>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('finance.budget') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-chart-pie fa-3x text-primary mb-3"></i>
                    <h5>Budget</h5>
                    <p class="text-muted">Budget planning and tracking</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('finance.revenue') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-up fa-3x text-success mb-3"></i>
                    <h5>Revenue</h5>
                    <p class="text-muted">Income and revenue tracking</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('finance.expenses') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-arrow-down fa-3x text-danger mb-3"></i>
                    <h5>Expenses</h5>
                    <p class="text-muted">Expense management</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('finance.reports') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-info mb-3"></i>
                    <h5>Reports</h5>
                    <p class="text-muted">Financial reports</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Financial Overview</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">R2.5M</h3>
                        <small class="text-muted">Total Revenue</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-danger">R1.8M</h3>
                        <small class="text-muted">Total Expenses</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">R700K</h3>
                        <small class="text-muted">Net Profit</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">R450K</h3>
                        <small class="text-muted">Available Budget</small>
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
