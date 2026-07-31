<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only: no usar softDeletes ni permitir update() desde controllers.
 * Poblar exclusivamente vía Observer (ver README).
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenant_id', 'user_id', 'entity_type', 'entity_id',
        'field', 'old_value', 'new_value', 'created_at',
    ];
}
