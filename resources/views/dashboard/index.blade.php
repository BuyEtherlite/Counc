@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Welcome back, {{ $user->name }}! 👋</h5>
                    <p class="card-text text-muted">
                        Role: <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                        @if($user->department)
                            | Department: <span class="badge bg-info">{{ $user->department->name }}</span>
                        @endif
                        @if($user->office)
                            | Office: <span class="badge bg-secondary">{{ $user->office->name }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Users</h6>
                            <h2 class="mb-0">{{ $stats['total_users'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Departments</h6>
                            <h2 class="mb-0">{{ $stats['total_departments'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Offices</h6>
                            <h2 class="mb-0">{{ $stats['total_offices'] }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-map-marker-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Active Modules</h6>
                            <h2 class="mb-0">{{ count($accessibleModules) }}</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-cubes fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Modules -->
    @if(count($accessibleModules) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <h4>📋 Your Modules</h4>
            <p class="text-muted">Quick access to your assigned modules</p>
        </div>
    </div>
    
    <div class="row">
        @foreach($accessibleModules as $moduleKey => $module)
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card module-card h-100">
                <div class="card-body text-center">
                    <div class="display-6 mb-3">{{ $module['icon'] }}</div>
                    <h6 class="card-title">{{ $module['name'] }}</h6>
                    <p class="card-text text-muted small">
                        {{ count($module['items']) }} features available
                    </p>
                    <div class="mt-auto">
                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#module{{ $moduleKey }}">
                            View Features
                        </button>
                    </div>
                </div>
                <div class="collapse" id="module{{ $moduleKey }}">
                    <div class="card-footer">
                        <small class="text-muted">
                            @foreach($module['items'] as $item)
                                <span class="badge bg-light text-dark me-1 mb-1">{{ $item }}</span>
                            @endforeach
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="display-1 text-muted">📦</div>
                    <h4>No Modules Assigned</h4>
                    <p class="text-muted">Contact your administrator to get access to ERP modules.</p>
                    @if($user->role === 'super_admin')
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-primary">
                        Configure Departments
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Activity Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📈 Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="fas fa-clock fa-2x mb-3"></i>
                            <p>Activity logging will be implemented in upcoming updates.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for module cards
    document.querySelectorAll('.module-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('button')) {
                const button = this.querySelector('button[data-bs-toggle="collapse"]');
                if (button) {
                    button.click();
                }
            }
        });
    });
});
</script>
@endsection