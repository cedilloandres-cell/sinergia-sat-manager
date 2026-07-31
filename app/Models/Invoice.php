<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'code', 'client_id', 'work_order_id',
        'sri_access_key', 'sri_status', 'sri_xml', 'sri_authorization_number',
        'subtotal', 'tax', 'total', 'issued_at',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
