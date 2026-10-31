<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('seo_robots', 32)->default('inherit');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->string('seo_robots', 32)->default('inherit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['canonical_url', 'og_title', 'og_description', 'seo_robots']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['canonical_url', 'og_title', 'og_description', 'seo_robots']);
        });
    }
};
