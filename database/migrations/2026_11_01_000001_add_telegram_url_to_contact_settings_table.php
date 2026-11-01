<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contact_settings', 'telegram_url')) {
            Schema::table('contact_settings', function (Blueprint $table): void {
                $table->string('telegram_url')->nullable()->after('instagram_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contact_settings', 'telegram_url')) {
            Schema::table('contact_settings', function (Blueprint $table): void {
                $table->dropColumn('telegram_url');
            });
        }
    }
};
