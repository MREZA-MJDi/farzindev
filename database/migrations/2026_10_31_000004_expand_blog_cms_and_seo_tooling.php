<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->longText('body_html')->nullable()->after('body');
            $table->string('cover_image_alt', 255)->nullable()->after('cover_image');
            $table->string('focus_keyword', 160)->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('focus_keyword');
            $table->string('og_title', 255)->nullable()->after('canonical_url');
            $table->string('og_description', 320)->nullable()->after('og_title');
            $table->string('seo_robots', 32)->default('inherit')->after('og_description');
            $table->string('schema_type', 32)->default('BlogPosting')->after('seo_robots');
            $table->boolean('is_featured')->default(false)->after('schema_type');
        });

        Schema::create('blog_terms', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 16);
            $table->string('name', 160);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('seo_robots', 32)->default('inherit');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['type', 'slug']);
            $table->index(['type', 'is_active', 'sort_order']);
        });

        Schema::create('blog_post_terms', function (Blueprint $table): void {
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('blog_term_id')->constrained('blog_terms')->cascadeOnDelete();
            $table->primary(['blog_post_id', 'blog_term_id']);
            $table->index('blog_term_id');
            $table->timestamps();
        });

        Schema::create('seo_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path', 255)->unique();
            $table->string('target_url', 2048);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['is_active', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_redirects');
        Schema::dropIfExists('blog_post_terms');
        Schema::dropIfExists('blog_terms');

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn([
                'body_html',
                'cover_image_alt',
                'focus_keyword',
                'canonical_url',
                'og_title',
                'og_description',
                'seo_robots',
                'schema_type',
                'is_featured',
            ]);
        });
    }
};
