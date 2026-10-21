<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('inventory_reserved_at')->nullable()->index();
            $table->timestamp('inventory_released_at')->nullable();
            $table->timestamp('inventory_committed_at')->nullable();
            $table->timestamp('inventory_issue_at')->nullable();
            $table->text('inventory_issue_reason')->nullable();
        });

        // Existing order stock is already managed operationally. Do not
        // retroactively change stock for historic orders during migration.
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['inventory_reserved_at']);
            $table->dropColumn([
                'inventory_reserved_at',
                'inventory_released_at',
                'inventory_committed_at',
                'inventory_issue_at',
                'inventory_issue_reason',
            ]);
        });
    }
};
