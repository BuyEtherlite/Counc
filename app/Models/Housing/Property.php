<?php

namespace App\Models\Housing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'housing_properties';

    protected $fillable = [
        'property_code',
        'address',
        'suburb',
        'city',
        'postal_code',
        'property_type',
        'bedrooms',
        'bathrooms',
        'size_sqm',
        'rental_amount',
        'deposit_amount',
        'status',
        'description',
        'amenities',
        'coordinates',
        'maintenance_notes',
        'council_id',
        'department_id',
        'office_id'
    ];

    protected $casts = [
        'amenities' => 'array',
        'coordinates' => 'array',
        'rental_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RESERVED = 'reserved';

    const TYPE_HOUSE = 'house';
    const TYPE_FLAT = 'flat';
    const TYPE_TOWNHOUSE = 'townhouse';
    const TYPE_ROOM = 'room';

    public function council()
    {
        return $this->belongsTo(\App\Models\Council::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

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
        return $this->hasOne(Allocation::class)->where('status', 'active');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(\App\Models\MaintenanceRequest::class);
    }

    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isOccupied()
    {
        return $this->status === self::STATUS_OCCUPIED;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'success',
            self::STATUS_OCCUPIED => 'primary',
            self::STATUS_MAINTENANCE => 'warning',
            self::STATUS_RESERVED => 'info',
            default => 'secondary'
        };
    }

    public function getFormattedAddressAttribute()
    {
        return $this->address . ', ' . $this->suburb . ', ' . $this->city . ' ' . $this->postal_code;
    }
}
