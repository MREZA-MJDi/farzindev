<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'show_in_home_gallery')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('show_in_home_gallery')
                    ->default(false)
                    ->after('is_new');
            });
        }

        // Preserve the current homepage gallery on existing installations:
        // active products that already have at least one image remain selected.
        DB::table('products')
            ->where('status', 'active')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('product_images')
                    ->whereColumn('product_images.product_id', 'products.id');
            })
            ->update(['show_in_home_gallery' => true]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'show_in_home_gallery')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('show_in_home_gallery');
            });
        }
    }
};
