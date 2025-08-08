<?php

namespace App\Models\Housing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaitingList extends Model
{
    use HasFactory;

    protected $table = 'housing_waiting_list';

    protected $fillable = [
        'housing_application_id',
        'position',
        'priority_score',
        'date_added',
        'preferred_areas',
        'housing_type_preference',
        'special_requirements',
        'status',
        'last_contacted',
        'contact_attempts',
        'notes',
    ];

    protected $casts = [
        'date_added' => 'date',
        'preferred_areas' => 'array',
        'last_contacted' => 'date',
        'contact_attempts' => 'integer',
        'priority_score' => 'integer',
        'position' => 'integer',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_OFFERED = 'offered';
    const STATUS_DECLINED = 'declined';
    const STATUS_ALLOCATED = 'allocated';
    const STATUS_REMOVED = 'removed';

    public function application()
    {
        return $this->belongsTo(HousingApplication::class, 'housing_application_id');
    }

    public function allocation()
    {
        return $this->hasOne(Allocation::class);
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'active' => 'primary',
            'contacted' => 'info',
            'offered' => 'warning',
            'declined' => 'secondary',
            'allocated' => 'success',
            'removed' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function updatePosition()
    {
        // Recalculate position based on priority score and date added
        $higherPriorityCount = self::where('priority_score', '>', $this->priority_score)
            ->orWhere(function($query) {
                $query->where('priority_score', $this->priority_score)
                      ->where('date_added', '<', $this->date_added);
            })
            ->where('status', 'active')
            ->count();
            
        $this->position = $higherPriorityCount + 1;
        $this->save();
    }

    public static function recalculateAllPositions()
    {
        $activeEntries = self::where('status', 'active')
            ->orderBy('priority_score', 'desc')
            ->orderBy('date_added', 'asc')
            ->get();

        foreach ($activeEntries as $index => $entry) {
            $entry->position = $index + 1;
            $entry->save();
        }
    }

    public function canBeOffered($propertyType = null, $area = null)
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        // Check property type preference
        if ($propertyType && $this->housing_type_preference) {
            if ($this->housing_type_preference !== $propertyType) {
                return false;
            }
        }

        // Check area preference
        if ($area && $this->preferred_areas) {
            if (!in_array($area, $this->preferred_areas)) {
                return false;
            }
        }

        return true;
    }
}