<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'council_id',
        'is_active',
        'modules_access',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'modules_access' => 'array',
    ];

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function offices()
    {
        return $this->hasMany(Office::class);
    }
}