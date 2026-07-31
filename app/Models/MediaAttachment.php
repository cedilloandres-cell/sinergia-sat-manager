<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaAttachment extends Model
{
   protected $fillable = [
    'tenant_id', 'attachable_type', 'attachable_id', 'type', 'disk',
    'path', 'mime_type', 'size_bytes', 'lat', 'lng', 'uploaded_by',
];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function url(): string
    {
        return \Storage::disk($this->disk)->url($this->path);
    }
}
