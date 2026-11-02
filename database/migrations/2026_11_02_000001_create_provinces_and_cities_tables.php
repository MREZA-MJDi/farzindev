<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table): void {
            // Preserve the stable administrative IDs supplied by the dataset.
            $table->unsignedBigInteger('id')->primary();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table): void {
            // City IDs are stable source IDs and fit in an unsigned BIGINT.
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('province_id')
                ->constrained('provinces')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('name', 120);
            $table->timestamps();

            $table->index(['province_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('provinces');
    }
};
