<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Council ERP System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-4">🏛️ Council ERP System</h1>
            <p class="lead mb-4">Comprehensive Municipal Management Solution</p>
            
            @if(!file_exists(storage_path('app/installed.lock')))
                <a href="/install" class="btn btn-light btn-lg">
                    🚀 Start Installation
                </a>
            @else
                <a href="/dashboard" class="btn btn-light btn-lg me-3">
                    📊 Dashboard
                </a>
                <a href="/login" class="btn btn-outline-light btn-lg">
                    🔑 Login
                </a>
            @endif
        </div>
    </div>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3">🏠</div>
                        <h5>Housing Management</h5>
                        <p class="text-muted">Manage waiting lists, allocations, and housing records</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3">💼</div>
                        <h5>Administrative CRM</h5>
                        <p class="text-muted">Customer relationship management and service delivery</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3">💰</div>
                        <h5>Financial Management</h5>
                        <p class="text-muted">Billing, receipting, and accounting integration</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3">🏊‍♂️</div>
                        <h5>Facility Bookings</h5>
                        <p class="text-muted">Swimming pools, halls, and recreational facilities</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3">🏗️</div>
                        <h5>Town Planning</h5>
                        <p class="text-muted">Development applications and architectural services</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card h-100">
                    <div class="card-body text-center">
                        <div class="display-6 mb-3">💧</div>
                        <h5>Water Management</h5>
                        <p class="text-muted">Connections, metering, and utility management</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p>&copy; 2024 Council ERP System. Built with Laravel for Municipal Excellence.</p>
        </div>
    </footer>
</body>
</html>
@extends('layouts.app')

@section('title', 'Welcome to Council ERP')

@section('content')
<div class="max-w-4xl mx-auto text-center">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            🏛️ Welcome to Council ERP
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            Comprehensive Enterprise Resource Planning for Local Government
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-8 mb-12">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-3xl mb-4">🏠</div>
            <h3 class="text-xl font-semibold mb-2">Housing Management</h3>
            <p class="text-gray-600 mb-4">
                Manage housing applications, waiting lists, property allocations, and tenant records.
            </p>
            <a href="{{ route('housing.applications.index') }}" class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors">
                View Housing
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-3xl mb-4">👥</div>
            <h3 class="text-xl font-semibold mb-2">Administration</h3>
            <p class="text-gray-600 mb-4">
                Manage users, departments, offices, and system configuration.
            </p>
            @if(auth()->user() && in_array(auth()->user()->role, ['super_admin', 'admin']))
                <a href="{{ route('admin.users.index') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                    Admin Panel
                </a>
            @else
                <span class="inline-block bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed">
                    Admin Access Required
                </span>
            @endif
        </div>
    </div>

    <div class="bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-2">Quick Stats</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
                <div class="text-2xl font-bold text-blue-600">{{ \App\Models\Housing\HousingApplication::count() }}</div>
                <div class="text-sm text-gray-600">Applications</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-green-600">{{ \App\Models\Housing\Property::where('status', 'available')->count() }}</div>
                <div class="text-sm text-gray-600">Available Properties</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-orange-600">{{ \App\Models\Housing\WaitingList::count() }}</div>
                <div class="text-sm text-gray-600">Waiting List</div>
            </div>
            <div>
                <div class="text-2xl font-bold text-purple-600">{{ \App\Models\User::where('is_active', true)->count() }}</div>
                <div class="text-sm text-gray-600">Active Users</div>
            </div>
        </div>
    </div>
</div>
@endsection
