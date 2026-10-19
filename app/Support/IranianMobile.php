<?php

namespace App\Support;

final class IranianMobile
{
    /**
     * Normalize Iranian mobile values to 09xxxxxxxxx.
     *
     * Supports Persian/Arabic digits, +98/0098 country prefixes,
     * and common separators. Validation is intentionally separate.
     */
    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $value = trim($input);

        if ($value === '') {
            return null;
        }

        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $digits = preg_replace('/\D/u', '', $value) ?? '';

        if (str_starts_with($digits, '0098') && strlen($digits) === 14) {
            $digits = '0' . substr($digits, 4);
        } elseif (str_starts_with($digits, '98') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        } elseif (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            $digits = '0' . $digits;
        }

        return $digits !== '' ? $digits : null;
    }
}
