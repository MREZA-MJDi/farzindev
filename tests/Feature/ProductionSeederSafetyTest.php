<?php

namespace Tests\\Feature;

use Database\\Seeders\\DatabaseSeeder;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class ProductionSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_database_seeding_does_not_insert_preview_products(): void
    {
        $this->app['env'] = 'production';

        config([
            'farzin.admin.name' => 'Farzin Test Admin',
            'farzin.admin.email' => 'deploy-test-admin@example.test',
            'farzin.admin.password' => 'temporary-seeder-test-password',
        ]);

        // Invoke the seeder directly to test its production behavior without
        // triggering Artisan's interactive "Application in production" prompt.
        $this->app->make(DatabaseSeeder::class)->run();

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseHas('categories', ['slug' => 'hoods']);
        $this->assertDatabaseHas('categories', ['slug' => 'sinks']);
        $this->assertDatabaseHas('users', [
            'email' => 'deploy-test-admin@example.test',
            'role' => 'admin',
        ]);
    }
}
