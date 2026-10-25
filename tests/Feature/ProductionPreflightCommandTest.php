<?php

namespace Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Schema;
use Tests\\TestCase;

class ProductionPreflightCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preflight_marks_the_missing_blog_table_as_a_critical_blocker(): void
    {
        Schema::dropIfExists('blog_posts');

        $this->artisan('farzin:preflight')
            ->expectsOutputToContain('Database table: blog_posts')
            ->expectsOutputToContain('FAIL')
            ->assertExitCode(1);
    }
}
