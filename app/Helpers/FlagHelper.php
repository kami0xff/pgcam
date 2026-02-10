<?php

namespace App\Helpers;

class FlagHelper
{
    /**
     * Convert ISO 3166-1 alpha-2 country code to flag emoji
     */
    public static function getFlag(string $countryCode): string
    {
        if (empty($countryCode) || strlen($countryCode) !== 2) {
            return '🌍';
        }

        $code = strtoupper($countryCode);
        
        // Convert each letter to regional indicator symbol
        // A = U+1F1E6, B = U+1F1E7, etc.
        $offset = 0x1F1E6 - ord('A');
        
        $flag = '';
        for ($i = 0; $i < 2; $i++) {
            $char = ord($code[$i]);
            if ($char >= ord('A') && $char <= ord('Z')) {
                $flag .= mb_chr($char + $offset);
            } else {
                return '🌍';
            }
        }
        
        return $flag;
    }

    /**
     * Get flag for a country name (tries to match common names to codes)
     */
    public static function getFlagByName(string $countryName): string
    {
        $codes = [
            'united states' => 'US',
            'usa' => 'US',
            'united kingdom' => 'GB',
            'uk' => 'GB',
            'germany' => 'DE',
            'france' => 'FR',
            'spain' => 'ES',
            'italy' => 'IT',
            'netherlands' => 'NL',
            'belgium' => 'BE',
            'poland' => 'PL',
            'ukraine' => 'UA',
            'russia' => 'RU',
            'romania' => 'RO',
            'czech republic' => 'CZ',
            'czechia' => 'CZ',
            'colombia' => 'CO',
            'brazil' => 'BR',
            'argentina' => 'AR',
            'mexico' => 'MX',
            'chile' => 'CL',
            'peru' => 'PE',
            'venezuela' => 'VE',
            'philippines' => 'PH',
            'thailand' => 'TH',
            'japan' => 'JP',
            'china' => 'CN',
            'south korea' => 'KR',
            'korea' => 'KR',
            'india' => 'IN',
            'indonesia' => 'ID',
            'australia' => 'AU',
            'canada' => 'CA',
            'latvia' => 'LV',
            'estonia' => 'EE',
            'lithuania' => 'LT',
            'hungary' => 'HU',
            'serbia' => 'RS',
            'croatia' => 'HR',
            'bulgaria' => 'BG',
            'moldova' => 'MD',
            'ecuador' => 'EC',
            'portugal' => 'PT',
            'austria' => 'AT',
            'switzerland' => 'CH',
            'sweden' => 'SE',
            'norway' => 'NO',
            'denmark' => 'DK',
            'finland' => 'FI',
            'greece' => 'GR',
            'turkey' => 'TR',
            'south africa' => 'ZA',
            'egypt' => 'EG',
            'kenya' => 'KE',
            'nigeria' => 'NG',
            'israel' => 'IL',
            'united arab emirates' => 'AE',
            'uae' => 'AE',
            'singapore' => 'SG',
            'malaysia' => 'MY',
            'vietnam' => 'VN',
            'taiwan' => 'TW',
            'hong kong' => 'HK',
            'new zealand' => 'NZ',
            'ireland' => 'IE',
            'slovakia' => 'SK',
            'slovenia' => 'SI',
            'cyprus' => 'CY',
            'malta' => 'MT',
            'luxembourg' => 'LU',
            'iceland' => 'IS',
            'belarus' => 'BY',
            'kazakhstan' => 'KZ',
            'georgia' => 'GE',
            'armenia' => 'AM',
            'azerbaijan' => 'AZ',
            'uzbekistan' => 'UZ',
            'costa rica' => 'CR',
            'panama' => 'PA',
            'guatemala' => 'GT',
            'honduras' => 'HN',
            'el salvador' => 'SV',
            'nicaragua' => 'NI',
            'dominican republic' => 'DO',
            'puerto rico' => 'PR',
            'jamaica' => 'JM',
            'cuba' => 'CU',
            'bolivia' => 'BO',
            'paraguay' => 'PY',
            'uruguay' => 'UY',
        ];

        $code = $codes[strtolower($countryName)] ?? null;
        
        return $code ? self::getFlag($code) : '🌍';
    }
}
