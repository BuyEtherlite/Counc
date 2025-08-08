<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Council extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'contact_info',
        'website',
        'logo',
        'is_primary',
        'settings',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'settings' => 'array',
    ];

    public function offices()
    {
        return $this->hasMany(Office::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}