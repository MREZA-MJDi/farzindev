<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTimeInterface;
use InvalidArgumentException;

final class JalaliHelper
{
    private const MONTHS = [
        'فروردین',
        'اردیبهشت',
        'خرداد',
        'تیر',
        'مرداد',
        'شهریور',
        'مهر',
        'آبان',
        'آذر',
        'دی',
        'بهمن',
        'اسفند',
    ];

    private const WEEKDAYS = [
        'یکشنبه',
        'دوشنبه',
        'سه‌شنبه',
        'چهارشنبه',
        'پنجشنبه',
        'جمعه',
        'شنبه',
    ];

    public static function format(
        DateTimeInterface|string|null $value,
        string $format = 'Y/m/d H:i'
    ): string {
        if ($value === null || $value === '') {
            return '—';
        }

        $date = $value instanceof DateTimeInterface
            ? Carbon::instance($value)
            : Carbon::parse($value);

        [$year, $month, $day] = self::gregorianToJalali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j')
        );

        $tokens = [
            'Y' => sprintf('%04d', $year),
            'y' => substr(sprintf('%04d', $year), -2),
            'm' => sprintf('%02d', $month),
            'n' => (string) $month,
            'd' => sprintf('%02d', $day),
            'j' => (string) $day,
            'F' => self::MONTHS[$month - 1],
            'M' => self::MONTHS[$month - 1],
            'l' => self::WEEKDAYS[(int) $date->format('w')],
            'D' => mb_substr(self::WEEKDAYS[(int) $date->format('w')], 0, 3),
            'H' => $date->format('H'),
            'G' => (string) (int) $date->format('G'),
            'h' => $date->format('h'),
            'g' => (string) (int) $date->format('g'),
            'i' => $date->format('i'),
            's' => $date->format('s'),
            'a' => (int) $date->format('G') < 12 ? 'ق.ظ' : 'ب.ظ',
            'A' => (int) $date->format('G') < 12 ? 'قبل‌ازظهر' : 'بعدازظهر',
        ];

        $formatted = preg_replace_callback(
            '/\\\\.|[a-zA-Z]/u',
            static function (array $match) use ($tokens): string {
                $token = $match[0];

                if (str_starts_with($token, '\\')) {
                    return mb_substr($token, 1);
                }

                return $tokens[$token] ?? $token;
            },
            $format
        );

        return self::persianDigits($formatted ?? $format);
    }

    /**
     * Get the Gregorian bounds of a Jalali month relative to a date.
     * The returned interval is [month start, next month start), which avoids
     * end-of-month microsecond and timezone edge cases in database queries.
     *
     * @return array{0:Carbon,1:Carbon}
     */
    public static function monthRange(
        DateTimeInterface|string|null $value = null,
        int $monthsAgo = 0
    ): array {
        $date = $value === null
            ? Carbon::now()
            : ($value instanceof DateTimeInterface
                ? Carbon::instance($value)
                : Carbon::parse($value));

        [$jalaliYear, $jalaliMonth] = self::gregorianToJalali(
            (int) $date->format('Y'),
            (int) $date->format('n'),
            (int) $date->format('j')
        );

        $serial = ($jalaliYear * 12) + ($jalaliMonth - 1) - $monthsAgo;
        $targetYear = (int) floor($serial / 12);
        $targetMonth = ($serial - ($targetYear * 12)) + 1;

        $nextSerial = $serial + 1;
        $nextYear = (int) floor($nextSerial / 12);
        $nextMonth = ($nextSerial - ($nextYear * 12)) + 1;

        [$startYear, $startMonth, $startDay] = self::jalaliToGregorian(
            $targetYear,
            $targetMonth,
            1
        );
        [$nextStartYear, $nextStartMonth, $nextStartDay] = self::jalaliToGregorian(
            $nextYear,
            $nextMonth,
            1
        );

        $timezone = $date->getTimezone();

        return [
            Carbon::create($startYear, $startMonth, $startDay, 0, 0, 0, $timezone),
            Carbon::create($nextStartYear, $nextStartMonth, $nextStartDay, 0, 0, 0, $timezone),
        ];
    }

    /**
     * Convert a Jalali calendar date to Gregorian.
     *
     * @return array{0:int,1:int,2:int}
     */
    private static function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;

        $days = -355668
            + (365 * $jy)
            + (intdiv($jy, 33) * 8)
            + intdiv(($jy % 33) + 3, 4)
            + $jd
            + ($jm < 7
                ? (($jm - 1) * 31)
                : ((($jm - 7) * 30) + 186));

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;
        $gregorianMonthDays = [
            0,
            31,
            self::isGregorianLeapYear($gy) ? 29 : 28,
            31, 30, 31, 30, 31, 31, 30, 31, 30, 31,
        ];

        for ($gm = 1; $gm <= 12 && $gd > $gregorianMonthDays[$gm]; $gm++) {
            $gd -= $gregorianMonthDays[$gm];
        }

        return [$gy, $gm, $gd];
    }

    public static function persianDigits(string|int|float $value): string
    {
        return strtr((string) $value, [
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

    /**
     * Convert a Gregorian date to the Jalali calendar.
     *
     * @return array{0:int,1:int,2:int}
     */
    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        if ($gm < 1 || $gm > 12 || $gd < 1 || $gd > 31) {
            throw new InvalidArgumentException('Invalid Gregorian date.');
        }

        $gMonthDays = [
            0, 31, 59, 90, 120, 151,
            181, 212, 243, 273, 304, 334,
        ];

        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        $gy2 = $gm > 2 ? $gy + 1 : $gy;

        $days = (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            + $gd
            + $gMonthDays[$gm - 1]
            - 80;

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    private static function isGregorianLeapYear(int $year): bool
    {
        return ($year % 4 === 0 && $year % 100 !== 0)
            || $year % 400 === 0;
    }
}
