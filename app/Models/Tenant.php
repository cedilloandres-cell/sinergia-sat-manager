<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'ruc', 'logo_path', 'branding', 'settings'];

    protected $casts = [
        'branding' => 'array',
        'settings' => 'array',
    ];
}
