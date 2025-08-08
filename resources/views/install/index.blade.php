@extends('layouts.install')

@section('title', 'Install Council ERP System')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card install-card">
            <div class="card-body p-5">
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                    <div class="step">3</div>
                </div>

                <h2 class="text-center mb-4">Welcome to Council ERP Installation</h2>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('install.store') }}">
                    @csrf
                    
                    <!-- Site Settings -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="border-bottom pb-2">📝 Site Settings</h4>
                        </div>
                        <div class="col-md-6">
                            <label for="site_name" class="form-label">Site Name *</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" 
                                   value="{{ old('site_name', 'City Council ERP') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="site_description" class="form-label">Site Description</label>
                            <input type="text" class="form-control" id="site_description" name="site_description" 
                                   value="{{ old('site_description', 'Municipal ERP Management System') }}">
                        </div>
                    </div>

                    <!-- Database Settings -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4 class="border-bottom pb-2">🗄️ Database Settings</h4>
                        </div>
                        <div class="col-md-6">
                            <label for="db_host" class="form-label">Database Host *</label>
                            <input type="text" class="form-control" id="db_host" name="db_host" 
                                   value="{{ old('db_host', '127.0.0.1') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="db_port" class="form-label">Database Port *</label>
                            <input type="number" class="form-control" id="db_port" name="db_port" 
                                   value="{{ old('db_port', '3306') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="db_database" class="form-label">Database Name *</label>
                            <input type="text" class="form-control" id="db_database" name="db_database" 
                                   value="{{ old('db_database', 'council_erp') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="db_username" class="form-label">Database Username *</label>
                            <input type="text" class="form-control" id="db_username" name="db_username" 
                                   value="{{ old('db_username', 'root') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label for="db_password" class="form-label">Database Password</label>
                            <input type="password" class="form-control" id="db_password" name="db_password">
                        </div>
                    </div>

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
                            <h4 class="border-bottom pb-2">🏛️ Council Details</h4>
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
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            🚀 Install Council ERP System
                        </button>
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
    // Test database connection button
    const testDbBtn = document.createElement('button');
    testDbBtn.type = 'button';
    testDbBtn.className = 'btn btn-outline-secondary btn-sm';
    testDbBtn.innerHTML = '🔍 Test Connection';
    testDbBtn.onclick = function() {
        // Add AJAX test functionality here
        alert('Database connection testing functionality can be added here');
    };
    
    const dbSection = document.querySelector('h4:contains("Database Settings")');
    if (dbSection) {
        dbSection.parentElement.appendChild(testDbBtn);
    }
});
</script>
@endsection