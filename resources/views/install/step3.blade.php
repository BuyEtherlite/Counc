@extends('layouts.install')

@section('title', 'Council Details - Council ERP Installation')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card install-card">
            <div class="card-body p-5">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step completed">2</div>
                    <div class="step active">3</div>
                </div>

                <h2 class="text-center mb-4">🏛️ Council Details & Admin Setup</h2>
                <p class="text-center text-muted mb-4">Complete the installation by setting up your council information and admin account</p>

                @if (isset($installData))
                    <div class="alert alert-success">
                        <h6 class="alert-heading">✅ Database Setup Complete!</h6>
                        <p class="mb-1"><strong>Site:</strong> {{ $installData['site_name'] ?? 'Council ERP' }}</p>
                        <p class="mb-1"><strong>Database:</strong> All tables created successfully</p>
                        <p class="mb-0"><small class="text-muted">Completed: {{ date('M j, Y g:i A', $installData['step2_completed_at'] ?? time()) }}</small></p>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">❌ Please fix the following issues:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('install.complete') }}" id="finalForm">
                    @csrf

                    <!-- Admin User Setup -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="border-bottom pb-2">👤 Administrator Account</h4>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" 
                                   value="{{ old('admin_name', 'System Administrator') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" 
                                   value="{{ old('admin_email', 'admin@council.local') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" 
                                   required minlength="8">
                            <small class="form-text text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="admin_password_confirmation" 
                                   name="admin_password_confirmation" required minlength="8">
                        </div>
                    </div>

                    <!-- Council Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="border-bottom pb-2">🏛️ Council Information</h4>
                        </div>
                        <div class="col-12">
                            <label for="council_name" class="form-label">Council Name *</label>
                            <input type="text" class="form-control" id="council_name" name="council_name" 
                                   value="{{ old('council_name', 'City Council') }}" required>
                        </div>
                        <div class="col-12">
                            <label for="council_address" class="form-label">Council Address *</label>
                            <textarea class="form-control" id="council_address" name="council_address" 
                                      rows="3" required>{{ old('council_address', '123 Main Street, City, State 12345') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="council_contact" class="form-label">Contact Information *</label>
                            <textarea class="form-control" id="council_contact" name="council_contact" 
                                      rows="2" required>{{ old('council_contact', 'Phone: (555) 123-4567, Email: info@council.local') }}</textarea>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('install.step2') }}" class="btn btn-outline-secondary me-3">
                            ← Back to Database
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-5" id="installButton">
                            <span id="installText">🚀 Complete Installation</span>
                            <span id="installSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                        </button>
                        <p class="text-muted mt-2 small">
                            <i class="fas fa-info-circle"></i> This will create the admin user and council in the database
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const finalForm = document.getElementById('finalForm');
    const installButton = document.getElementById('installButton');
    const installText = document.getElementById('installText');
    const installSpinner = document.getElementById('installSpinner');

    finalForm.addEventListener('submit', function(e) {
        // Show loading state
        installButton.disabled = true;
        installText.textContent = 'Creating Admin & Council...';
        installSpinner.style.display = 'inline-block';
    });

    // Password confirmation validation
    const password = document.getElementById('admin_password');
    const confirmPassword = document.getElementById('admin_password_confirmation');

    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>
@endsection