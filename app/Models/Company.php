<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = ['tenant_id', 'name', 'ruc', 'address', 'phone', 'email', 'notes'];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
