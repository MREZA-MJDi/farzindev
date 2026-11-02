<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedSmallInteger('width_cm')->nullable()->after('stock');
            $table->string('installation_type', 100)->nullable()->after('width_cm');
            $table->unsignedInteger('suction_power_m3h')->nullable()->after('installation_type');
            $table->decimal('noise_db', 5, 1)->nullable()->after('suction_power_m3h');
            $table->unsignedSmallInteger('power_watts')->nullable()->after('noise_db');
            $table->string('material', 120)->nullable()->after('power_watts');
            $table->unsignedSmallInteger('warranty_months')->nullable()->after('material');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn([
                'width_cm',
                'installation_type',
                'suction_power_m3h',
                'noise_db',
                'power_watts',
                'material',
                'warranty_months',
            ]);
        });
    }
};
