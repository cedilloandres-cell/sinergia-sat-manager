<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'tenant_id', 'company_id', 'first_name', 'last_name',
        'phone', 'whatsapp', 'email', 'role_in_company',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
