<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EL CORAZÓN DEL SISTEMA — ver comentario en la migración.
 * El "informe técnico" es una vista renderizada de estos eventos
 * (ver App\Services\TechnicalReportBuilder, sugerido en el README).
 */
class WorkOrderEvent extends Model
{
    protected $fillable = [
        'work_order_id', 'user_id', 'type', 'content',
        'media_attachment_id', 'metadata', 'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaAttachment::class, 'media_attachment_id');
    }
}
