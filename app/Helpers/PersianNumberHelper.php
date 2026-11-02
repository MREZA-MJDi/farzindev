<?php

namespace App\Helpers;

final class PersianNumberHelper
{
    public static function format(int|float|string|null $value, int $decimals = 0): string
    {
        $number = number_format(
            (float) ($value ?? 0),
            max(0, $decimals),
            '٫',
            '٬'
        );

        return JalaliHelper::persianDigits($number);
    }
}
