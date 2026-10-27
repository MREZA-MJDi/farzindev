<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductionPreflightCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_marks_a_missing_variant_gallery_pivot_as_a_critical_blocker(): void
    {
        Schema::dropIfExists('product_variant_images');

        $this->artisan('farzin:preflight')
            ->expectsOutputToContain('Database table: product_variant_images')
            ->expectsOutputToContain('Database column: product_variant_images.product_variant_id')
            ->assertExitCode(1);
    }

    public function test_preflight_checks_media_library_columns_used_by_the_product_editor(): void
    {
        Schema::table('product_images', function ($table): void {
            $table->dropForeign(['media_id']);
            $table->dropIndex(['media_id']);
            $table->dropColumn('media_id');
        });

        $this->artisan('farzin:preflight')
            ->expectsOutputToContain('Database column: product_images.media_id')
            ->assertExitCode(1);
    }

    public function test_preflight_marks_the_missing_blog_table_as_a_critical_blocker(): void
    {
        Schema::dropIfExists('blog_posts');

        $this->artisan('farzin:preflight')
            ->expectsOutputToContain('Database table: blog_posts')
            ->assertExitCode(1);
    }
}
