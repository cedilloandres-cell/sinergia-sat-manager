<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCatalogItem extends Model
{
    protected $table = 'service_catalog';

    protected $fillable = [
        'tenant_id', 'name', 'category', 'description', 'price',
        'estimated_minutes', 'dynamic_fields_schema', 'active',
    ];

    protected $casts = [
        'dynamic_fields_schema' => 'array',
        'active' => 'boolean',
    ];
}
