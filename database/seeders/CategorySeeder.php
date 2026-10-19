<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Hide only the known demo categories from the old furniture catalogue.
        Category::query()
            ->whereIn('slug', ['furniture', 'chairs', 'tables', 'decor', 'accessories'])
            ->update(['is_active' => false]);

        $categories = [
            [
                'name' => 'هود آشپزخانه',
                'slug' => 'hoods',
                'description' => 'انواع هود آشپزخانه برای تهویه بهتر، کاهش بو و هماهنگی با طراحی آشپزخانه.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'سینک ظرفشویی',
                'slug' => 'sinks',
                'description' => 'راهنمای انتخاب و مشاهده مدل‌های سینک ظرفشویی برای آشپزخانه‌های مدرن و کاربردی.',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
