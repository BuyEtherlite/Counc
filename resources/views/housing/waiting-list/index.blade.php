@extends('layouts.app')

@section('page-title', 'Housing Waiting List')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>📋 Housing Waiting List</h4>
        <div class="btn-group">
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('housing.waiting-list.recalculate') }}">
                    <i class="fas fa-sync me-2"></i>Recalculate Positions
                </a></li>
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkContactModal">
                    <i class="fas fa-envelope me-2"></i>Bulk Contact
                </a></li>
            </ul>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Active on List</h6>
                            <h3>{{ $stats['total_active'] }}</h3>
                        </div>
                        <i class="fas fa-list fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Contacted</h6>
                            <h3>{{ $stats['total_contacted'] }}</h3>
                        </div>
                        <i class="fas fa-phone fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Offered</h6>
                            <h3>{{ $stats['total_offered'] }}</h3>
                        </div>
                        <i class="fas fa-hand-holding-heart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Avg Wait (days)</h6>
                            <h3>{{ $stats['average_wait_time'] }}</h3>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="offered" {{ request('status') === 'offered' ? 'selected' : '' }}>Offered</option>
                        <option value="declined" {{ request('status') === 'declined' ? 'selected' : '' }}>Declined</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="housing_type" class="form-select">
                        <option value="">All Housing Types</option>
                        <option value="house" {{ request('housing_type') === 'house' ? 'selected' : '' }}>House</option>
                        <option value="apartment" {{ request('housing_type') === 'apartment' ? 'selected' : '' }}>Apartment</option>
                        <option value="townhouse" {{ request('housing_type') === 'townhouse' ? 'selected' : '' }}>Townhouse</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($waitingList->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Position</th>
                            <th>Applicant</th>
                            <th>Priority Score</th>
                            <th>Date Added</th>
                            <th>Housing Type</th>
                            <th>Last Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($waitingList as $entry)
                        <tr class="{{ $entry->position <= 10 ? 'table-warning' : '' }}">
                            <td>
                                <input type="checkbox" name="selected_ids[]" value="{{ $entry->id }}" class="form-check-input entry-checkbox">
                            </td>
                            <td>
                                <span class="badge bg-{{ $entry->position <= 5 ? 'danger' : ($entry->position <= 10 ? 'warning' : 'primary') }} fs-6">
                                    #{{ $entry->position }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $entry->application->applicant_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $entry->application->application_number }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $entry->priority_score }}</span>
                            </td>
                            <td>{{ $entry->date_added->format('M d, Y') }}</td>
                            <td>{{ ucfirst($entry->housing_type_preference ?? 'Any') }}</td>
                            <td>
                                {{ $entry->last_contacted ? $entry->last_contacted->format('M d, Y') : 'Never' }}
                                @if($entry->contact_attempts > 0)
                                    <br><small class="text-muted">({{ $entry->contact_attempts }} attempts)</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $entry->status_badge }}">
                                    {{ ucfirst($entry->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('housing.waiting-list.show', $entry) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($entry->status === 'active')
                                    <button class="btn btn-outline-success" onclick="contactEntry({{ $entry->id }})">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $waitingList->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-list fa-3x text-muted mb-3"></i>
                <h5>No Entries on Waiting List</h5>
                <p class="text-muted">The waiting list is currently empty.</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Contact Modal -->
<div class="modal fade" id="bulkContactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkContactForm" method="POST" action="{{ route('housing.waiting-list.bulk-contact') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Contact Method</label>
                        <select name="contact_method" class="form-select" required>
                            <option value="phone">Phone</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <div id="selectedEntries"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Contacts</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function contactEntry(id) {
    // Add AJAX call to mark as contacted
    console.log('Contact entry:', id);
}

document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.entry-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

document.getElementById('bulkContactModal').addEventListener('show.bs.modal', function() {
    const selected = document.querySelectorAll('.entry-checkbox:checked');
    const container = document.getElementById('selectedEntries');
    
    container.innerHTML = '';
    selected.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
    
    if (selected.length === 0) {
        alert('Please select entries to contact');
        return false;
    }
});
</script>
@endsection