
@extends('layouts.app')

@section('page-title', 'Committee Administration')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>👥 Committee Administration</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Schedule Meeting
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('committee.members') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>Members</h5>
                    <p class="text-muted">Committee member management</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('committee.meetings') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-calendar fa-3x text-warning mb-3"></i>
                    <h5>Meetings</h5>
                    <p class="text-muted">Meeting scheduling and management</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('committee.minutes') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                    <h5>Minutes</h5>
                    <p class="text-muted">Meeting minutes and records</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Committee Overview</h6>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-primary">12</h3>
                        <small class="text-muted">Active Members</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-warning">3</h3>
                        <small class="text-muted">Scheduled Meetings</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-success">24</h3>
                        <small class="text-muted">Meetings This Year</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h3 class="text-info">18</h3>
                        <small class="text-muted">Recorded Minutes</small>
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
