<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_policy_pages_render_with_canonical_metadata(): void
    {
        foreach ([
            ['shipping-returns', 'ارسال و مرجوعی'],
            ['warranty', 'ضمانت و خدمات پس از فروش'],
            ['payment-installments', 'خرید اقساطی'],
            ['faq', 'پرسش‌های متداول'],
        ] as [$route, $title]) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee($title)
                ->assertSee('rel="canonical"', false);
        }
    }

    public function test_general_faq_page_outputs_faqpage_structured_data(): void
    {
        $this->get(route('faq'))
            ->assertOk()
            ->assertSee('FAQPage', false)
            ->assertSee('مشخصات فنی هر محصول را از کجا ببینم؟');
    }
}
