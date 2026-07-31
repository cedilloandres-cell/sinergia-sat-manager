<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleEvent extends Model
{
    protected $table = 'schedule_events';

    protected $fillable = [
        'tenant_id', 'technician_id', 'work_order_id',
        'title', 'starts_at', 'ends_at', 'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
