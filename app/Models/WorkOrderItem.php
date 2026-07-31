<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id', 'type', 'service_catalog_id', 'material_catalog_id',
        'description', 'quantity', 'unit_price', 'subtotal',
    ];

    protected static function booted(): void
    {
        static::saving(function (WorkOrderItem $item) {
            $item->subtotal = round($item->quantity * $item->unit_price, 2);
        });
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
