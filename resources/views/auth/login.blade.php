@extends('layouts.install')

@section('title', 'Login - Council ERP')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card install-card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h2>🔑 Login to Council ERP</h2>
                    <p class="text-muted">Enter your credentials to access the system</p>
                </div>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            🚀 Login
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="/" class="text-decoration-none">
                        ← Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection