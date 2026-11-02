<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->string('eitaa_url')->nullable()->after('telegram_url');
            $table->string('warranty_summary', 500)->nullable()->after('hours');
            $table->string('shipping_summary', 500)->nullable()->after('warranty_summary');
            $table->string('installation_summary', 500)->nullable()->after('shipping_summary');
        });
    }

    public function down(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'eitaa_url',
                'warranty_summary',
                'shipping_summary',
                'installation_summary',
            ]);
        });
    }
};
