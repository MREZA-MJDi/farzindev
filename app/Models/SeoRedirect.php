<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoRedirect extends Model
{
    protected $fillable = [
        'source_path',
        'target_url',
        'status_code',
        'hits',
        'last_hit_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'last_hit_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
