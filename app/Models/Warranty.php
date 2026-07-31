<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Warranty extends Model
{
    protected $fillable = [
        'work_order_item_id', 'duration_days', 'expires_at', 'conditions',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(WorkOrderItem::class, 'work_order_item_id');
    }

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }
}
