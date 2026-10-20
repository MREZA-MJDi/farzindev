<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * These 50 records are a visual/content preview catalogue for the
     * standalone storefront. They are not a verified Farzin price list.
     *
     * Local/staging: activate them so the UI and real DB flows can be tested.
     * Production: keep them as drafts with zero stock until staff replace
     * model names, prices and specifications with verified company data.
     * Existing customer products and order history are never deleted.
     */
    public function run(): void
    {
        Product::query()
            ->where('sku', 'like', 'LIV-%')
            ->update([
                'status' => 'archived',
                'is_featured' => false,
                'is_new' => false,
            ]);

        if (app()->environment('testing')) {
            return;
        }

        $hoods = Category::query()->where('slug', 'hoods')->first();
        $sinks = Category::query()->where('slug', 'sinks')->first();

        if (! $hoods || ! $sinks) {
            return;
        }

        $publishPreview = app()->environment(['local', 'staging']);

        $hoodModels = [
            ['هود مورب شیشه‌ای', 90, 14900000],
            ['هود مورب شیشه‌ای', 60, 11900000],
            ['هود مخفی کابینتی', 70, 8900000],
            ['هود مخفی کابینتی', 80, 9900000],
            ['هود مخفی کابینتی', 90, 10900000],
            ['هود شومینه‌ای استیل', 90, 16900000],
            ['هود شومینه‌ای استیل', 60, 12900000],
            ['هود زیرکابینتی کم‌عمق', 60, 7900000],
            ['هود زیرکابینتی کم‌عمق', 80, 8900000],
            ['هود جزیره‌ای مینیمال', 90, 24900000],
            ['هود مورب شیشه‌ای تیره', 90, 17900000],
            ['هود مورب شیشه‌ای روشن', 90, 16900000],
            ['هود توکار یکپارچه', 70, 11900000],
            ['هود توکار یکپارچه', 80, 12900000],
            ['هود توکار یکپارچه', 90, 13900000],
            ['هود شومینه‌ای مشکی', 90, 18900000],
            ['هود شومینه‌ای مشکی', 60, 14900000],
            ['هود تخت زیرکابینتی', 60, 7400000],
            ['هود تخت زیرکابینتی', 90, 9400000],
            ['هود مورب با پنل ساده', 80, 13900000],
            ['هود مورب با پنل ساده', 90, 15900000],
            ['هود توکار باریک', 70, 9900000],
            ['هود توکار باریک', 90, 11900000],
            ['هود دکوراتیو دیواری', 90, 19900000],
            ['هود دکوراتیو دیواری', 80, 17900000],
        ];

        $sinkModels = [
            ['سینک استیل تک‌لگن', 'تک‌لگن', 6900000],
            ['سینک استیل دولگن', 'دولگن', 8900000],
            ['سینک استیل دولگن عمیق', 'دولگن عمیق', 10900000],
            ['سینک گرانیتی مشکی', 'گرانیتی', 16900000],
            ['سینک گرانیتی روشن', 'گرانیتی', 15900000],
            ['سینک روکار استیل', 'روکار', 7900000],
            ['سینک توکار استیل', 'توکار', 9900000],
            ['سینک زیرکار مینیمال', 'زیرکار', 14900000],
            ['سینک باکسی استیل', 'باکسی', 13900000],
            ['سینک باکسی عمیق', 'باکسی عمیق', 15900000],
            ['سینک استیل یک‌لگن با سینی', 'تک‌لگن + سینی', 9500000],
            ['سینک استیل دولگن با سینی', 'دولگن + سینی', 11900000],
            ['سینک گوشه‌ای جمع‌وجور', 'گوشه‌ای', 9900000],
            ['سینک گرانیتی خاکستری', 'گرانیتی', 16900000],
            ['سینک گرانیتی کرم', 'گرانیتی', 16900000],
            ['سینک باکسی مشکی', 'باکسی', 17900000],
            ['سینک باکسی استیل برس‌خورده', 'باکسی برس‌خورده', 16900000],
            ['سینک تک‌لگن عمیق', 'تک‌لگن عمیق', 9900000],
            ['سینک دولگن فشرده', 'دولگن فشرده', 8900000],
            ['سینک توکار لبه‌باریک', 'توکار', 12900000],
            ['سینک استیل ضداثر انگشت', 'استیل', 13900000],
            ['سینک گرانیتی سفید', 'گرانیتی', 17900000],
            ['سینک ظرفشویی چندمنظوره', 'چندمنظوره', 21900000],
            ['سینک با سینی جمع‌وجور', 'با سینی', 8900000],
            ['سینک مینیمال تک‌لگن', 'مینیمال', 11900000],
        ];

        $products = [];

        foreach ($hoodModels as $index => [$name, $width, $price]) {
            $number = $index + 1;
            $products[] = [
                'category_id' => $hoods->id,
                'name' => "{$name} {$width} سانتی‌متر — مدل H" . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'slug' => 'farzin-hood-preview-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'sku' => 'FZ-SEED-HOOD-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'short_description' => 'اطلاعات نمایشی برای پیش‌نمایش فروشگاه؛ مشخصات نهایی باید با مدل رسمی تطبیق داده شود.',
                'description' => 'این رکورد برای بررسی چیدمان، تصاویر و فرایند مدیریت کاتالوگ ساخته شده است. پیش از انتشار نهایی، مشخصات رسمی، ابعاد، امکانات و قیمت تأییدشده شرکت را در پنل مدیریت جایگزین کنید.',
                'price' => $price,
                'compare_at_price' => null,
                'stock' => $publishPreview ? 8 + ($number % 9) : 0,
                'status' => $publishPreview ? 'active' : 'draft',
                'is_featured' => $number <= 8,
                'is_new' => $number >= 18 && $number <= 25,
                'meta_title' => "{$name} فرزین — مدل H" . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'meta_description' => 'پیش‌نمایش رکورد کاتالوگ هود فرزین؛ مشخصات نهایی و قیمت باید تأیید شوند.',
            ];
        }

        foreach ($sinkModels as $index => [$name, $style, $price]) {
            $number = $index + 1;
            $products[] = [
                'category_id' => $sinks->id,
                'name' => "{$name} — مدل S" . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'slug' => 'farzin-sink-preview-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'sku' => 'FZ-SEED-SINK-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'short_description' => "پیش‌نمایش کاتالوگ سینک فرزین، سبک {$style}؛ اطلاعات رسمی هنوز باید تأیید شود.",
                'description' => 'این رکورد جهت پیش‌نمایش رابط فروشگاه و پنل مدیریت ساخته شده است. پیش از انتشار نهایی، جنس، ابعاد، تعداد لگن، روش نصب، اقلام همراه و قیمت رسمی را در پنل جایگزین کنید.',
                'price' => $price,
                'compare_at_price' => null,
                'stock' => $publishPreview ? 7 + ($number % 11) : 0,
                'status' => $publishPreview ? 'active' : 'draft',
                'is_featured' => $number <= 8,
                'is_new' => $number >= 18 && $number <= 25,
                'meta_title' => "{$name} فرزین — مدل S" . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'meta_description' => 'پیش‌نمایش رکورد کاتالوگ سینک فرزین؛ مشخصات نهایی و قیمت باید تأیید شوند.',
            ];
        }

        foreach ($products as $data) {
            Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                $data
            );
        }
    }
}
