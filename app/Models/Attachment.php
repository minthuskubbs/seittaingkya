<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'clinic_id', 'attachable_type', 'attachable_id', 'category',
        'path', 'original_name', 'mime', 'size', 'uploaded_by',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function isImage(): bool
    {
        return in_array($this->mime, ['image/png', 'image/jpeg', 'image/jpg']);
    }
}
