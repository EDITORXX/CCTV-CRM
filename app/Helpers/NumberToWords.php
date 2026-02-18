<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    private static $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    public static function convert($number)
    {
        $number = round($number);
        if ($number == 0) return 'Zero';

        $result = '';

        if ($number >= 10000000) {
            $result .= self::convertBelow1000(floor($number / 10000000)) . ' Crore ';
            $number %= 10000000;
        }
        if ($number >= 100000) {
            $result .= self::convertBelow1000(floor($number / 100000)) . ' Lakh ';
            $number %= 100000;
        }
        if ($number >= 1000) {
            $result .= self::convertBelow1000(floor($number / 1000)) . ' Thousand ';
            $number %= 1000;
        }
        if ($number >= 100) {
            $result .= self::$ones[floor($number / 100)] . ' Hundred ';
            $number %= 100;
        }
        if ($number > 0) {
            if ($result != '') $result .= 'and ';
            if ($number < 20) {
                $result .= self::$ones[$number];
            } else {
                $result .= self::$tens[floor($number / 10)];
                if ($number % 10) {
                    $result .= ' ' . self::$ones[$number % 10];
                }
            }
        }

        return trim($result);
    }

    private static function convertBelow1000($number)
    {
        $result = '';
        if ($number >= 100) {
            $result .= self::$ones[floor($number / 100)] . ' Hundred ';
            $number %= 100;
        }
        if ($number > 0) {
            if ($number < 20) {
                $result .= self::$ones[$number];
            } else {
                $result .= self::$tens[floor($number / 10)];
                if ($number % 10) {
                    $result .= ' ' . self::$ones[$number % 10];
                }
            }
        }
        return trim($result);
    }
}
