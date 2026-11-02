<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->string('product_model', 120)->nullable()->after('subject');
            $table->unsignedSmallInteger('width_cm')->nullable()->after('product_model');
            $table->boolean('callback_requested')->default(false)->after('width_cm');
            $table->string('attachment_path')->nullable()->after('callback_requested');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table): void {
            $table->dropColumn([
                'product_model',
                'width_cm',
                'callback_requested',
                'attachment_path',
            ]);
        });
    }
};
