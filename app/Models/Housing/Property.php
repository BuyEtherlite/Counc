<?php

namespace App\Models\Housing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_number',
        'property_type',
        'address',
        'suburb',
        'bedrooms',
        'bathrooms',
        'size_sqm',
        'rental_amount',
        'status',
        'description',
        'amenities',
        'accessibility_features',
        'office_id',
        'gps_coordinates',
        'property_condition',
        'last_inspection_date',
        'next_inspection_due',
    ];

    protected $casts = [
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'size_sqm' => 'decimal:2',
        'rental_amount' => 'decimal:2',
        'amenities' => 'array',
        'accessibility_features' => 'array',
        'last_inspection_date' => 'date',
        'next_inspection_due' => 'date',
    ];

    // Status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_MAINTENANCE = 'under_maintenance';
    const STATUS_RENOVATION = 'under_renovation';
    const STATUS_CONDEMNED = 'condemned';

    // Property type constants
    const TYPE_HOUSE = 'house';
    const TYPE_APARTMENT = 'apartment';
    const TYPE_TOWNHOUSE = 'townhouse';
    const TYPE_FLAT = 'flat';
    const TYPE_STUDIO = 'studio';

    public function office()
    {
        return $this->belongsTo(\App\Models\Office::class);
    }

    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }

    public function currentAllocation()
    {
        return $this->hasOne(Allocation::class)->where('status', 'active')->latest();
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function getStatusBadgeAttribute()
    {
        $colors = [
            'available' => 'success',
            'occupied' => 'primary',
            'under_maintenance' => 'warning',
            'under_renovation' => 'info',
            'condemned' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getFullAddressAttribute()
    {
        return $this->address . ', ' . $this->suburb;
    }

    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isOccupied()
    {
        return $this->status === self::STATUS_OCCUPIED;
    }

    public function needsInspection()
    {
        return $this->next_inspection_due && $this->next_inspection_due->isPast();
    }
}