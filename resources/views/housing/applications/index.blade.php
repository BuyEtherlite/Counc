@extends('layouts.app')

@section('page-title', 'Housing Applications')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>🏠 Housing Applications</h4>
        <a href="{{ route('housing.applications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Application
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Pending</h6>
                            <h3>{{ $applications->where('status', 'pending')->count() }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Under Review</h6>
                            <h3>{{ $applications->where('status', 'under_review')->count() }}</h3>
                        </div>
                        <i class="fas fa-search fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Approved</h6>
                            <h3>{{ $applications->where('status', 'approved')->count() }}</h3>
                        </div>
                        <i class="fas fa-check fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>On Waiting List</h6>
                            <h3>{{ $applications->where('status', 'on_waiting_list')->count() }}</h3>
                        </div>
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($applications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Application #</th>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>Family Size</th>
                            <th>Income</th>
                            <th>Applied</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applications as $application)
                        <tr>
                            <td>
                                <strong>{{ $application->application_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $application->applicant_name }}</strong>
                                    <br>
                                    <small class="text-muted">ID: {{ $application->applicant_id_number }}</small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <small>📧 {{ $application->applicant_email }}</small><br>
                                    <small>📞 {{ $application->applicant_phone }}</small>
                                </div>
                            </td>
                            <td>{{ $application->family_size }}</td>
                            <td>R{{ number_format($application->monthly_income, 0) }}</td>
                            <td>{{ $application->application_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $application->status_badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($application->priority_score > 0)
                                    <span class="badge bg-info">{{ $application->priority_score }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('housing.applications.show', $application) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('housing.applications.edit', $application) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $applications->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-home fa-3x text-muted mb-3"></i>
                <h5>No Applications Found</h5>
                <p class="text-muted">Start by creating your first housing application.</p>
                <a href="{{ route('housing.applications.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create First Application
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection