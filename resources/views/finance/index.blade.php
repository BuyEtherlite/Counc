
@extends('layouts.app')

@section('page-title', 'Finance Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>💰 Finance Dashboard</h4>
        <div>
            <a href="{{ route('finance.create-invoice') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-1"></i>Create Invoice
            </a>
            <a href="{{ route('finance.reports') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chart-bar me-1"></i>Reports
            </a>
        </div>
    </div>

    <!-- Financial Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">R{{ number_format($stats['total_revenue'], 2) }}</h4>
                            <small>Total Revenue</small>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">R{{ number_format($stats['this_month_revenue'], 2) }}</h4>
                            <small>This Month</small>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['pending_invoices'] }}</h4>
                            <small>Pending Invoices</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['overdue_invoices'] }}</h4>
                            <small>Overdue Invoices</small>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Revenue Chart -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Monthly Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('finance.create-invoice') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create New Invoice
                        </a>
                        <a href="{{ route('finance.invoices', ['status' => 'overdue']) }}" class="btn btn-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>View Overdue Invoices
                        </a>
                        <a href="{{ route('finance.invoices', ['status' => 'sent']) }}" class="btn btn-info">
                            <i class="fas fa-paper-plane me-2"></i>Pending Invoices
                        </a>
                        <a href="{{ route('finance.reports') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-chart-bar me-2"></i>Generate Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Invoices -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Invoices</h5>
            <a href="{{ route('finance.invoices') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            @if($recentInvoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentInvoices as $invoice)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $invoice->invoice_number }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $invoice->customer_name }}</strong><br>
                                    <small class="text-muted">{{ $invoice->customer_email }}</small>
                                </div>
                            </td>
                            <td>
                                <strong>R{{ number_format($invoice->total_amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="{{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                                    {{ $invoice->due_date->format('M d, Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $invoice->status_color }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('finance.show-invoice', $invoice) }}" 
                                       class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($invoice->status !== 'paid')
                                    <button class="btn btn-outline-success" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#paymentModal{{ $invoice->id }}"
                                            title="Mark as Paid">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h6>No invoices found</h6>
                <p class="text-muted">Start by creating your first invoice.</p>
                <a href="{{ route('finance.create-invoice') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Create First Invoice
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Payment Modals -->
@foreach($recentInvoices->where('status', '!=', 'paid') as $invoice)
<div class="modal fade" id="paymentModal{{ $invoice->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Invoice as Paid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('finance.mark-as-paid', $invoice) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Invoice</label>
                        <input type="text" class="form-control" value="{{ $invoice->invoice_number }} - R{{ number_format($invoice->total_amount, 2) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select payment method</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Reference (Optional)</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="Transaction ID, cheque number, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: monthNames,
        datasets: [{
            label: 'Revenue',
            data: [
                @foreach(range(1, 12) as $month)
                    {{ $monthlyRevenue->where('month', $month)->first()->revenue ?? 0 }},
                @endforeach
            ],
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'R' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: R' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
@endsection
