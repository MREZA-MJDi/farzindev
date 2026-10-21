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

        return strtr($number, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ]);
    }
}
