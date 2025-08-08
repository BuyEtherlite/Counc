<?php

namespace App\Models\Housing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'allocation_id',
        'name',
        'email',
        'phone',
        'id_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'move_in_date',
        'move_out_date',
        'status',
        'rent_balance',
        'deposit_balance',
        'payment_day',
        'termination_reason',
        'notes',
    ];

    protected $casts = [
        'move_in_date' => 'date',
        'move_out_date' => 'date',
        'rent_balance' => 'decimal:2',
        'deposit_balance' => 'decimal:2',
        'payment_day' => 'integer',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_EVICTED = 'evicted';

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }

    public function property()
    {
        return $this->hasOneThrough(Property::class, Allocation::class, 'id', 'id', 'allocation_id', 'property_id');
    }

    public function rentPayments()
    {
        return $this->hasMany(RentPayment::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'suspended' => 'warning',
            'evicted' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasOutstandingRent()
    {
        return $this->rent_balance > 0;
    }

    public function getMonthsInResidenceAttribute()
    {
        $endDate = $this->move_out_date ?? now();
        return $this->move_in_date ? $this->move_in_date->diffInMonths($endDate) : 0;
    }

    public function getNextRentDueDateAttribute()
    {
        if (!$this->payment_day) {
            return null;
        }

        $today = now();
        $nextDue = now()->day($this->payment_day);
        
        if ($nextDue->isPast()) {
            $nextDue->addMonth();
        }
        
        return $nextDue;
    }

    public function calculateRentDue()
    {
        // Get monthly rent from allocation
        $monthlyRent = $this->allocation->monthly_rent ?? 0;
        
        // Calculate based on current balance and monthly rent
        return $monthlyRent + $this->rent_balance;
    }
}