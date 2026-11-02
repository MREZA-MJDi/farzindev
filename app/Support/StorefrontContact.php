<?php

namespace App\\Support;

use App\\Support\\IranianMobile;

final class StorefrontContact
{
    /**
     * Return a safe public telephone value. Obvious demo values such as
     * repeated-zero placeholders are intentionally hidden.
     *
     * @return array{display:string,href:string}|null
     */
    public static function phone(mixed $value): ?array
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $display = trim((string) $value);

        if ($display === '') {
            return null;
        }

        $ascii = strtr($display, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
        $digits = preg_replace('/\\D/u', '', $ascii) ?? '';

        if (
            strlen($digits) < 8
            || strlen($digits) > 15
            || count(array_unique(str_split($digits))) < 4
        ) {
            return null;
        }

        return [
            'display' => $display,
            'href' => 'tel:+' . $digits,
        ];
    }

    /**
     * Return an E.164 WhatsApp destination only for a valid Iranian mobile.
     */
    public static function whatsapp(mixed $value): ?string
    {
        $mobile = IranianMobile::normalize($value);

        if (
            $mobile === null
            || ! preg_match('/^09[0-9]{9}$/', $mobile)
            || count(array_unique(str_split($mobile))) < 4
        ) {
            return null;
        }

        return '98' . substr($mobile, 1);
    }
}
