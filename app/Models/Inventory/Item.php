<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Council;
use App\Models\Department;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category',
        'unit_of_measure',
        'current_stock',
        'minimum_stock',
        'maximum_stock',
        'unit_cost',
        'total_value',
        'location',
        'supplier_name',
        'supplier_contact',
        'last_restock_date',
        'expiry_date',
        'status',
        'council_id',
        'department_id'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'last_restock_date' => 'date',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DISCONTINUED = 'discontinued';

    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isLowStock()
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function isOverStock()
    {
        return $this->current_stock >= $this->maximum_stock;
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && $this->expiry_date <= now()->addDays($days);
    }

    public function updateStock($quantity, $type, $reason = null)
    {
        $oldStock = $this->current_stock;

        if ($type === 'in') {
            $this->current_stock += $quantity;
        } else {
            $this->current_stock -= $quantity;
        }

        $this->total_value = $this->current_stock * $this->unit_cost;
        $this->save();

        // Record stock movement
        $this->stockMovements()->create([
            'movement_type' => $type,
            'quantity' => $quantity,
            'previous_stock' => $oldStock,
            'new_stock' => $this->current_stock,
            'reason' => $reason,
            'moved_by' => auth()->id()
        ]);
    }

    public function getStockStatusAttribute()
    {
        if ($this->isLowStock()) {
            return 'low';
        } elseif ($this->isOverStock()) {
            return 'over';
        } else {
            return 'normal';
        }
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'low' => 'danger',
            'over' => 'warning',
            'normal' => 'success',
            default => 'secondary'
        };
    }
}