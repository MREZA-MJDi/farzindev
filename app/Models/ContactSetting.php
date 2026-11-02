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
        'telegram_url',
        'eitaa_url',
        'address',
        'latitude',
        'longitude',
        'hours',
        'warranty_summary',
        'shipping_summary',
        'installation_summary',
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
