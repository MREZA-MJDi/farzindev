<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smtp_settings', function (Blueprint $table) {
            $table->string('mailbox_address', 255)->nullable();
            $table->string('imap_host', 255)->nullable();
            $table->unsignedSmallInteger('imap_port')->default(993);
            $table->string('imap_encryption', 10)->default('ssl');
            $table->string('imap_username', 255)->nullable();
            $table->text('imap_password')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('smtp_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mailbox_address',
                'imap_host',
                'imap_port',
                'imap_encryption',
                'imap_username',
                'imap_password',
            ]);
        });
    }
};
