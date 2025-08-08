<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'id_number',
        'customer_type',
        'status',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    /**
     * Get the full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get all contacts for this customer.
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Get all interactions for this customer.
     */
    public function interactions()
    {
        return $this->hasMany(CustomerInteraction::class);
    }
}
