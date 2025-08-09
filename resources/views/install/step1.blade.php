
@extends('layouts.install')

@section('title', 'System Requirements - Council ERP Installation')

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

                <h2 class="text-center mb-4">🔍 System Requirements Check</h2>
                <p class="text-center text-muted mb-4">Please ensure your system meets the following requirements before proceeding</p>

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

                @if(!collect($requirements)->every('status'))
                    <div class="alert alert-info">
                        <h6 class="alert-heading">💡 First time deploying to hosting?</h6>
                        <p class="mb-2">If you just uploaded files to your hosting provider and see failed requirements, this is normal!</p>
                        <p class="mb-0">
                            <strong>Most common fix:</strong> Run <code>composer install --no-dev</code> in your hosting terminal.
                            <a href="#deployment-help" data-bs-toggle="collapse" class="ms-2">View deployment guide</a>
                        </p>
                    </div>
                    
                    <div class="collapse" id="deployment-help">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>🚀 Quick Deployment Fix</h6>
                                <ol class="mb-2">
                                    <li>Access your hosting control panel or SSH</li>
                                    <li>Navigate to your website directory</li>
                                    <li>Run: <code>composer install --no-dev</code></li>
                                    <li>Set permissions: <code>chmod -R 775 storage/ bootstrap/cache/</code></li>
                                    <li>Refresh this page</li>
                                </ol>
                                <p class="small mb-0">
                                    <strong>Need detailed help?</strong> See <a href="{{ asset('DEPLOYMENT.md') }}" target="_blank">DEPLOYMENT.md</a> 
                                    or contact your hosting provider's support.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- System Requirements -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4 class="border-bottom pb-2">⚙️ PHP Requirements</h4>
                        <div class="requirements-list">
                            @foreach($requirements as $req)
                                <div class="requirement-item d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $req['name'] }}</span>
                                    <span class="badge {{ $req['status'] ? 'bg-success' : 'bg-danger' }}">
                                        {{ $req['status'] ? '✓' : '✗' }} {{ $req['current'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h4 class="border-bottom pb-2">📁 Folder Permissions</h4>
                        <div class="permissions-list">
                            @foreach($permissions as $perm)
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>{{ $perm['name'] }}</span>
                                    <span class="badge {{ $perm['status'] ? 'bg-success' : 'bg-danger' }}">
                                        {{ $perm['status'] ? '✓ Writable' : '✗ Not Writable' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @php
                    $allRequirementsMet = collect($requirements)->every('status') && collect($permissions)->every('status');
                @endphp

                <div class="text-center">
                    @if($allRequirementsMet)
                        <a href="{{ route('install.step2') }}" class="btn btn-primary btn-lg px-5">
                            ✅ Continue to Database Setup
                        </a>
                        <p class="text-success mt-2 small">
                            <i class="fas fa-check-circle"></i> All requirements met! You can proceed to the next step.
                        </p>
                    @else
                        <button class="btn btn-outline-secondary btn-lg px-5" disabled>
                            ⚠️ Resolve System Requirements First
                        </button>
                        <p class="text-danger mt-2 small">
                            <i class="fas fa-exclamation-triangle"></i> Please fix the above issues before continuing.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
