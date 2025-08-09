
@extends('layouts.app')

@section('page-title', 'Facility Bookings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏊‍♂️ Facility Bookings</h4>
        <button class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Booking
        </button>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('facilities.pools') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-swimming-pool fa-3x text-primary mb-3"></i>
                    <h5>Pool Bookings</h5>
                    <p class="text-muted">Swimming pool reservations</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('facilities.halls') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-3x text-warning mb-3"></i>
                    <h5>Hall Rentals</h5>
                    <p class="text-muted">Community hall bookings</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card module-card" onclick="location.href='{{ route('facilities.sports') }}'">
                <div class="card-body text-center">
                    <i class="fas fa-football-ball fa-3x text-success mb-3"></i>
                    <h5>Sports Facilities</h5>
                    <p class="text-muted">Sports field reservations</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6>Today's Bookings</h6>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-calendar-check fa-2x mb-2"></i>
                <p>No bookings scheduled for today</p>
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
