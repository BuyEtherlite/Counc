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

                <form method="POST" action="{{ route('install.complete') }}">

                    <!-- Admin User Settings -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="border-bottom pb-2">👤 Admin User Settings</h4>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_name" class="form-label">Admin Name *</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name"
                                   value="{{ old('admin_name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_email" class="form-label">Admin Email *</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email"
                                   value="{{ old('admin_email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_password" class="form-label">Admin Password *</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" id="admin_password_confirmation"
                                   name="admin_password_confirmation" required>
                        </div>
                    </div>

                    <!-- Council Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="border-bottom pb-2">🏛️ Council Information</h4>
                        </div>
                        <div class="col-md-12">
                            <label for="council_name" class="form-label">Council Name *</label>
                            <input type="text" class="form-control" id="council_name" name="council_name"
                                   value="{{ old('council_name') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label for="council_address" class="form-label">Council Address *</label>
                            <textarea class="form-control" id="council_address" name="council_address" rows="3" required>{{ old('council_address') }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label for="council_contact" class="form-label">Contact Information *</label>
                            <textarea class="form-control" id="council_contact" name="council_contact" rows="2"
                                      placeholder="Phone, Email, Website" required>{{ old('council_contact') }}</textarea>
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

    // Handle form submission
    finalForm.addEventListener('submit', function(e) {
        // Validate required fields
        const requiredFields = ['admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation', 'council_name', 'council_address', 'council_contact'];
        let hasErrors = false;

        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                field.classList.add('is-invalid');
                hasErrors = true;
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });

        // Check password confirmation
        const password = document.getElementById('admin_password').value;
        const confirmPassword = document.getElementById('admin_password_confirmation').value;

        if (password !== confirmPassword) {
            document.getElementById('admin_password_confirmation').classList.add('is-invalid');
            hasErrors = true;
        }

        if (hasErrors) {
            e.preventDefault();
            return;
        }

        // Show loading state during installation
        installButton.disabled = true;
        document.getElementById('installText').textContent = 'Installing...';
        document.getElementById('installSpinner').style.display = 'inline-block';

        // Disable all form inputs to prevent changes
        const formInputs = finalForm.querySelectorAll('input, button, textarea');
        formInputs.forEach(input => input.disabled = true);
    });
});
</script>
@endsection