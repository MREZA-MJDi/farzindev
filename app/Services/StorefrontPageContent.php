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
    private const CACHE_KEY = 'storefront.page-content.v2';

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

            $values = array_replace($defaults, array_map(
                static fn ($value): string => (string) ($value ?? ''),
                $stored
            ));

            // Translate only the legacy built-in labels. Custom editor copy is
            // preserved verbatim so the content manager remains authoritative.
            $legacyLabels = [
                'page_home_technology_eyebrow' => [
                    'ENGINEERING / DETAIL / PERFORMANCE' => 'مهندسی • جزئیات • عملکرد',
                ],
                'page_about_hero_eyebrow' => [
                    'ABOUT FARZIN' => 'درباره فرزین',
                ],
                'page_about_statement_eyebrow' => [
                    'THE MAKER AND THE WORK' => 'نگاه ما به انتخاب محصول',
                ],
                'page_about_factory_eyebrow' => [
                    'FACTORY & PROCESS' => 'از جستجو تا انتخاب',
                ],
                'page_about_values_eyebrow' => [
                    'OUR FOCUS' => 'آنچه برای ما مهم است',
                ],
                'page_about_experience_eyebrow' => [
                    'THE EXPERIENCE' => 'تجربه خرید',
                ],
                'page_about_installment_eyebrow' => [
                    'FLEXIBLE PAYMENT' => 'شرایط پرداخت',
                ],
            ];

            foreach ($legacyLabels as $key => $translations) {
                $values[$key] = $translations[$values[$key]] ?? $values[$key];
            }

            $legacyCopy = [
                'page_about_factory_title' => [
                    'پشت محصول را هم بشناسید.' => 'چطور انتخاب را ساده‌تر می‌کنیم؟',
                ],
                'page_about_factory_body_1' => [
                    'برای شناخت یک هود یا سینک، ظاهر محصول تنها بخشی از ماجراست. ابعاد، نوع نصب، جنس، امکانات و شیوه نگهداری هم اهمیت دارند. معرفی فرزین قرار است مسیر آشنایی با مجموعه و محصولات را روشن‌تر کند.'
                        => 'برای هود، نوع نصب، عرض، قدرت مکش و میزان صدا را بررسی کنید؛ برای اجاق گاز، ابعاد برش، تعداد شعله و امکانات ایمنی؛ و برای سینک، روش نصب، تعداد لگن، ابعاد و جنس. این معیارها کمک می‌کنند محصولات را با فضای آشپزخانه خود تطبیق دهید.',
                ],
                'page_about_factory_body_2' => [
                    'اطلاعات اختصاصی هر مدل باید با مشخصات فنی تأییدشده همان محصول همراه باشد؛ به همین دلیل ویژگی‌ها و جزئیات قابل‌بررسی در صفحه محصول ارائه می‌شوند.'
                        => 'مشخصات فنی، قیمت و وضعیت موجودی هر مدل را در صفحه همان محصول بررسی کنید. در صورت وجود ابهام، پیش از ثبت سفارش از راه‌های ارتباطی فروشگاه سؤال کنید؛ ویژگی‌های هر کالا باید بر اساس اطلاعات ثبت‌شده همان مدل سنجیده شود.',
                ],
                'page_about_value_4_title' => [
                    'مسیر تولید' => 'پشتیبانی پیش از خرید',
                ],
                'page_about_value_4_description' => [
                    'خرید باید ساده و آرام باشد؛ از اولین مشاهده محصول تا سفارش نهایی.' => 'برای پرسش درباره مدل، مشخصات و مراحل سفارش از راه‌های ارتباطی فروشگاه استفاده کنید.',
                ],
            ];

            foreach ($legacyCopy as $key => $translations) {
                $values[$key] = $translations[$values[$key] ?? ''] ?? ($values[$key] ?? '');
            }

            // The former development brand was "Farzin". Keep old CMS values
            // readable while migrating the public identity to Faraji Home.
            foreach ($values as &$value) {
                $value = str_ireplace(
                    ['فرزین فرجی', 'فرزین', 'FARZIN', 'Farzin'],
                    ['فرجی‌هوم', 'فرجی‌هوم', 'FARAJI HOME', 'Faraji Home'],
                    $value
                );
            }
            unset($value);

            return $values;
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
            'page_home_hero_eyebrow' => 'فرجی‌هوم | تجهیزات آشپزخانه',
            'page_home_hero_title' => 'هود، سینک و اجاق گاز، مستقیم از کارخانه',
            'page_home_hero_highlight' => '',
            'page_home_hero_description' => 'مشخصات فنی، قیمت و وضعیت موجودی مدل‌های هود، سینک و اجاق گاز را بررسی کنید.',
            'page_home_hero_image_url' => '',
            'page_home_enamad_url' => '',
            'page_home_warranty_text' => '',
            'page_home_reviews_text' => '',
            'page_home_hero_primary_label' => 'مشاهده محصولات',
            'page_home_hero_secondary_label' => '',
            'page_home_technology_eyebrow' => 'مهندسی • جزئیات • عملکرد',
            'page_home_technology_title' => 'مشخصات محصول را پیش از خرید بررسی کنید',
            'page_home_technology_body' => 'در انتخاب تجهیزات آشپزخانه، ظاهر تنها بخشی از تصمیم است. ابعاد نصب، نوع کاربری، جنس، امکانات و شیوه نگهداری هر محصول را بررسی کنید و مشخصات ثبت‌شده همان مدل را مبنای مقایسه قرار دهید.',
            'page_home_technology_cta' => 'آشنایی با فرجی‌هوم',
            'page_home_technology_image_url' => '',
            'page_home_show_technology' => '1',
            'page_home_categories_eyebrow' => 'دسته‌بندی تجهیزات آشپزخانه',
            'page_home_categories_title' => 'اول دسته مناسب را پیدا کنید.',
            'page_home_categories_description' => 'از میان دسته‌بندی‌های فعال، گروه محصول موردنیازتان را انتخاب کنید و مدل‌ها را بر اساس مشخصات و فضای نصب مقایسه کنید.',
            'page_home_products_eyebrow' => 'محصولات آشپزخانه',
            'page_home_products_title' => 'محصولات منتخب برای آشپزخانه شما',
            'page_home_products_description' => 'مدل‌ها را با توجه به قیمت، موجودی، تصویر و مشخصات اختصاصی هر محصول بررسی کنید.',
            'page_home_best_sellers_title' => 'پرفروش‌ترین محصولات',
            'page_home_best_sellers_description' => 'انتخاب‌ها بر اساس تعداد محصولات ثبت‌شده در سفارش‌های پرداخت‌شده مرتب شده‌اند؛ مشخصات هر مدل را بررسی کنید.',
            'page_home_trust_eyebrow' => 'خرید از فرجی‌هوم',
            'page_home_trust_title' => 'اطلاعات مهم، پیش از تصمیم‌گیری',
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
            'page_home_gas_guide_title' => 'راهنمای انتخاب اجاق گاز صفحه‌ای',
            'page_home_gas_guide_description' => 'ابعاد برش، تعداد شعله، جنس صفحه، نوع شبکه و امکانات ایمنی مدل‌ها را با فضای آشپزخانه تطبیق دهید.',
            'page_home_gas_guide_cta' => 'مشاهده اجاق گازها',
            'page_home_journal_title' => 'راهنمای انتخاب، نصب و نگهداری',
            'page_home_journal_description' => 'راهنمای انتخاب، نصب و نگهداری تجهیزات آشپزخانه؛ برای اینکه پیش از خرید، معیارهای مهم را بشناسید.',
            'page_home_final_title' => 'مدل مناسب آشپزخانه‌تان را پیدا کنید.',
            'page_home_final_description' => 'برای بررسی مشخصات محصول یا پرسش درباره سفارش، از راه‌های ارتباطی فروشگاه استفاده کنید.',
            'page_home_final_cta' => 'ورود به فروشگاه',

            'page_home_banner_1_eyebrow' => '',
            'page_home_banner_1_title' => '',
            'page_home_banner_1_description' => '',
            'page_home_banner_1_button_label' => '',
            'page_home_banner_1_button_url' => '',
            'page_home_banner_1_image_url' => '',
            'page_home_banner_2_eyebrow' => '',
            'page_home_banner_2_title' => '',
            'page_home_banner_2_description' => '',
            'page_home_banner_2_button_label' => '',
            'page_home_banner_2_button_url' => '',
            'page_home_banner_2_image_url' => '',
            'page_home_banner_3_eyebrow' => '',
            'page_home_banner_3_title' => '',
            'page_home_banner_3_description' => '',
            'page_home_banner_3_button_label' => '',
            'page_home_banner_3_button_url' => '',
            'page_home_banner_3_image_url' => '',
            'page_home_banner_4_eyebrow' => '',
            'page_home_banner_4_title' => '',
            'page_home_banner_4_description' => '',
            'page_home_banner_4_button_label' => '',
            'page_home_banner_4_button_url' => '',
            'page_home_banner_4_image_url' => '',

            // About page copy.
            'page_about_hero_eyebrow' => 'درباره فرجی‌هوم',
            'page_about_hero_title' => 'فرجی‌هوم؛',
            'page_about_hero_highlight' => 'همراه انتخاب بهتر تجهیزات آشپزخانه.',
            'page_about_hero_description' => 'فرجی‌هوم فروشگاه تخصصی تجهیزات آشپزخانه است؛ جایی برای مشاهده و مقایسه مدل‌های هود، اجاق گاز صفحه‌ای، سینک و سایر محصولات موجود از برندهای متنوع. تلاش می‌کنیم تصویر، قیمت و مشخصات هر کالا را کنار هم قرار دهیم تا پیش از خرید، انتخاب دقیق‌تری داشته باشید.',
            'page_about_hero_cta' => 'مشاهده محصولات',
            'page_about_statement_eyebrow' => 'نگاه ما به انتخاب محصول',
            'page_about_statement_title' => 'انتخاب خوب، از اطلاعات روشن شروع می‌شود.',
            'page_about_statement_body_1' => 'در خرید تجهیزات آشپزخانه، ظاهر محصول تنها معیار نیست. ابعاد نصب، نوع کاربری، جنس، امکانات و خدمات برند اهمیت دارند. مقایسه روشن این ویژگی‌ها کمک می‌کند محصولی متناسب با فضای واقعی و شیوه استفاده خود انتخاب کنید.',
            'page_about_statement_body_2' => 'فرجی‌هوم محصولات برندهای گوناگون را در یک مسیر خرید آنلاین گرد هم می‌آورد؛ با دسته‌بندی روشن، صفحه اختصاصی هر محصول و راه‌های ارتباطی برای پرسش پیش از خرید.',
            'page_about_factory_eyebrow' => 'از جستجو تا انتخاب',
            'page_about_factory_title' => 'چطور انتخاب را ساده‌تر می‌کنیم؟',
            'page_about_factory_body_1' => 'برای هود، نوع نصب، عرض، قدرت مکش و میزان صدا را بررسی کنید؛ برای اجاق گاز، ابعاد برش، تعداد شعله و امکانات ایمنی؛ و برای سینک، روش نصب، تعداد لگن، ابعاد و جنس. این معیارها کمک می‌کنند محصولات را با فضای آشپزخانه خود تطبیق دهید.',
            'page_about_factory_body_2' => 'مشخصات فنی، قیمت و وضعیت موجودی هر مدل را در صفحه همان محصول بررسی کنید. در صورت وجود ابهام، پیش از ثبت سفارش از راه‌های ارتباطی فروشگاه سؤال کنید؛ ویژگی‌های هر کالا باید بر اساس اطلاعات ثبت‌شده همان مدل سنجیده شود.',
            'page_about_factory_cta' => 'مشاهده محصولات',
            'page_about_values_eyebrow' => 'آنچه برای ما مهم است',
            'page_about_values_title' => 'خریدی که با نیاز شما شروع می‌شود.',
            'page_about_value_1_title' => 'تناسب با فضا',
            'page_about_value_1_description' => 'ابعاد، نوع نصب و اندازه فضای کابینت را پیش از انتخاب در نظر بگیرید.',
            'page_about_value_2_title' => 'مشخصات روشن',
            'page_about_value_2_description' => 'تفاوت مدل‌ها را با مشخصات فنی ثبت‌شده همان محصول بررسی کنید.',
            'page_about_value_3_title' => 'تنوع برند و مدل',
            'page_about_value_3_description' => 'محصولات موجود را بر اساس دسته، برند، قیمت و ویژگی‌های قابل مقایسه بررسی کنید.',
            'page_about_value_4_title' => 'پشتیبانی پیش از خرید',
            'page_about_value_4_description' => 'برای پرسش درباره مدل، مشخصات و مراحل سفارش از راه‌های ارتباطی فروشگاه استفاده کنید.',
            'page_about_experience_eyebrow' => 'تجربه خرید',
            'page_about_experience_title' => 'مسیر خرید روشن و ساده',
            'page_about_experience_description' => 'از انتخاب دسته مناسب تا بررسی مشخصات، ثبت سفارش و پیگیری آن، اطلاعات هر مرحله به‌صورت روشن در دسترس شماست.',
            'page_about_step_1_title' => 'جستجو',
            'page_about_step_1_description' => 'دسته و برند موردنظر را انتخاب کنید و مدل‌های مرتبط را ببینید.',
            'page_about_step_2_title' => 'مقایسه',
            'page_about_step_2_description' => 'تصویر، قیمت، موجودی و ویژگی‌های فنی مدل‌ها را در صفحه محصول بررسی کنید.',
            'page_about_step_3_title' => 'ثبت انتخاب',
            'page_about_step_3_description' => 'مدل و گزینه‌های در دسترس را انتخاب و به سبد خرید اضافه کنید.',
            'page_about_step_4_title' => 'پیگیری',
            'page_about_step_4_description' => 'جزئیات و وضعیت سفارش را از حساب کاربری یا راه‌های ارتباطی فروشگاه دنبال کنید.',
            'page_about_installment_eyebrow' => 'شرایط پرداخت',
            'page_about_installment_title' => 'روش پرداخت متناسب با شرایط هر محصول را بررسی کنید.',
            'page_about_installment_description' => 'چنانچه خرید اقساطی برای محصولی فعال باشد، شرایط همان مدل در صفحه محصول یا هنگام ثبت سفارش نمایش داده می‌شود. پیش از پرداخت، مبلغ پیش‌پرداخت، تعداد اقساط و شرایط را با دقت بررسی کنید.',
            'page_about_installment_cta' => 'مشاهده محصولات',
            'page_about_final_eyebrow' => 'فرجی‌هوم',
            'page_about_final_title' => 'برای انتخاب بهتر تجهیزات آشپزخانه، از همین‌جا شروع کنید.',
            'page_about_final_description' => 'دسته‌ها و محصولات موجود فرجی‌هوم را بررسی کنید یا برای پرسش پیش از خرید با ما در ارتباط باشید.',
            'page_about_final_shop_cta' => 'ورود به فروشگاه',
            'page_about_final_contact_cta' => 'تماس با ما',

            // Legal pages.
            'page_shipping_title' => 'ارسال و مرجوعی',
            'page_shipping_lead' => 'روش و هزینه ارسال با توجه به نشانی و گزینه‌های قابل انتخاب هنگام ثبت سفارش مشخص می‌شود. پیش از نهایی کردن خرید، اطلاعات نمایش‌داده‌شده برای همان سفارش را بررسی کنید.',
            'page_shipping_section_1_title' => 'هزینه و روش ارسال',
            'page_shipping_section_1_body' => 'هزینه و امکان ارسال بر اساس نشانی تحویل و روش‌های فعال در زمان ثبت سفارش محاسبه و نمایش داده می‌شود. اگر گزینه‌ای برای نشانی شما نمایش داده نشد، پیش از پرداخت با فروشگاه تماس بگیرید.',
            'page_shipping_section_2_title' => 'پیگیری سفارش',
            'page_shipping_section_2_body' => 'وضعیت سفارش‌های ثبت‌شده از حساب کاربری قابل مشاهده است. برای پرسش درباره وضعیت یا جزئیات تحویل، شماره سفارش را هنگام تماس با فروشگاه در دسترس داشته باشید.',
            'page_shipping_section_3_title' => 'بررسی کالا و درخواست مرجوعی',
            'page_shipping_section_3_body' => 'هنگام دریافت، سلامت ظاهری بسته و تطابق کالای تحویل‌شده با سفارش را بررسی کنید. شرایط پذیرش درخواست مرجوعی با توجه به نوع کالا و مقررات جاری بررسی می‌شود؛ پیش از ارسال مجدد، با فروشگاه هماهنگ کنید.',
            'page_warranty_title' => 'ضمانت و خدمات پس از فروش',
            'page_warranty_lead' => 'شرایط ضمانت ممکن است بین مدل‌ها متفاوت باشد. اطلاعات اختصاصی هر محصول و مدارک همراه آن را پیش از خرید بررسی کنید.',
            'page_warranty_section_1_title' => 'مدت و دامنه ضمانت',
            'page_warranty_section_1_body' => 'مدت، شروع و موارد تحت پوشش ضمانت باید برای همان مدل در صفحه محصول یا مدرک ضمانت درج شده باشد. در صورت نبود اطلاعات روشن، پیش از ثبت سفارش از فروشگاه استعلام بگیرید.',
            'page_warranty_section_2_title' => 'مدارک خرید',
            'page_warranty_section_2_body' => 'فاکتور خرید، مشخصات مدل و مدارک همراه محصول را برای پیگیری خدمات پس از فروش نگهداری کنید. شرایط ارائه خدمات بر اساس ضوابط شرکت ارائه‌دهنده ضمانت بررسی می‌شود.',
            'page_warranty_section_3_title' => 'درخواست خدمات',
            'page_warranty_section_3_body' => 'برای پیگیری ضمانت، نام مدل، شماره سفارش و شرح ایراد را از طریق راه‌های ارتباطی فروشگاه ارسال کنید. پیش از بررسی، محصول را باز یا تعمیر نکنید مگر آنکه دستورالعمل رسمی همان مدل چنین اجازه‌ای داده باشد.',
            'page_installments_title' => 'خرید اقساطی',
            'page_installments_lead' => 'امکان خرید اقساطی فقط برای محصولاتی نمایش داده می‌شود که این روش پرداخت برای آن‌ها در فروشگاه فعال شده باشد.',
            'page_installments_section_1_title' => 'مشاهده شرایط هر محصول',
            'page_installments_section_1_body' => 'در صفحه محصول، وضعیت فعال بودن خرید اقساطی و جزئیات ثبت‌شده همان مدل را بررسی کنید. شرایط یک محصول را به محصول دیگر تعمیم ندهید.',
            'page_installments_section_2_title' => 'بررسی مبلغ نهایی',
            'page_installments_section_2_body' => 'پیش از تأیید سفارش، مبلغ پرداخت اولیه، تعداد و مبلغ اقساط و هر هزینه اعلام‌شده را در خلاصه سفارش بررسی کنید. ثبت سفارش را فقط پس از اطمینان از جزئیات نهایی ادامه دهید.',
            'page_installments_section_3_title' => 'روش پرداخت',
            'page_installments_section_3_body' => 'روش‌های پرداخت فعال در مرحله تسویه‌حساب نمایش داده می‌شوند. اگر پرداخت آنلاین برای فروشگاه پیکربندی و فعال نشده باشد، گزینه‌ای برای ورود اطلاعات کارت بانکی در سایت ارائه نمی‌شود.',
            'page_faq_title' => 'پرسش‌های متداول',
            'page_faq_lead' => 'پاسخ پرسش‌های رایج درباره انتخاب محصول، سفارش و خدمات فروشگاه.',
            'page_faq_1_question' => 'مشخصات فنی هر محصول را از کجا ببینم؟',
            'page_faq_1_answer' => 'در صفحه هر محصول، مشخصات ثبت‌شده همان مدل، تصاویر، قیمت و وضعیت موجودی نمایش داده می‌شود. ویژگی‌های خالی یا ثبت‌نشده به‌عنوان مشخصات قطعی در نظر گرفته نمی‌شوند.',
            'page_faq_2_question' => 'چطور از موجودی و قیمت نهایی مطلع شوم؟',
            'page_faq_2_answer' => 'قیمت و وضعیت موجودی درج‌شده در صفحه محصول را بررسی کنید. مبلغ نهایی و هزینه‌های مربوط به سفارش در مرحله تسویه‌حساب نمایش داده می‌شود.',
            'page_faq_3_question' => 'آیا همه محصولات امکان خرید اقساطی دارند؟',
            'page_faq_3_answer' => 'خیر؛ خرید اقساطی فقط برای محصولاتی فعال است که این روش پرداخت برای آن‌ها در فروشگاه تنظیم شده باشد. جزئیات همان محصول را بررسی کنید.',
            'page_faq_4_question' => 'سفارش ثبت‌شده را چطور پیگیری کنم؟',
            'page_faq_4_answer' => 'پس از ورود به حساب کاربری، بخش سفارش‌ها را باز کنید و وضعیت سفارش موردنظر را ببینید.',
            'page_faq_5_question' => 'شرایط ضمانت محصولات یکسان است؟',
            'page_faq_5_answer' => 'شرایط ضمانت به مدل و ارائه‌دهنده ضمانت بستگی دارد. اطلاعات صفحه محصول و مدارک همراه کالا را ملاک قرار دهید.',
            'page_faq_6_question' => 'برای پرسش درباره یک مدل چطور با فروشگاه ارتباط بگیرم؟',
            'page_faq_6_answer' => 'از صفحه تماس با ما استفاده کنید و نام مدل یا شماره سفارش را در پیام خود بنویسید تا موضوع درخواست روشن باشد.',

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
