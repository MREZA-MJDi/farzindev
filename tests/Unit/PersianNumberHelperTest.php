<?php

namespace Tests\Unit;

use App\Helpers\PersianNumberHelper;
use PHPUnit\Framework\TestCase;

class PersianNumberHelperTest extends TestCase
{
    public function test_it_formats_whole_currency_values_with_persian_digits(): void
    {
        $this->assertSame('۱۲٬۳۴۵٬۶۷۸', PersianNumberHelper::format(12345678));
    }

    public function test_it_formats_decimal_values_with_persian_separators(): void
    {
        $this->assertSame('۱۲۳٫۵۰', PersianNumberHelper::format(123.5, 2));
    }

    public function test_it_handles_null_values(): void
    {
        $this->assertSame('۰', PersianNumberHelper::format(null));
    }
}
