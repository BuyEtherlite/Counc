<?php

namespace App\Models\Housing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Allocation extends Model
{
    use HasFactory;

    protected $table = 'housing_allocations';

    protected $fillable = [
        'housing_application_id',
        'property_id',
        'waiting_list_id',
        'allocated_by',
        'allocation_date',
        'move_in_date',
        'lease_start_date',
        'lease_end_date',
        'monthly_rent',
        'deposit_amount',
        'status',
        'terms_conditions',
        'special_conditions',
        'allocation_notes',
        'tenant_accepted_date',
        'keys_handed_date',
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'move_in_date' => 'date',
        'lease_start_date' => 'date',
        'lease_end_date' => 'date',
        'tenant_accepted_date' => 'date',
        'keys_handed_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'terms_conditions' => 'array',
        'special_conditions' => 'array',
    ];

    // Status constants
    const STATUS_OFFERED = 'offered';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_ACTIVE = 'active';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_EXPIRED = 'expired';

    public function application()
    {
        return $this->belongsTo(HousingApplication::class, 'housing_application_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function waitingListEntry()
    {
        return $this->belongsTo(WaitingList::class, 'waiting_list_id');
    }

    public function allocatedBy()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class);
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'offered' => 'warning',
            'accepted' => 'info',
            'declined' => 'secondary',
            'active' => 'success',
            'terminated' => 'danger',
            'expired' => 'dark',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired()
    {
        return $this->lease_end_date && $this->lease_end_date->isPast();
    }

    public function daysUntilExpiry()
    {
        if (!$this->lease_end_date) {
            return null;
        }
        
        return now()->diffInDays($this->lease_end_date, false);
    }

    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();

        // Update property status
        $this->property->update(['status' => Property::STATUS_OCCUPIED]);

        // Update waiting list status
        if ($this->waitingListEntry) {
            $this->waitingListEntry->update(['status' => WaitingList::STATUS_ALLOCATED]);
        }

        // Create tenant record
        Tenant::create([
            'allocation_id' => $this->id,
            'name' => $this->application->applicant_name,
            'email' => $this->application->applicant_email,
            'phone' => $this->application->applicant_phone,
            'id_number' => $this->application->applicant_id_number,
            'move_in_date' => $this->move_in_date,
        ]);
    }

    public function terminate($reason = null, $date = null)
    {
        $this->status = self::STATUS_TERMINATED;
        $this->save();

        // Update property status
        $this->property->update(['status' => Property::STATUS_AVAILABLE]);

        // Deactivate tenant
        if ($this->tenant) {
            $this->tenant->update([
                'status' => 'inactive',
                'move_out_date' => $date ?? now(),
                'termination_reason' => $reason,
            ]);
        }
    }
}