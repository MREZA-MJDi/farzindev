<?php

namespace Tests\Unit;

use App\Helpers\JalaliHelper;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class JalaliHelperTest extends TestCase
{
    public function test_it_formats_a_gregorian_date_as_jalali(): void
    {
        $date = new DateTimeImmutable('2026-10-21 14:05:09');

        $this->assertSame(
            '۱۴۰۵/۰۷/۲۹',
            JalaliHelper::format($date, 'Y/m/d')
        );

        $this->assertSame(
            '۱۴۰۵/۰۷/۲۹ ۱۴:۰۵',
            JalaliHelper::format($date, 'Y/m/d H:i')
        );
    }

    public function test_it_handles_the_jalali_new_year_boundary(): void
    {
        $this->assertSame(
            '۱۴۰۳/۱۲/۳۰',
            JalaliHelper::format(new DateTimeImmutable('2025-03-20'), 'Y/m/d')
        );

        $this->assertSame(
            '۱۴۰۴/۰۱/۰۱',
            JalaliHelper::format(new DateTimeImmutable('2025-03-21'), 'Y/m/d')
        );
    }

    public function test_month_ranges_follow_the_jalali_calendar_for_reporting(): void
    {
        [$currentStart, $nextStart] = JalaliHelper::monthRange(
            new DateTimeImmutable('2025-04-10')
        );

        $this->assertSame('2025-03-21', $currentStart->format('Y-m-d'));
        $this->assertSame('2025-04-21', $nextStart->format('Y-m-d'));

        [$previousStart, $previousEnd] = JalaliHelper::monthRange(
            new DateTimeImmutable('2025-04-10'),
            1
        );

        $this->assertSame('2025-02-19', $previousStart->format('Y-m-d'));
        $this->assertSame('2025-03-21', $previousEnd->format('Y-m-d'));
    }

    public function test_it_returns_a_dash_for_missing_dates(): void
    {
        $this->assertSame('—', JalaliHelper::format(null));
    }

    public function test_it_converts_digits_to_persian_digits(): void
    {
        $this->assertSame('۱۲۳٬۴۵۶', JalaliHelper::persianDigits('123٬456'));
    }
}
