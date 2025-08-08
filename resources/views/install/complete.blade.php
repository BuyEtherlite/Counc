@extends('layouts.install')

@section('title', 'Installation Complete - Council ERP')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card install-card">
            <div class="card-body p-5 text-center">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step completed">3</div>
                </div>

                <div class="mb-4">
                    <div class="display-1 text-success">✅</div>
                    <h2 class="text-success">Installation Complete!</h2>
                    <p class="lead text-muted">Your Council ERP System has been successfully installed and configured.</p>
                </div>

                <div class="card bg-light mb-4">
                    <div class="card-body">
                        <h5 class="card-title">📋 Admin User Details</h5>
                        <div class="row text-start">
                            <div class="col-sm-4"><strong>Name:</strong></div>
                            <div class="col-sm-8">{{ $admin->name }}</div>
                            <div class="col-sm-4"><strong>Email:</strong></div>
                            <div class="col-sm-8">{{ $admin->email }}</div>
                            <div class="col-sm-4"><strong>Role:</strong></div>
                            <div class="col-sm-8"><span class="badge bg-primary">Super Administrator</span></div>
                            <div class="col-sm-4"><strong>Created:</strong></div>
                            <div class="col-sm-8">{{ $admin->created_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">🔐 Security Notice</h6>
                    <p class="mb-0">Please save these admin credentials in a secure location. You can use these credentials to log in and create additional users, assign them to departments and offices, and configure the system modules.</p>
                </div>

                <div class="alert alert-warning">
                    <h6 class="alert-heading">⚠️ Next Steps</h6>
                    <ul class="text-start mb-0">
                        <li>Log in with your admin credentials</li>
                        <li>Create departments and offices</li>
                        <li>Set up additional users and assign roles</li>
                        <li>Configure module permissions for each department</li>
                        <li>Start using the ERP modules</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="/" class="btn btn-primary btn-lg me-3">
                        🏠 Go to Dashboard
                    </a>
                    <a href="/login" class="btn btn-outline-primary btn-lg">
                        🔑 Login Now
                    </a>
                </div>

                <div class="mt-4 text-muted small">
                    <p>🎉 Welcome to your new Council ERP System!</p>
                    <p>Installation completed at {{ now()->format('M d, Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection