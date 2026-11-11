<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->string('content_title', 180)->nullable()->after('description');
            $table->text('content_body')->nullable()->after('content_title');
            $table->string('content_image', 2048)->nullable()->after('content_body');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['content_title', 'content_body', 'content_image']);
        });
    }
};
