<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\StorefrontSetting;
use App\Services\StorefrontPageContent;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageContentController extends Controller
{
    private const GROUPS = [
        'home' => [
            'title' => 'صفحه اصلی',
            'description' => 'عنوان و تصویر Hero را ویرایش کنید. کلیدهای نمایش بخش محصولات و مجله را هم بررسی کنید؛ اگر خاموش باشند، حتی محصولات و مقاله‌های منتشرشده در صفحه اصلی دیده نمی‌شوند. دسته‌ها و محصولات از اطلاعات واقعی فروشگاه خوانده می‌شوند.',
            'fields' => [
                'page_home_hero_eyebrow' => ['label' => 'برچسب بالای Hero جایگزین', 'type' => 'text', 'max' => 120],
                'page_home_hero_title' => ['label' => 'عنوان Hero جایگزین', 'type' => 'text', 'max' => 120],
                'page_home_banner_1_eyebrow' => ['label' => 'بنر 1 — برچسب بالای عنوان', 'type' => 'text', 'max' => 100],
                'page_home_banner_1_title' => ['label' => 'بنر 1 — عنوان', 'type' => 'text', 'max' => 140],
                'page_home_banner_1_description' => ['label' => 'بنر 1 — توضیح', 'type' => 'textarea', 'max' => 350],
                'page_home_banner_1_button_label' => ['label' => 'بنر 1 — متن دکمه', 'type' => 'text', 'max' => 70],
                'page_home_banner_1_button_url' => ['label' => 'بنر 1 — نشانی دکمه (مسیر داخلی یا لینک HTTPS)', 'type' => 'text', 'max' => 500],
                'page_home_banner_2_eyebrow' => ['label' => 'بنر 2 — برچسب بالای عنوان', 'type' => 'text', 'max' => 100],
                'page_home_banner_2_title' => ['label' => 'بنر 2 — عنوان', 'type' => 'text', 'max' => 140],
                'page_home_banner_2_description' => ['label' => 'بنر 2 — توضیح', 'type' => 'textarea', 'max' => 350],
                'page_home_banner_2_button_label' => ['label' => 'بنر 2 — متن دکمه', 'type' => 'text', 'max' => 70],
                'page_home_banner_2_button_url' => ['label' => 'بنر 2 — نشانی دکمه (مسیر داخلی یا لینک HTTPS)', 'type' => 'text', 'max' => 500],
                'page_home_banner_3_eyebrow' => ['label' => 'بنر 3 — برچسب بالای عنوان', 'type' => 'text', 'max' => 100],
                'page_home_banner_3_title' => ['label' => 'بنر 3 — عنوان', 'type' => 'text', 'max' => 140],
                'page_home_banner_3_description' => ['label' => 'بنر 3 — توضیح', 'type' => 'textarea', 'max' => 350],
                'page_home_banner_3_button_label' => ['label' => 'بنر 3 — متن دکمه', 'type' => 'text', 'max' => 70],
                'page_home_banner_3_button_url' => ['label' => 'بنر 3 — نشانی دکمه (مسیر داخلی یا لینک HTTPS)', 'type' => 'text', 'max' => 500],
                'page_home_banner_4_eyebrow' => ['label' => 'بنر 4 — برچسب بالای عنوان', 'type' => 'text', 'max' => 100],
                'page_home_banner_4_title' => ['label' => 'بنر 4 — عنوان', 'type' => 'text', 'max' => 140],
                'page_home_banner_4_description' => ['label' => 'بنر 4 — توضیح', 'type' => 'textarea', 'max' => 350],
                'page_home_banner_4_button_label' => ['label' => 'بنر 4 — متن دکمه', 'type' => 'text', 'max' => 70],
                'page_home_banner_4_button_url' => ['label' => 'بنر 4 — نشانی دکمه (مسیر داخلی یا لینک HTTPS)', 'type' => 'text', 'max' => 500],
                'page_home_hero_description' => ['label' => 'زیرعنوان یک‌خطی Hero', 'type' => 'text', 'max' => 220],
                'page_home_enamad_url' => ['label' => 'پیوند رسمی نماد اعتماد الکترونیکی (HTTPS)', 'type' => 'text', 'max' => 500],
                'page_home_warranty_text' => ['label' => 'توضیح ضمانت در صفحه اصلی', 'type' => 'textarea', 'max' => 300],
                'page_home_hero_primary_label' => ['label' => 'متن دکمه اصلی', 'type' => 'text', 'max' => 80],
                'page_home_technology_eyebrow' => ['label' => 'فناوری — برچسب', 'type' => 'text', 'max' => 120],
                'page_home_technology_title' => ['label' => 'فناوری — عنوان', 'type' => 'text', 'max' => 160],
                'page_home_technology_body' => ['label' => 'فناوری — توضیح', 'type' => 'textarea', 'max' => 1000],
                'page_home_technology_cta' => ['label' => 'فناوری — متن دکمه', 'type' => 'text', 'max' => 80],
                'page_home_show_technology' => ['label' => 'نمایش سکشن فناوری در صفحه اصلی', 'type' => 'toggle'],
                'page_home_categories_eyebrow' => ['label' => 'برچسب دسته‌بندی‌ها', 'type' => 'text', 'max' => 120],
                'page_home_categories_title' => ['label' => 'عنوان بخش دسته‌بندی‌ها', 'type' => 'text', 'max' => 160],
                'page_home_categories_description' => ['label' => 'توضیح بخش دسته‌بندی‌ها', 'type' => 'textarea', 'max' => 500],
                'page_home_products_eyebrow' => ['label' => 'برچسب بخش محصولات منتخب', 'type' => 'text', 'max' => 120],
                'page_home_products_title' => ['label' => 'عنوان محصولات منتخب در نبود فروش', 'type' => 'text', 'max' => 160],
                'page_home_products_description' => ['label' => 'توضیح محصولات منتخب', 'type' => 'textarea', 'max' => 500],
                'page_home_guides_title' => ['label' => 'عنوان راهنماها', 'type' => 'text', 'max' => 160],
                'page_home_guides_description' => ['label' => 'توضیح راهنماها', 'type' => 'textarea', 'max' => 300],
                'page_home_hood_guide_title' => ['label' => 'راهنمای هود — عنوان', 'type' => 'text', 'max' => 100],
                'page_home_hood_guide_description' => ['label' => 'راهنمای هود — توضیح', 'type' => 'textarea', 'max' => 300],
                'page_home_hood_guide_cta' => ['label' => 'راهنمای هود — دکمه', 'type' => 'text', 'max' => 80],
                'page_home_sink_guide_title' => ['label' => 'راهنمای سینک — عنوان', 'type' => 'text', 'max' => 100],
                'page_home_sink_guide_description' => ['label' => 'راهنمای سینک — توضیح', 'type' => 'textarea', 'max' => 300],
                'page_home_sink_guide_cta' => ['label' => 'راهنمای سینک — دکمه', 'type' => 'text', 'max' => 80],
                'page_home_gas_guide_title' => ['label' => 'راهنمای اجاق گاز — عنوان', 'type' => 'text', 'max' => 100],
                'page_home_gas_guide_description' => ['label' => 'راهنمای اجاق گاز — توضیح', 'type' => 'textarea', 'max' => 300],
                'page_home_gas_guide_cta' => ['label' => 'راهنمای اجاق گاز — دکمه', 'type' => 'text', 'max' => 80],
                'page_home_journal_title' => ['label' => 'عنوان مجله', 'type' => 'text', 'max' => 160],
                'page_home_journal_description' => ['label' => 'توضیح مجله', 'type' => 'textarea', 'max' => 300],
                'page_home_final_title' => ['label' => 'عنوان دعوت پایانی', 'type' => 'text', 'max' => 160],
                'page_home_final_description' => ['label' => 'توضیح دعوت پایانی', 'type' => 'textarea', 'max' => 500],
                'page_home_final_cta' => ['label' => 'متن دکمه پایانی', 'type' => 'text', 'max' => 80],
                'page_home_show_categories' => ['label' => 'نمایش بخش دسته‌بندی‌ها', 'type' => 'toggle'],
                'page_home_show_products' => ['label' => 'نمایش بخش محصولات منتخب', 'type' => 'toggle'],
                'page_home_show_trust' => ['label' => 'نمایش بخش اعتمادسازی', 'type' => 'toggle'],
                'page_home_show_guides' => ['label' => 'نمایش راهنماهای هود و سینک', 'type' => 'toggle'],
                'page_home_show_journal' => ['label' => 'نمایش بخش مجله', 'type' => 'toggle'],
                'page_home_show_final_cta' => ['label' => 'نمایش دعوت پایانی', 'type' => 'toggle'],
            ],
        ],
        'about' => [
            'title' => 'درباره ما',
            'description' => 'متن معرفی فروشگاه، معیارهای انتخاب تجهیزات، ارزش‌ها، مسیر خرید و دعوت پایانی را ویرایش کنید.',
            'fields' => [
                'page_about_hero_eyebrow' => ['label' => 'برچسب Hero', 'type' => 'text', 'max' => 120],
                'page_about_hero_title' => ['label' => 'عنوان Hero', 'type' => 'text', 'max' => 160],
                'page_about_hero_highlight' => ['label' => 'خط تأکیدی Hero', 'type' => 'text', 'max' => 160],
                'page_about_hero_description' => ['label' => 'متن معرفی', 'type' => 'textarea', 'max' => 1000],
                'page_about_hero_cta' => ['label' => 'دکمه معرفی', 'type' => 'text', 'max' => 80],
                'page_about_statement_eyebrow' => ['label' => 'برچسب بیانیه', 'type' => 'text', 'max' => 120],
                'page_about_statement_title' => ['label' => 'عنوان بیانیه', 'type' => 'text', 'max' => 160],
                'page_about_statement_body_1' => ['label' => 'متن بیانیه — بخش اول', 'type' => 'textarea', 'max' => 1000],
                'page_about_statement_body_2' => ['label' => 'متن بیانیه — بخش دوم', 'type' => 'textarea', 'max' => 1000],
                'page_about_factory_eyebrow' => ['label' => 'برچسب راهنمای انتخاب', 'type' => 'text', 'max' => 120],
                'page_about_factory_title' => ['label' => 'عنوان راهنمای انتخاب', 'type' => 'text', 'max' => 160],
                'page_about_factory_body_1' => ['label' => 'متن راهنمای انتخاب — بخش اول', 'type' => 'textarea', 'max' => 1000],
                'page_about_factory_body_2' => ['label' => 'متن راهنمای انتخاب — بخش دوم', 'type' => 'textarea', 'max' => 1000],
                'page_about_factory_cta' => ['label' => 'دکمه مشاهده محصولات', 'type' => 'text', 'max' => 80],
                'page_about_values_eyebrow' => ['label' => 'برچسب ارزش‌ها', 'type' => 'text', 'max' => 120],
                'page_about_values_title' => ['label' => 'عنوان ارزش‌ها', 'type' => 'text', 'max' => 160],
                'page_about_value_1_title' => ['label' => 'ارزش ۱ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_value_1_description' => ['label' => 'ارزش ۱ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_value_2_title' => ['label' => 'ارزش ۲ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_value_2_description' => ['label' => 'ارزش ۲ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_value_3_title' => ['label' => 'ارزش ۳ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_value_3_description' => ['label' => 'ارزش ۳ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_value_4_title' => ['label' => 'ارزش ۴ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_value_4_description' => ['label' => 'ارزش ۴ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_experience_eyebrow' => ['label' => 'برچسب مسیر مشتری', 'type' => 'text', 'max' => 120],
                'page_about_experience_title' => ['label' => 'عنوان مسیر مشتری', 'type' => 'text', 'max' => 160],
                'page_about_experience_description' => ['label' => 'توضیح مسیر مشتری', 'type' => 'textarea', 'max' => 500],
                'page_about_step_1_title' => ['label' => 'مرحله ۱ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_step_1_description' => ['label' => 'مرحله ۱ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_step_2_title' => ['label' => 'مرحله ۲ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_step_2_description' => ['label' => 'مرحله ۲ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_step_3_title' => ['label' => 'مرحله ۳ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_step_3_description' => ['label' => 'مرحله ۳ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_step_4_title' => ['label' => 'مرحله ۴ — عنوان', 'type' => 'text', 'max' => 100],
                'page_about_step_4_description' => ['label' => 'مرحله ۴ — توضیح', 'type' => 'textarea', 'max' => 500],
                'page_about_installment_eyebrow' => ['label' => 'برچسب پرداخت منعطف', 'type' => 'text', 'max' => 120],
                'page_about_installment_title' => ['label' => 'عنوان پرداخت منعطف', 'type' => 'text', 'max' => 160],
                'page_about_installment_description' => ['label' => 'توضیح پرداخت منعطف', 'type' => 'textarea', 'max' => 1000],
                'page_about_installment_cta' => ['label' => 'دکمه پرداخت منعطف', 'type' => 'text', 'max' => 80],
                'page_about_final_eyebrow' => ['label' => 'برچسب بخش پایانی', 'type' => 'text', 'max' => 100],
                'page_about_final_title' => ['label' => 'عنوان بخش پایانی', 'type' => 'text', 'max' => 160],
                'page_about_final_description' => ['label' => 'توضیح بخش پایانی', 'type' => 'textarea', 'max' => 500],
                'page_about_final_shop_cta' => ['label' => 'دکمه فروشگاه', 'type' => 'text', 'max' => 80],
                'page_about_final_contact_cta' => ['label' => 'دکمه تماس', 'type' => 'text', 'max' => 80],
                'page_about_show_statement' => ['label' => 'نمایش بیانیه مجموعه', 'type' => 'toggle'],
                'page_about_show_factory' => ['label' => 'نمایش راهنمای انتخاب محصولات', 'type' => 'toggle'],
                'page_about_show_values' => ['label' => 'نمایش ارزش‌های مجموعه', 'type' => 'toggle'],
                'page_about_show_experience' => ['label' => 'نمایش مسیر تجربه مشتری', 'type' => 'toggle'],
                'page_about_show_installment' => ['label' => 'نمایش بخش پرداخت منعطف', 'type' => 'toggle'],
                'page_about_show_final' => ['label' => 'نمایش دعوت پایانی', 'type' => 'toggle'],
            ],
        ],
        'policies' => [
            'title' => 'اطلاعات خرید و پرسش‌های متداول',
            'description' => 'متن ارسال و مرجوعی، ضمانت، پرداخت اقساطی و پرسش‌های متداول را مدیریت کنید. هیچ مبلغ، مهلت یا تعهدی بدون تأیید مجموعه در این صفحات درج نکنید.',
            'fields' => [
                'page_shipping_title' => ['label' => 'ارسال و مرجوعی — عنوان', 'type' => 'text', 'max' => 160],
                'page_shipping_lead' => ['label' => 'ارسال و مرجوعی — مقدمه', 'type' => 'textarea', 'max' => 600],
                'page_shipping_section_1_title' => ['label' => 'ارسال — بخش ۱: عنوان', 'type' => 'text', 'max' => 160],
                'page_shipping_section_1_body' => ['label' => 'ارسال — بخش ۱: متن', 'type' => 'textarea', 'max' => 1800],
                'page_shipping_section_2_title' => ['label' => 'ارسال — بخش ۲: عنوان', 'type' => 'text', 'max' => 160],
                'page_shipping_section_2_body' => ['label' => 'ارسال — بخش ۲: متن', 'type' => 'textarea', 'max' => 1800],
                'page_shipping_section_3_title' => ['label' => 'ارسال — بخش ۳: عنوان', 'type' => 'text', 'max' => 160],
                'page_shipping_section_3_body' => ['label' => 'ارسال — بخش ۳: متن', 'type' => 'textarea', 'max' => 1800],
                'page_warranty_title' => ['label' => 'ضمانت — عنوان', 'type' => 'text', 'max' => 160],
                'page_warranty_lead' => ['label' => 'ضمانت — مقدمه', 'type' => 'textarea', 'max' => 600],
                'page_warranty_section_1_title' => ['label' => 'ضمانت — بخش ۱: عنوان', 'type' => 'text', 'max' => 160],
                'page_warranty_section_1_body' => ['label' => 'ضمانت — بخش ۱: متن', 'type' => 'textarea', 'max' => 1800],
                'page_warranty_section_2_title' => ['label' => 'ضمانت — بخش ۲: عنوان', 'type' => 'text', 'max' => 160],
                'page_warranty_section_2_body' => ['label' => 'ضمانت — بخش ۲: متن', 'type' => 'textarea', 'max' => 1800],
                'page_warranty_section_3_title' => ['label' => 'ضمانت — بخش ۳: عنوان', 'type' => 'text', 'max' => 160],
                'page_warranty_section_3_body' => ['label' => 'ضمانت — بخش ۳: متن', 'type' => 'textarea', 'max' => 1800],
                'page_installments_title' => ['label' => 'پرداخت اقساطی — عنوان', 'type' => 'text', 'max' => 160],
                'page_installments_lead' => ['label' => 'پرداخت اقساطی — مقدمه', 'type' => 'textarea', 'max' => 600],
                'page_installments_section_1_title' => ['label' => 'اقساط — بخش ۱: عنوان', 'type' => 'text', 'max' => 160],
                'page_installments_section_1_body' => ['label' => 'اقساط — بخش ۱: متن', 'type' => 'textarea', 'max' => 1800],
                'page_installments_section_2_title' => ['label' => 'اقساط — بخش ۲: عنوان', 'type' => 'text', 'max' => 160],
                'page_installments_section_2_body' => ['label' => 'اقساط — بخش ۲: متن', 'type' => 'textarea', 'max' => 1800],
                'page_installments_section_3_title' => ['label' => 'اقساط — بخش ۳: عنوان', 'type' => 'text', 'max' => 160],
                'page_installments_section_3_body' => ['label' => 'اقساط — بخش ۳: متن', 'type' => 'textarea', 'max' => 1800],
                'page_faq_title' => ['label' => 'پرسش‌های متداول — عنوان', 'type' => 'text', 'max' => 160],
                'page_faq_lead' => ['label' => 'پرسش‌های متداول — مقدمه', 'type' => 'textarea', 'max' => 600],
                'page_faq_1_question' => ['label' => 'پرسش ۱', 'type' => 'text', 'max' => 180],
                'page_faq_1_answer' => ['label' => 'پاسخ ۱', 'type' => 'textarea', 'max' => 1000],
                'page_faq_2_question' => ['label' => 'پرسش ۲', 'type' => 'text', 'max' => 180],
                'page_faq_2_answer' => ['label' => 'پاسخ ۲', 'type' => 'textarea', 'max' => 1000],
                'page_faq_3_question' => ['label' => 'پرسش ۳', 'type' => 'text', 'max' => 180],
                'page_faq_3_answer' => ['label' => 'پاسخ ۳', 'type' => 'textarea', 'max' => 1000],
                'page_faq_4_question' => ['label' => 'پرسش ۴', 'type' => 'text', 'max' => 180],
                'page_faq_4_answer' => ['label' => 'پاسخ ۴', 'type' => 'textarea', 'max' => 1000],
                'page_faq_5_question' => ['label' => 'پرسش ۵', 'type' => 'text', 'max' => 180],
                'page_faq_5_answer' => ['label' => 'پاسخ ۵', 'type' => 'textarea', 'max' => 1000],
                'page_faq_6_question' => ['label' => 'پرسش ۶', 'type' => 'text', 'max' => 180],
                'page_faq_6_answer' => ['label' => 'پاسخ ۶', 'type' => 'textarea', 'max' => 1000],
            ],
        ],
        'legal' => [
            'title' => 'قوانین و حریم خصوصی',
            'description' => 'متن عمومی قوانین خرید و حریم خصوصی را ویرایش کنید. محتوای این تب به‌صورت متن امن نمایش داده می‌شود.',
            'fields' => [
                'page_terms_title' => ['label' => 'عنوان صفحه قوانین', 'type' => 'text', 'max' => 160],
                'page_terms_lead' => ['label' => 'مقدمه قوانین', 'type' => 'textarea', 'max' => 500],
                'page_terms_section_1_title' => ['label' => 'قوانین — بخش ۱: عنوان', 'type' => 'text', 'max' => 160],
                'page_terms_section_1_body' => ['label' => 'قوانین — بخش ۱: متن', 'type' => 'textarea', 'max' => 2000],
                'page_terms_section_2_title' => ['label' => 'قوانین — بخش ۲: عنوان', 'type' => 'text', 'max' => 160],
                'page_terms_section_2_body' => ['label' => 'قوانین — بخش ۲: متن', 'type' => 'textarea', 'max' => 2000],
                'page_terms_section_3_title' => ['label' => 'قوانین — بخش ۳: عنوان', 'type' => 'text', 'max' => 160],
                'page_terms_section_3_body' => ['label' => 'قوانین — بخش ۳: متن', 'type' => 'textarea', 'max' => 2000],
                'page_terms_section_4_title' => ['label' => 'قوانین — بخش ۴: عنوان', 'type' => 'text', 'max' => 160],
                'page_terms_section_4_body' => ['label' => 'قوانین — بخش ۴: متن', 'type' => 'textarea', 'max' => 2000],
                'page_terms_contact_title' => ['label' => 'قوانین — متن دعوت تماس', 'type' => 'text', 'max' => 200],
                'page_terms_contact_cta' => ['label' => 'قوانین — دکمه تماس', 'type' => 'text', 'max' => 80],
                'page_privacy_title' => ['label' => 'عنوان صفحه حریم خصوصی', 'type' => 'text', 'max' => 160],
                'page_privacy_lead' => ['label' => 'مقدمه حریم خصوصی', 'type' => 'textarea', 'max' => 500],
                'page_privacy_section_1_title' => ['label' => 'حریم خصوصی — بخش ۱: عنوان', 'type' => 'text', 'max' => 160],
                'page_privacy_section_1_body' => ['label' => 'حریم خصوصی — بخش ۱: متن', 'type' => 'textarea', 'max' => 2000],
                'page_privacy_section_2_title' => ['label' => 'حریم خصوصی — بخش ۲: عنوان', 'type' => 'text', 'max' => 160],
                'page_privacy_section_2_body' => ['label' => 'حریم خصوصی — بخش ۲: متن', 'type' => 'textarea', 'max' => 2000],
                'page_privacy_section_3_title' => ['label' => 'حریم خصوصی — بخش ۳: عنوان', 'type' => 'text', 'max' => 160],
                'page_privacy_section_3_body' => ['label' => 'حریم خصوصی — بخش ۳: متن', 'type' => 'textarea', 'max' => 2000],
                'page_privacy_section_4_title' => ['label' => 'حریم خصوصی — بخش ۴: عنوان', 'type' => 'text', 'max' => 160],
                'page_privacy_section_4_body' => ['label' => 'حریم خصوصی — بخش ۴: متن', 'type' => 'textarea', 'max' => 2000],
                'page_privacy_contact_title' => ['label' => 'حریم خصوصی — متن دعوت تماس', 'type' => 'text', 'max' => 200],
                'page_privacy_contact_cta' => ['label' => 'حریم خصوصی — دکمه تماس', 'type' => 'text', 'max' => 80],
            ],
        ],
        'typography' => [
            'title' => 'تایپوگرافی و رفتار عمومی',
            'description' => 'مقیاس پایه نوشته‌ها، ارتفاع خط و عرض محتوای عمومی را تنظیم کنید. مقدارها محدود و کنترل‌شده‌اند تا چیدمان در موبایل و دسکتاپ آسیب نبیند.',
            'fields' => [
                'page_typography_font_scale' => ['label' => 'مقیاس کلی فونت (درصد)', 'type' => 'number', 'min' => 90, 'max' => 110, 'step' => 1],
                'page_typography_line_height' => ['label' => 'ارتفاع خط متن', 'type' => 'number', 'min' => 1.4, 'max' => 2.2, 'step' => 0.05],
                'page_typography_content_width' => ['label' => 'حداکثر عرض محتوای عمومی (px)', 'type' => 'number', 'min' => 1120, 'max' => 1440, 'step' => 10],
            ],
        ],
    ];

    public function edit(Request $request, StorefrontPageContent $content): View
    {
        $tab = $request->query('tab', 'home');
        abort_unless(array_key_exists($tab, self::GROUPS), 404);

        return view('admin.page-content.edit', [
            'tab' => $tab,
            'group' => self::GROUPS[$tab],
            'fieldSections' => $this->groupFields($tab),
            'groups' => self::GROUPS,
            'values' => $content->all(),
            'technologyMediaImages' => $tab === 'home'
                ? Media::query()->where('disk', 'public')->where('mime_type', 'like', 'image/%')->latest()->limit(100)->get()
                    ->filter(fn (Media $media): bool => filled($media->url))->values()
                : collect(),
            'selectedTechnologyMediaId' => $tab === 'home'
                ? Media::query()->where('disk', 'public')->where('path', $content->all()['page_home_technology_image_url'] ?? '')->value('id')
                : null,
        ]);
    }

    /**
     * Group editable fields by the public section they control.
     *
     * @return array<int, array{title: string, description: string, fields: array}>
     */
    private function groupFields(string $tab): array
    {
        $sections = [];

        foreach (self::GROUPS[$tab]['fields'] as $key => $field) {
            $title = match (true) {
                $tab === 'home' && preg_match('/^page_home_banner_(\d+)_/', $key, $matches) === 1 => 'اسلایدر اصلی — بنر ' . $matches[1],
                $tab === 'home' && str_starts_with($key, 'page_home_hero_') => 'Hero جایگزین',
                $tab === 'home' && str_starts_with($key, 'page_home_categories_') => 'بخش دسته‌بندی‌ها',
                $tab === 'home' && str_starts_with($key, 'page_home_products_') => 'بخش محصولات منتخب',
                $tab === 'home' && str_starts_with($key, 'page_home_hood_guide_') => 'راهنمای هود',
                $tab === 'home' && str_starts_with($key, 'page_home_sink_guide_') => 'راهنمای سینک',
                $tab === 'home' && str_starts_with($key, 'page_home_gas_guide_') => 'راهنمای اجاق گاز',
                $tab === 'home' && str_starts_with($key, 'page_home_guides_') => 'معرفی بخش راهنماها',
                $tab === 'home' && str_starts_with($key, 'page_home_journal_') => 'مجله و مقاله‌ها',
                $tab === 'home' && str_starts_with($key, 'page_home_technology_') => 'بخش فناوری و جزئیات ساخت',
                $tab === 'home' && $key === 'page_home_show_categories' => 'بخش دسته‌بندی‌ها',
                $tab === 'home' && $key === 'page_home_show_products' => 'بخش محصولات منتخب',
                $tab === 'home' && $key === 'page_home_show_guides' => 'معرفی بخش راهنماها',
                $tab === 'home' && $key === 'page_home_show_journal' => 'مجله و مقاله‌ها',
                $tab === 'home' && $key === 'page_home_show_technology' => 'بخش فناوری و جزئیات ساخت',
                $tab === 'home' && $key === 'page_home_show_final_cta' => 'دعوت پایانی',
                $tab === 'home' && ($key === 'page_home_show_trust' || in_array($key, ['page_home_enamad_url', 'page_home_warranty_text'], true)) => 'اعتمادسازی',
                $tab === 'about' && str_starts_with($key, 'page_about_hero_') => 'معرفی و Hero',
                $tab === 'about' && str_starts_with($key, 'page_about_statement_') => 'بیانیه مجموعه',
                $tab === 'about' && str_starts_with($key, 'page_about_factory_') => 'راهنمای انتخاب محصولات',
                $tab === 'about' && preg_match('/^page_about_value_\d+_/', $key) === 1 => 'ارزش‌های مجموعه',
                $tab === 'about' && str_starts_with($key, 'page_about_values_') => 'ارزش‌های مجموعه',
                $tab === 'about' && preg_match('/^page_about_step_\d+_/', $key) === 1 => 'مراحل تجربه مشتری',
                $tab === 'about' && str_starts_with($key, 'page_about_experience_') => 'مراحل تجربه مشتری',
                $tab === 'about' && str_starts_with($key, 'page_about_installment_') => 'توضیح پرداخت منعطف',
                $tab === 'about' && str_starts_with($key, 'page_about_final_') => 'دعوت پایانی و تماس',
                $tab === 'about' && str_starts_with($key, 'page_about_show_') => 'نمایش بخش‌های درباره ما',
                $tab === 'policies' && str_starts_with($key, 'page_shipping_') => 'ارسال و مرجوعی — /shipping-returns',
                $tab === 'policies' && str_starts_with($key, 'page_warranty_') => 'ضمانت — /warranty',
                $tab === 'policies' && str_starts_with($key, 'page_installments_') => 'پرداخت اقساطی — /payment-installments',
                $tab === 'policies' && str_starts_with($key, 'page_faq_') => 'پرسش‌های متداول — /faq',
                $tab === 'legal' && str_starts_with($key, 'page_terms_') => 'قوانین و شرایط — /terms',
                $tab === 'legal' && str_starts_with($key, 'page_privacy_') => 'حریم خصوصی — /privacy',
                $tab === 'typography' => 'تنظیمات عمومی صفحات',
                default => 'سایر تنظیمات',
            };

            $sections[$title] ??= [
                'title' => $title,
                'description' => match (true) {
                    str_starts_with($title, 'اسلایدر اصلی') => 'محتوای همین اسلاید را کنترل می‌کند؛ تصویر هر بنر جداگانه در همین تب انتخاب می‌شود.',
                    $title === 'Hero جایگزین' => 'فقط زمانی نمایش داده می‌شود که هیچ بنر اصلیِ دارای تصویر و عنوان فعال نباشد.',
                    str_contains($title, '/terms') => 'این فیلدها مستقیماً در صفحه قوانین عمومی نمایش داده می‌شوند.',
                    str_contains($title, '/privacy') => 'این فیلدها مستقیماً در صفحه حریم خصوصی عمومی نمایش داده می‌شوند.',
                    str_contains($title, '/shipping-returns') || str_contains($title, '/warranty') || str_contains($title, '/payment-installments') || str_contains($title, '/faq') => 'مسیر صفحه عمومی در عنوان گروه مشخص شده است.',
                    default => 'این تنظیمات به بخش مشخص‌شده در صفحه عمومی متصل هستند؛ پیش‌نمایش بالای صفحه نسخه ذخیره‌شده را باز می‌کند.',
                },
                'fields' => [],
            ];

            $sections[$title]['fields'][$key] = $field;
        }

        return array_values($sections);
    }

    public function update(Request $request, StorefrontPageContent $content, ResponsiveImageService $images): RedirectResponse
    {
        $tab = (string) $request->input('tab', 'home');
        abort_unless(array_key_exists($tab, self::GROUPS), 404);

        $rules = [];
        foreach (self::GROUPS[$tab]['fields'] as $key => $field) {
            $rules[$key] = match ($field['type']) {
                'toggle' => ['required', 'boolean'],
                'number' => ['required', 'numeric', 'between:' . $field['min'] . ',' . $field['max']],
                'select' => ['required', 'string', Rule::in(array_keys($field['options']))],
                default => ['present', 'nullable', 'string', 'max:' . $field['max']],
            };
        }

        if ($tab === 'home') {
            $rules['page_home_hero_image'] = [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=1200,min_height=750,ratio=16/10',
                'max:20480',
            ];
            $rules['page_home_technology_image'] = [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=1200,min_height=750,ratio=16/10',
                'max:20480',
            ];
            $rules['page_home_technology_media_id'] = [
                'nullable',
                Rule::exists('media', 'id')->where(fn ($query) => $query
                    ->where('disk', 'public')
                    ->where('mime_type', 'like', 'image/%')),
            ];

            for ($index = 1; $index <= 4; $index++) {
                $rules["page_home_banner_{$index}_image"] = [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'dimensions:min_width=1200,min_height=375,ratio=16/6',
                    'max:20480',
                ];
                $rules["remove_page_home_banner_{$index}_image"] = ['sometimes', 'boolean'];
                $rules["page_home_banner_{$index}_button_url"] = [
                    'present',
                    'nullable',
                    'string',
                    'max:500',
                    static function (string $attribute, mixed $value, \Closure $fail): void {
                        $value = trim((string) $value);
                        if ($value === '') {
                            return;
                        }

                        $scheme = parse_url($value, PHP_URL_SCHEME);
                        $isInternalPath = str_starts_with($value, '/') && ! str_starts_with($value, '//');
                        $isSecureAbsoluteUrl = filter_var($value, FILTER_VALIDATE_URL)
                            && in_array($scheme, ['http', 'https'], true);

                        if (! $isInternalPath && ! $isSecureAbsoluteUrl) {
                            $fail('نشانی دکمه باید مسیر داخلی سایت یا لینک معتبر HTTP/HTTPS باشد.');
                        }
                    },
                ];
            }
        }

        $validated = $request->validate($rules, [
            'page_typography_font_scale.between' => 'مقیاس فونت باید بین ۹۰ تا ۱۱۰ درصد باشد.',
            'page_typography_line_height.between' => 'ارتفاع خط باید بین ۱٫۴ تا ۲٫۲ باشد.',
            'page_typography_content_width.between' => 'عرض محتوا باید بین ۱۱۲۰ تا ۱۴۴۰ پیکسل باشد.',
            'page_home_hero_image.image' => 'فایل Hero باید یک تصویر معتبر باشد.',
            'page_home_hero_image.mimes' => 'فرمت تصویر Hero باید JPG، PNG یا WEBP باشد.',
            'page_home_hero_image.dimensions' => 'خروجی Hero باید نسبت ۱۶:۱۰ و حداقل ابعاد ۱۲۰۰ در ۷۵۰ پیکسل داشته باشد.',
            'page_home_hero_image.max' => 'حجم تصویر Hero نباید بیشتر از ۲۰ مگابایت باشد.',
            'page_home_technology_image.image' => 'فایل سکشن فناوری باید یک تصویر معتبر باشد.',
            'page_home_technology_image.dimensions' => 'تصویر فناوری باید نسبت ۱۶:۱۰ و حداقل ابعاد ۱۲۰۰ در ۷۵۰ پیکسل داشته باشد.',
            'page_home_technology_image.max' => 'حجم تصویر فناوری نباید بیشتر از ۲۰ مگابایت باشد.',
            'page_home_banner_1_image.image' => 'فایل بنر 1 باید یک تصویر معتبر باشد.',
            'page_home_banner_1_image.dimensions' => 'تصویر بنر 1 باید حداقل ۱۲۰۰×۳۷۵ و با نسبت ۱۶:۶ باشد.',
            'page_home_banner_1_image.max' => 'حجم تصویر بنر 1 نباید بیشتر از ۲۰ مگابایت باشد.',
            'page_home_banner_2_image.image' => 'فایل بنر 2 باید یک تصویر معتبر باشد.',
            'page_home_banner_2_image.dimensions' => 'تصویر بنر 2 باید حداقل ۱۲۰۰×۳۷۵ و با نسبت ۱۶:۶ باشد.',
            'page_home_banner_2_image.max' => 'حجم تصویر بنر 2 نباید بیشتر از ۲۰ مگابایت باشد.',
            'page_home_banner_3_image.image' => 'فایل بنر 3 باید یک تصویر معتبر باشد.',
            'page_home_banner_3_image.dimensions' => 'تصویر بنر 3 باید حداقل ۱۲۰۰×۳۷۵ و با نسبت ۱۶:۶ باشد.',
            'page_home_banner_3_image.max' => 'حجم تصویر بنر 3 نباید بیشتر از ۲۰ مگابایت باشد.',
            'page_home_banner_4_image.image' => 'فایل بنر 4 باید یک تصویر معتبر باشد.',
            'page_home_banner_4_image.dimensions' => 'تصویر بنر 4 باید حداقل ۱۲۰۰×۳۷۵ و با نسبت ۱۶:۶ باشد.',
            'page_home_banner_4_image.max' => 'حجم تصویر بنر 4 نباید بیشتر از ۲۰ مگابایت باشد.',
        ]);

        $heroImageUpload = $validated['page_home_hero_image'] ?? null;
        $technologyImageUpload = $validated['page_home_technology_image'] ?? null;
        $technologyMediaId = $validated['page_home_technology_media_id'] ?? null;
        unset(
            $validated['page_home_hero_image'],
            $validated['page_home_technology_image'],
            $validated['page_home_technology_media_id']
        );

        $bannerImageUploads = [];
        $removeBannerImages = [];
        $previousBannerImages = [];

        for ($index = 1; $index <= 4; $index++) {
            $uploadKey = "page_home_banner_{$index}_image";
            $removeKey = "remove_page_home_banner_{$index}_image";
            $bannerImageUploads[$index] = $validated[$uploadKey] ?? null;
            unset($validated[$uploadKey], $validated[$removeKey]);

            $removeBannerImages[$index] = $request->boolean($removeKey);
            $previousBannerImages[$index] = (string) StorefrontSetting::query()
                ->where('key', "page_home_banner_{$index}_image_url")
                ->value('value');
        }

        $normalized = [];
        foreach ($validated as $key => $value) {
            $field = self::GROUPS[$tab]['fields'][$key];
            $normalized[$key] = match ($field['type']) {
                'toggle' => $request->boolean($key) ? '1' : '0',
                default => trim((string) ($value ?? '')),
            };
        }

        $previousHeroImagePath = $tab === 'home'
            ? (string) StorefrontSetting::query()
                ->where('key', 'page_home_hero_image_url')
                ->value('value')
            : '';

        $newHeroImagePath = null;
        if ($heroImageUpload !== null) {
            $newHeroImagePath = $images->store($heroImageUpload, 'homepage');

            if (! $newHeroImagePath) {
                return back()->withInput()->withErrors([
                    'page_home_hero_image' => 'ذخیره تصویر Hero انجام نشد؛ فضای ذخیره‌سازی را بررسی کنید.',
                ]);
            }
        }

        $previousTechnologyImagePath = $tab === 'home'
            ? (string) StorefrontSetting::query()->where('key', 'page_home_technology_image_url')->value('value')
            : '';
        $newTechnologyImagePath = null;
        $technologyImageIsMediaLibraryAsset = false;

        if ($technologyImageUpload !== null) {
            $newTechnologyImagePath = $images->store($technologyImageUpload, 'homepage/technology');

            if (! $newTechnologyImagePath) {
                if ($newHeroImagePath !== null) {
                    $images->delete($newHeroImagePath);
                }

                return back()->withInput()->withErrors([
                    'page_home_technology_image' => 'ذخیره تصویر فناوری انجام نشد؛ تصویر قبلی حفظ شد.',
                ]);
            }
        } elseif ($technologyMediaId !== null && $technologyMediaId !== '') {
            $selectedTechnologyMedia = Media::query()
                ->whereKey($technologyMediaId)
                ->where('disk', 'public')
                ->where('mime_type', 'like', 'image/%')
                ->firstOrFail();

            if (! $selectedTechnologyMedia->url) {
                return back()->withInput()->withErrors([
                    'page_home_technology_media_id' => 'فایل انتخاب‌شده در کتابخانه رسانه در دسترس نیست.',
                ]);
            }

            $newTechnologyImagePath = $selectedTechnologyMedia->path;
            $technologyImageIsMediaLibraryAsset = true;
        }

        $newBannerImagePaths = [];
        $bannerImageChanges = [];

        foreach ($bannerImageUploads as $index => $upload) {
            if ($upload !== null) {
                $storedPath = $images->store($upload, 'homepage/banners');

                if (! $storedPath) {
                    if ($newHeroImagePath !== null) {
                        $images->delete($newHeroImagePath);
                    }
                    if ($newTechnologyImagePath !== null && ! $technologyImageIsMediaLibraryAsset) {
                        $images->delete($newTechnologyImagePath);
                    }
                    foreach ($newBannerImagePaths as $storedBannerPath) {
                        $images->delete($storedBannerPath);
                    }

                    return back()->withInput()->withErrors([
                        "page_home_banner_{$index}_image" => "ذخیره تصویر بنر {$index} انجام نشد؛ فایل قبلی حفظ شد.",
                    ]);
                }

                $newBannerImagePaths[$index] = $storedPath;
                $bannerImageChanges[$index] = $storedPath;
            } elseif ($removeBannerImages[$index]) {
                $bannerImageChanges[$index] = null;
            }
        }

        $expectedSettings = $normalized;
        if ($newHeroImagePath !== null) {
            $expectedSettings['page_home_hero_image_url'] = $newHeroImagePath;
        }
        if ($newTechnologyImagePath !== null) {
            $expectedSettings['page_home_technology_image_url'] = $newTechnologyImagePath;
        }
        foreach ($bannerImageChanges as $index => $path) {
            $expectedSettings["page_home_banner_{$index}_image_url"] = (string) ($path ?? '');
        }

        try {
            DB::transaction(function () use ($normalized, $newHeroImagePath, $newTechnologyImagePath, $bannerImageChanges, $expectedSettings): void {
                foreach ($normalized as $key => $value) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }

                if ($newHeroImagePath !== null) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => 'page_home_hero_image_url'],
                        ['value' => $newHeroImagePath]
                    );
                }

                if ($newTechnologyImagePath !== null) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => 'page_home_technology_image_url'],
                        ['value' => $newTechnologyImagePath]
                    );
                }

                foreach ($bannerImageChanges as $index => $path) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => "page_home_banner_{$index}_image_url"],
                        ['value' => $path]
                    );
                }

                $savedSettings = StorefrontSetting::query()
                    ->whereIn('key', array_keys($expectedSettings))
                    ->pluck('value', 'key')
                    ->all();

                foreach ($expectedSettings as $key => $expectedValue) {
                    if ((string) ($savedSettings[$key] ?? '') !== (string) $expectedValue) {
                        throw new \RuntimeException('Persisted page content did not match submitted value: ' . $key);
                    }
                }
            });
        } catch (\Throwable $exception) {
            if ($newHeroImagePath !== null) {
                $images->delete($newHeroImagePath);
            }
            if ($newTechnologyImagePath !== null && ! $technologyImageIsMediaLibraryAsset) {
                $images->delete($newTechnologyImagePath);
            }
            foreach ($newBannerImagePaths as $storedBannerPath) {
                $images->delete($storedBannerPath);
            }

            if ($exception instanceof \RuntimeException || $exception instanceof \Illuminate\Database\QueryException) {
                report($exception);

                return back()->withInput()->withErrors([
                    'page_content_save' => 'ذخیره تغییرات در پایگاه داده تأیید نشد؛ محتوای قبلی حفظ شد. خطا ثبت شده است؛ دوباره تلاش کنید.',
                ]);
            }

            throw $exception;
        }

        foreach ($bannerImageChanges as $index => $newPath) {
            $oldPath = $previousBannerImages[$index] ?? '';
            if ($oldPath !== '' && $oldPath !== (string) $newPath) {
                $images->delete($oldPath);
            }
        }

        if (
            $newHeroImagePath !== null
            && $previousHeroImagePath !== ''
            && $previousHeroImagePath !== $newHeroImagePath
        ) {
            $images->delete($previousHeroImagePath);
        }

        if (
            $newTechnologyImagePath !== null
            && $previousTechnologyImagePath !== ''
            && $previousTechnologyImagePath !== $newTechnologyImagePath
            && ! Media::query()->where('disk', 'public')->where('path', $previousTechnologyImagePath)->exists()
        ) {
            $images->delete($previousTechnologyImagePath);
        }

        $content->clearCache();

        return redirect()
            ->route('admin.page-content.edit', ['tab' => $tab])
            ->with('success', 'ذخیره‌سازی تأیید شد؛ اطلاعات از پایگاه داده دوباره خوانده شدند. پیش‌نمایش بالای صفحه نسخه ذخیره‌شده را نشان می‌دهد.');
    }

}
