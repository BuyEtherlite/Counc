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
                        <h5 class="card-title">📋 Admin Login Credentials</h5>
                        <div class="alert alert-info">
                            <strong>⚠️ IMPORTANT:</strong> Please copy these credentials to a secure location before proceeding.
                        </div>
                        <div class="row text-start">
                            <div class="col-sm-4"><strong>Name:</strong></div>
                            <div class="col-sm-8">
                                <code>{{ $admin->name }}</code>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $admin->name }}')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="col-sm-4"><strong>Email/Username:</strong></div>
                            <div class="col-sm-8">
                                <code>{{ $admin->email }}</code>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $admin->email }}')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <div class="col-sm-4"><strong>Password:</strong></div>
                            <div class="col-sm-8">
                                <code id="adminPassword">{{ request()->session()->get('temp_admin_password', 'Use the password you entered during installation') }}</code>
                                <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard(document.getElementById('adminPassword').textContent)">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
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
                    <a href="/login" class="btn btn-primary btn-lg me-3">
                        🔑 Continue to Login
                    </a>
                    <a href="/dashboard" class="btn btn-outline-primary btn-lg">
                        🏠 Go to Dashboard
                    </a>
                </div>

                <div class="mt-4 text-muted small">
                    <p>🎉 Welcome to your new Council ERP System!</p>
                    <p>Installation completed at {{ now()->format('M d, Y H:i:s') }}</p>
                    <p><strong>Next Steps:</strong> Use the "Continue to Login" button to access your system with the credentials above.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Create a temporary success message
        const btn = event.target.closest('button');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(() => {
            btn.innerHTML = originalIcon;
        }, 2000);
    }, function(err) {
        console.error('Could not copy text: ', err);
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            const btn = event.target.closest('button');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalIcon;
            }, 2000);
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
    });
}
</script>
@endsection