<?php

namespace App\Models\Housing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class HousingApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'applicant_address',
        'applicant_id_number',
        'family_size',
        'monthly_income',
        'employment_status',
        'preferred_area',
        'housing_type_preference',
        'special_needs',
        'application_date',
        'status',
        'priority_score',
        'assessment_notes',
        'assessed_by',
        'office_id',
        'documents',
    ];

    protected $casts = [
        'application_date' => 'date',
        'documents' => 'array',
        'priority_score' => 'integer',
        'family_size' => 'integer',
        'monthly_income' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ON_WAITING_LIST = 'on_waiting_list';

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function office()
    {
        return $this->belongsTo(\App\Models\Office::class);
    }

    public function waitingListEntry()
    {
        return $this->hasOne(WaitingList::class);
    }

    public function allocation()
    {
        return $this->hasOne(Allocation::class);
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'under_review' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'on_waiting_list' => 'primary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function calculatePriorityScore()
    {
        $score = 0;
        
        // Family size scoring
        $score += min($this->family_size * 10, 50);
        
        // Income scoring (lower income = higher priority)
        if ($this->monthly_income < 2000) $score += 30;
        elseif ($this->monthly_income < 3000) $score += 20;
        elseif ($this->monthly_income < 4000) $score += 10;
        
        // Special needs
        if (!empty($this->special_needs)) $score += 20;
        
        // Time on waiting list
        if ($this->application_date) {
            $monthsWaiting = $this->application_date->diffInMonths(now());
            $score += min($monthsWaiting * 2, 40);
        }
        
        return $score;
    }
}