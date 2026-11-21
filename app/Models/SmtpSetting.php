<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    protected $fillable = [
        'host', 'port', 'encryption', 'username', 'password',
        'from_address', 'from_name', 'enabled',
        'imap_host', 'imap_port', 'imap_encryption', 'imap_username',
        'imap_password', 'mailbox_address',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'imap_password' => 'encrypted',
            'enabled' => 'boolean',
            'port' => 'integer',
            'imap_port' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }
}
