@extends('layouts.install')

@section('title', 'Database Configuration - Council ERP Installation')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card install-card">
            <div class="card-body p-5">
                <div class="step-indicator">
                    <div class="step completed">1</div>
                    <div class="step active">2</div>
                    <div class="step">3</div>
                </div>

                <h2 class="text-center mb-4">🗄️ Database Configuration</h2>
                <p class="text-center text-muted mb-4">Configure your database connection settings</p>

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

                <form method="POST" action="{{ route('install.step2.store') }}" id="databaseForm">
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
                            <p class="text-muted">Enter your database connection details below:</p>
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
                        <div class="col-md-8">
                            <label for="db_password" class="form-label">Database Password</label>
                            <input type="password" class="form-control" id="db_password" name="db_password">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary w-100" id="testDbConnection">
                                <span id="testDbText">🔍 Test Connection</span>
                                <span id="testDbSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <div id="dbTestResult" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('install.step1') }}" class="btn btn-outline-secondary me-3">
                            ← Back to Requirements
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="continueButton" disabled>
                            <span id="continueText">📋 Continue to Council Details</span>
                            <span id="continueSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                        </button>
                        <p class="text-muted mt-2 small">
                            <i class="fas fa-info-circle"></i> Please test database connection before continuing
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
    const testDbBtn = document.getElementById('testDbConnection');
    const continueButton = document.getElementById('continueButton');
    const databaseForm = document.getElementById('databaseForm');
    let dbConnectionTested = false;

    // Test database connection
    testDbBtn.addEventListener('click', function() {
        const dbHost = document.getElementById('db_host').value;
        const dbPort = document.getElementById('db_port').value;
        const dbDatabase = document.getElementById('db_database').value;
        const dbUsername = document.getElementById('db_username').value;
        const dbPassword = document.getElementById('db_password').value;

        if (!dbHost || !dbPort || !dbDatabase || !dbUsername) {
            showDbResult('Please fill in all required database fields.', 'danger');
            return;
        }

        // Show loading state
        document.getElementById('testDbText').textContent = 'Testing...';
        document.getElementById('testDbSpinner').style.display = 'inline-block';
        testDbBtn.disabled = true;

        // Make AJAX request
        fetch('{{ route("install.test-database") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                db_host: dbHost,
                db_port: dbPort,
                db_database: dbDatabase,
                db_username: dbUsername,
                db_password: dbPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDbResult(data.message, 'success');
                dbConnectionTested = true;
                continueButton.disabled = false;
                document.getElementById('continueText').textContent = '📋 Continue to Council Details';
            } else {
                showDbResult(data.message, 'danger');
                dbConnectionTested = false;
                continueButton.disabled = true;
            }
        })
        .catch(error => {
            showDbResult('An error occurred while testing the database connection.', 'danger');
            dbConnectionTested = false;
            continueButton.disabled = true;
        })
        .finally(() => {
            // Reset button state
            document.getElementById('testDbText').textContent = '🔍 Test Connection';
            document.getElementById('testDbSpinner').style.display = 'none';
            testDbBtn.disabled = false;
        });
    });

    // Handle form submission
    databaseForm.addEventListener('submit', function(e) {
        if (!dbConnectionTested) {
            e.preventDefault();
            showDbResult('Please test the database connection first.', 'warning');
            return;
        }

        // Show loading state during form submission
        continueButton.disabled = true;
        document.getElementById('continueText').textContent = 'Processing...';
        document.getElementById('continueSpinner').style.display = 'inline-block';
    });

    // Reset database test status when fields change
    const dbFields = ['db_host', 'db_port', 'db_database', 'db_username', 'db_password'];
    dbFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', function() {
                dbConnectionTested = false;
                continueButton.disabled = true;
                hideDbResult();
            });
        }
    });

    function showDbResult(message, type) {
        const resultDiv = document.getElementById('dbTestResult');
        resultDiv.className = `alert alert-${type}`;
        resultDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'times-circle' : 'exclamation-triangle'}"></i> ${message}`;
        resultDiv.style.display = 'block';
    }

    function hideDbResult() {
        document.getElementById('dbTestResult').style.display = 'none';
    }
});
</script>
@endsection