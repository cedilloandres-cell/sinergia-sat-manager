<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalReport extends Model
{
    protected $fillable = [
        'work_order_id', 'generated_html', 'content_html',
        'status', 'pdf_path', 'sent_at',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
