<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupPreviewCatalogue extends Command
{
    protected $signature = 'farzin:cleanup-preview-catalogue
        {--apply : Archive the listed preview products and hide the listed demo categories}';

    protected $description = 'Preview or safely archive known demo catalogue records without deleting order history';

    public function handle(): int
    {
        $products = $this->candidateProducts()->get();
        $categories = $this->candidateCategories()->get();

        $this->components->info('محصولات شناسایی‌شده برای خروج از کاتالوگ عمومی: ' . $products->count());

        if ($products->isNotEmpty()) {
            $this->table(
                ['شناسه', 'SKU', 'نام محصول', 'دسته', 'وضعیت فعلی', 'اقلام سفارش'],
                $products->map(fn (Product $product) => [
                    $product->id,
                    $product->sku ?: '—',
                    $product->name,
                    $product->category?->name ?: '—',
                    $product->status,
                    $product->order_items_count,
                ])->all()
            );
        }

        $this->components->info('دسته‌های آزمایشی یا نامرتبط: ' . $categories->count());

        if ($categories->isNotEmpty()) {
            $this->table(
                ['شناسه', 'نام دسته', 'Slug', 'وضعیت فعلی'],
                $categories->map(fn (Category $category) => [
                    $category->id,
                    $category->name,
                    $category->slug,
                    $category->is_active ? 'فعال' : 'غیرفعال',
                ])->all()
            );
        }

        if (! $this->option('apply')) {
            $this->components->warn('این فقط پیش‌نمایش است؛ هیچ داده‌ای تغییر نکرد. پس از بررسی فهرست، دستور را با --apply اجرا کنید.');
            return self::SUCCESS;
        }

        if ($products->isEmpty() && $categories->isEmpty()) {
            $this->components->info('رکوردی برای آرشیو یا مخفی‌کردن پیدا نشد.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($products, $categories): void {
            if ($products->isNotEmpty()) {
                Product::query()
                    ->whereKey($products->modelKeys())
                    ->update([
                        'status' => 'archived',
                        'stock' => 0,
                        'is_featured' => false,
                        'is_new' => false,
                        'show_in_home_gallery' => false,
                    ]);
            }

            if ($categories->isNotEmpty()) {
                Category::query()
                    ->whereKey($categories->modelKeys())
                    ->update(['is_active' => false]);
            }
        });

        $this->components->success('موارد فهرست‌شده آرشیو یا غیرفعال شدند. هیچ محصول، سفارش، تصویر یا سابقه‌ای حذف نشد.');

        return self::SUCCESS;
    }

    private function candidateProducts()
    {
        return Product::query()
            ->with('category:id,name,slug')
            ->withCount('orderItems')
            ->where(function ($query): void {
                $query
                    ->where('sku', 'like', '%SEED%')
                    ->orWhere('sku', 'like', '%DEMO%')
                    ->orWhere('sku', 'like', '%TEST%')
                    ->orWhere('slug', 'like', '%preview%')
                    ->orWhere('name', 'like', '%تی‌شرت%')
                    ->orWhere('name', 'like', '%تی شرت%')
                    ->orWhere('name', 'like', '%تیشرت%')
                    ->orWhere('name', 'like', '%t-shirt%')
                    ->orWhere('name', 'like', '%میز%')
                    ->orWhereHas('category', function ($category): void {
                        $category
                            ->whereIn('slug', ['furniture', 'chairs', 'tables', 'decor', 'accessories'])
                            ->orWhere('name', 'like', '%میز%');
                    });
            })
            ->orderBy('id');
    }

    private function candidateCategories()
    {
        return Category::query()
            ->where(function ($query): void {
                $query
                    ->whereIn('slug', ['furniture', 'chairs', 'tables', 'decor', 'accessories'])
                    ->orWhere('name', 'like', '%میز%');
            })
            ->orderBy('id');
    }
}
