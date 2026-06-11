<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function publicUrl(): string
    {
        return Storage::disk()->url($this->path);
    }

    public function markdownLink(): string
    {
        return '![](' . $this->publicUrl() . ')';
    }
}
