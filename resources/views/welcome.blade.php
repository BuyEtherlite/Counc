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