<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $fillable = [
        'phone',
        'email',
        'whatsapp',
        'instagram_url',
        'address',
        'hours',
        'phone_label',
        'email_label',
        'support_label',
        'online_label',
        'support_description',
        'online_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
