<?php

namespace App\Services;

use App\Models\StorefrontSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Editable, escaped-by-default copy and presentation controls for public pages.
 *
 * Values are plain text only. Rich article content remains in the Blog CMS and
 * product/category descriptions remain in their dedicated CRUD modules.
 */
class StorefrontPageContent
{
    private const CACHE_KEY = 'storefront.page-content.v1';

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            $defaults = $this->defaults();
            $stored = StorefrontSetting::query()
                ->whereIn('key', array_keys($defaults))
                ->pluck('value', 'key')
                ->all();

            return array_replace($defaults, array_map(
                static fn ($value): string => (string) ($value ?? ''),
                $stored
            ));
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            // Homepage hero and catalogue.
            'page_home_hero_eyebrow' => 'تجهیزات آشپزخانه فرزین',
            'page_home_hero_title' => 'برای آشپزخانه‌ای',
            'page_home_hero_highlight' => 'هماهنگ با نیاز شما',
            'page_home_hero_description' => 'هود و سینک را با تصویر، قیمت و مشخصات واقعی مقایسه کنید؛ انتخاب را از مدل مناسب فضای خودتان شروع کنید.',
            'page_home_hero_primary_label' => 'مشاهده محصولات',
            'page_home_hero_secondary_label' => 'انتخاب دسته‌بندی',
            'page_home_categories_eyebrow' => 'دسته‌بندی محصولات فرزین',
            'page_home_categories_title' => 'از دسته‌ی مناسب شروع کنید.',
            'page_home_categories_description' => 'هود و سینک موردنیازتان را از میان دسته‌بندی‌های محصولات فرزین پیدا کنید.',
            'page_home_products_eyebrow' => 'کاتالوگ محصولات فرزین',
            'page_home_products_title' => 'مدل‌های منتخب فرزین',
            'page_home_products_description' => 'مدل‌های منتخب فرزین را ببینید و برای مقایسه مشخصات و انتخاب متناسب با آشپزخانه، وارد صفحه هر محصول شوید.',
            'page_home_best_sellers_title' => 'پرفروش‌ترین محصولات',
            'page_home_best_sellers_description' => 'انتخاب‌ها بر اساس تعداد محصولات ثبت‌شده در سفارش‌های پرداخت‌شده مرتب شده‌اند؛ مشخصات هر مدل را بررسی کنید.',
            'page_home_trust_eyebrow' => 'تجربه خرید از فرزین',
            'page_home_trust_title' => 'برای انتخاب، اطلاعات روشن در اختیار شماست.',
            'page_home_trust_1_title' => 'مشخصات پیش از خرید',
            'page_home_trust_1_description' => 'تصویر، قیمت و جزئیات هر مدل را در صفحه محصول بررسی کنید.',
            'page_home_trust_2_title' => 'سبد خرید یکپارچه',
            'page_home_trust_2_description' => 'محصول‌ها را به سبد اضافه کنید؛ سبد مهمان پس از ورود به حساب منتقل می‌شود.',
            'page_home_trust_3_title' => 'مدیریت سفارش‌ها',
            'page_home_trust_3_description' => 'پس از ورود، سفارش‌های ثبت‌شده را از حساب کاربری خود ببینید.',
            'page_home_guides_title' => 'از نیاز آشپزخانه‌تان شروع کنید.',
            'page_home_guides_description' => 'مسیر مناسب را انتخاب کنید تا مدل‌ها و مشخصات مرتبط را ببینید.',
            'page_home_hood_guide_title' => 'راهنمای انتخاب هود',
            'page_home_hood_guide_description' => 'نوع نصب، عرض و مشخصات مدل‌ها را بررسی کنید.',
            'page_home_hood_guide_cta' => 'مشاهده هودها',
            'page_home_sink_guide_title' => 'راهنمای انتخاب سینک',
            'page_home_sink_guide_description' => 'جنس، تعداد لگن و ابعاد مناسب را مقایسه کنید.',
            'page_home_sink_guide_cta' => 'مشاهده سینک‌ها',
            'page_home_journal_title' => 'قبل از انتخاب، آگاهانه‌تر ببینید.',
            'page_home_journal_description' => 'نکته‌ها و مقاله‌های مرتبط با هود و سینک؛ کوتاه، کاربردی و از دل تجربه‌ی تجهیزات آشپزخانه.',
            'page_home_final_title' => 'مدل مناسب آشپزخانه‌تان را پیدا کنید.',
            'page_home_final_description' => 'محصول‌ها را مقایسه کنید و برای بررسی مشخصات یا پرسش پیش از خرید، از مسیرهای ارتباطی فرزین کمک بگیرید.',
            'page_home_final_cta' => 'ورود به فروشگاه',

            // About page copy.
            'page_about_hero_eyebrow' => 'ABOUT FARZIN',
            'page_about_hero_title' => 'فرزین فرجی؛',
            'page_about_hero_highlight' => 'از تولید تا جزئیات آشپزخانه.',
            'page_about_hero_description' => 'فرزین فرجی در حوزه تولید هود و سینک آشپزخانه فعالیت می‌کند. این وب‌سایت برای معرفی مجموعه، نمایش محصولات و کمک به شناخت دقیق‌تر مدل‌ها و مشخصات آن‌ها طراحی شده است. جزئیات هر محصول را می‌توانید در کاتالوگ فرزین بررسی کنید.',
            'page_about_hero_cta' => 'مشاهده محصولات فرزین',
            'page_about_statement_eyebrow' => 'THE MAKER AND THE WORK',
            'page_about_statement_title' => 'تولید، محصول و تجربه‌ی استفاده.',
            'page_about_statement_body_1' => 'محصول برای ما فقط یک تصویر یا نام در کاتالوگ نیست؛ ابعاد، کاربرد و مشخصات آن باید برای خریدار قابل بررسی باشد. معرفی روشن محصول کمک می‌کند انتخاب بر پایه‌ی نیاز واقعی آشپزخانه انجام شود.',
            'page_about_statement_body_2' => 'در کنار معرفی مجموعه و مسیر کاری آن، کاتالوگ محصولات نیز بخشی از همین تجربه است: اطلاعات هر مدل باید کمک کند خریدار پیش از سفارش، تفاوت‌ها و تناسب آن را با فضای خود بررسی کند.',
            'page_about_factory_eyebrow' => 'FACTORY & PROCESS',
            'page_about_factory_title' => 'پشت محصول را هم بشناسید.',
            'page_about_factory_body_1' => 'برای شناخت یک هود یا سینک، ظاهر محصول تنها بخشی از ماجراست. ابعاد، نوع نصب، جنس، امکانات و شیوه نگهداری هم اهمیت دارند. معرفی فرزین قرار است مسیر آشنایی با مجموعه و محصولات را روشن‌تر کند.',
            'page_about_factory_body_2' => 'اطلاعات اختصاصی هر مدل باید با مشخصات فنی تأییدشده همان محصول همراه باشد؛ به همین دلیل ویژگی‌ها و جزئیات قابل‌بررسی در صفحه محصول ارائه می‌شوند.',
            'page_about_factory_cta' => 'دیدن کاتالوگ محصولات',
            'page_about_values_eyebrow' => 'OUR FOCUS',
            'page_about_values_title' => 'تمرکز مجموعه فرزین.',
            'page_about_value_1_title' => 'کاربرد',
            'page_about_value_1_description' => 'فرم و ظاهر باید در خدمت فضای شما باشد، نه اینکه فضا را تحت‌الشعاع قرار دهد.',
            'page_about_value_2_title' => 'مشخصات روشن',
            'page_about_value_2_description' => 'انتخاب متریال و ساخت خوب، چیزی است که ارزش یک محصول را در طول زمان حفظ می‌کند.',
            'page_about_value_3_title' => 'انتخاب مناسب',
            'page_about_value_3_description' => 'قیمت، مشخصات، موجودی و شرایط پرداخت باید قبل از تصمیم‌گیری روشن باشند.',
            'page_about_value_4_title' => 'مسیر تولید',
            'page_about_value_4_description' => 'خرید باید ساده و آرام باشد؛ از اولین مشاهده محصول تا سفارش نهایی.',
            'page_about_experience_eyebrow' => 'THE EXPERIENCE',
            'page_about_experience_title' => 'از کشف تا انتخاب.',
            'page_about_experience_description' => 'تجربه فرزین فقط به صفحه محصول محدود نمی‌شود. تمام مسیر برای انتخاب راحت‌تر طراحی شده است.',
            'page_about_step_1_title' => 'کشف',
            'page_about_step_1_description' => 'دسته‌بندی‌ها و مجموعه‌های مختلف را بررسی می‌کنید و محصول مناسب فضای خود را پیدا می‌کنید.',
            'page_about_step_2_title' => 'بررسی',
            'page_about_step_2_description' => 'قیمت، مشخصات، موجودی و در صورت وجود، شرایط خرید اقساطی محصول را می‌بینید.',
            'page_about_step_3_title' => 'انتخاب',
            'page_about_step_3_description' => 'محصول و ویژگی‌های موردنظر را انتخاب و وارد سبد خرید می‌کنید.',
            'page_about_step_4_title' => 'پرداخت',
            'page_about_step_4_description' => 'مسیر پرداخت متناسب با سفارش انتخاب می‌شود و شرایط اقساطی در صورت فعال بودن، شفاف نمایش داده خواهد شد.',
            'page_about_installment_eyebrow' => 'FLEXIBLE PAYMENT',
            'page_about_installment_title' => 'انتخاب خوب نباید فقط به بودجه امروز محدود شود.',
            'page_about_installment_description' => 'برخی محصولات فرزین می‌توانند با شرایط اقساطی تعریف‌شده توسط فروشگاه خریداری شوند. درصد پیش‌پرداخت، تعداد چک و فاصله سررسید برای هر محصول مشخص است.',
            'page_about_installment_cta' => 'مشاهده محصولات',
            'page_about_final_eyebrow' => 'فرزین',
            'page_about_final_title' => 'برای فضاهایی که قرار است ماندگار باشند.',
            'page_about_final_description' => 'مجموعه فرزین را ببین و محصولی را پیدا کن که فقط خانه را پر نکند؛ بلکه بخشی از شخصیت آن شود.',
            'page_about_final_shop_cta' => 'ورود به فروشگاه',
            'page_about_final_contact_cta' => 'تماس با ما',

            // Legal pages.
            'page_terms_title' => 'قوانین و شرایط استفاده',
            'page_terms_lead' => 'پیش از ساخت حساب یا ثبت سفارش، نکات زیر را مطالعه کنید.',
            'page_terms_section_1_title' => 'اطلاعات محصول',
            'page_terms_section_1_body' => 'مشخصات، تصاویر و قیمت هر کالا در صفحه همان محصول نمایش داده می‌شود. پیش از ثبت سفارش، مشخصات فنی و گزینه‌های قابل انتخاب را بررسی کنید. تصاویر برای معرفی محصول هستند و نمایش رنگ و جزئیات ممکن است با توجه به نمایشگر متفاوت باشد.',
            'page_terms_section_2_title' => 'حساب کاربری و اطلاعات سفارش',
            'page_terms_section_2_body' => 'هنگام ایجاد حساب یا ثبت سفارش، اطلاعات تماس و نشانی را دقیق وارد کنید. مسئولیت صحت اطلاعاتی که برای ارتباط و تحویل سفارش ثبت می‌کنید بر عهده کاربر است. حساب کاربری برای مشاهده سوابق سفارش و مدیریت اطلاعات استفاده می‌شود.',
            'page_terms_section_3_title' => 'ثبت سفارش و پرداخت',
            'page_terms_section_3_body' => 'ثبت اطلاعات در سبد خرید به‌تنهایی به معنی تکمیل خرید نیست. نتیجه ثبت سفارش و وضعیت پرداخت باید در صفحه مربوط به همان سفارش مشخص شود. تا زمانی که درگاه پرداخت در سایت فعال و تأیید نشده باشد، اطلاعات کارت یا رمز بانکی را در فرم‌های این سایت وارد نکنید.',
            'page_terms_section_4_title' => 'ارسال، ضمانت و خدمات پس از فروش',
            'page_terms_section_4_body' => 'شرایط ارسال، ضمانت، نصب و مرجوعی را پیش از خرید از طریق صفحه تماس با فرزین استعلام کنید. این صفحه جایگزین تعهد یا ضمانت‌نامه اختصاصی محصول نیست؛ شرایط قطعی هر کالا باید هنگام خرید به‌صورت روشن اعلام شود.',
            'page_terms_contact_title' => 'پرسشی درباره شرایط خرید دارید؟',
            'page_terms_contact_cta' => 'ارتباط با فرزین',

            'page_privacy_title' => 'حریم خصوصی',
            'page_privacy_lead' => 'اطلاعات حساب و سفارش شما برای ارائه خدمات فروشگاه استفاده می‌شود.',
            'page_privacy_section_1_title' => 'چه اطلاعاتی ثبت می‌شود؟',
            'page_privacy_section_1_body' => 'بسته به بخشی که استفاده می‌کنید، اطلاعاتی مانند نام، شماره موبایل، ایمیل اختیاری، نشانی تحویل، جزئیات سفارش و پیام‌هایی که از طریق فرم تماس ارسال می‌کنید در سامانه ثبت می‌شوند.',
            'page_privacy_section_2_title' => 'اطلاعات برای چه کاری استفاده می‌شود؟',
            'page_privacy_section_2_body' => 'این اطلاعات برای ایجاد و مدیریت حساب، ثبت و پیگیری سفارش، ارتباط درباره خرید و پاسخ به درخواست‌های پشتیبانی استفاده می‌شوند. اطلاعات حساب و سفارش برای نمایش سوابق مرتبط به همان کاربر به کار می‌روند.',
            'page_privacy_section_3_title' => 'پرداخت',
            'page_privacy_section_3_body' => 'اطلاعات محرمانه کارت بانکی را در پیام یا فرم تماس ارسال نکنید. در صورت فعال بودن پرداخت آنلاین، اطلاعات پرداخت را فقط در صفحه رسمی درگاه معرفی‌شده وارد کنید.',
            'page_privacy_section_4_title' => 'درخواست اصلاح اطلاعات',
            'page_privacy_section_4_body' => 'برای پیگیری خطا یا درخواست اصلاح اطلاعات تماس و حساب کاربری، از صفحه تماس با فرزین پیام بفرستید.',
            'page_privacy_contact_title' => 'درخواست یا پرسشی درباره اطلاعات حساب دارید؟',
            'page_privacy_contact_cta' => 'ارتباط با فرزین',

            // Public typography controls. Percentages are constrained in the CMS controller.
            'page_typography_font_scale' => '100',
            'page_typography_line_height' => '1.8',
            'page_typography_content_width' => '1280',

            // Public section switches; all enabled by default.
            'page_home_show_categories' => '1',
            'page_home_show_products' => '1',
            'page_home_show_trust' => '1',
            'page_home_show_guides' => '1',
            'page_home_show_journal' => '1',
            'page_home_show_final_cta' => '1',
            'page_about_show_statement' => '1',
            'page_about_show_factory' => '1',
            'page_about_show_values' => '1',
            'page_about_show_experience' => '1',
            'page_about_show_installment' => '1',
            'page_about_show_final' => '1',
        ];
    }
}
