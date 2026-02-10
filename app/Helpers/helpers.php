<?php

if (!function_exists('country_flag')) {
    /**
     * Convert country code or name to flag emoji
     */
    function country_flag(?string $country): string
    {
        if (empty($country)) {
            return '';
        }

        // Common country name to code mapping
        $countryMap = [
            'united states' => 'US',
            'usa' => 'US',
            'united kingdom' => 'GB',
            'uk' => 'GB',
            'england' => 'GB',
            'germany' => 'DE',
            'france' => 'FR',
            'spain' => 'ES',
            'italy' => 'IT',
            'russia' => 'RU',
            'russian federation' => 'RU',
            'ukraine' => 'UA',
            'poland' => 'PL',
            'romania' => 'RO',
            'netherlands' => 'NL',
            'belgium' => 'BE',
            'czech republic' => 'CZ',
            'czechia' => 'CZ',
            'colombia' => 'CO',
            'brazil' => 'BR',
            'argentina' => 'AR',
            'mexico' => 'MX',
            'canada' => 'CA',
            'australia' => 'AU',
            'japan' => 'JP',
            'south korea' => 'KR',
            'korea' => 'KR',
            'china' => 'CN',
            'india' => 'IN',
            'thailand' => 'TH',
            'philippines' => 'PH',
            'indonesia' => 'ID',
            'vietnam' => 'VN',
            'turkey' => 'TR',
            'portugal' => 'PT',
            'sweden' => 'SE',
            'norway' => 'NO',
            'finland' => 'FI',
            'denmark' => 'DK',
            'switzerland' => 'CH',
            'austria' => 'AT',
            'greece' => 'GR',
            'hungary' => 'HU',
            'croatia' => 'HR',
            'serbia' => 'RS',
            'bulgaria' => 'BG',
            'slovakia' => 'SK',
            'slovenia' => 'SI',
            'latvia' => 'LV',
            'lithuania' => 'LT',
            'estonia' => 'EE',
            'ireland' => 'IE',
            'south africa' => 'ZA',
            'egypt' => 'EG',
            'chile' => 'CL',
            'peru' => 'PE',
            'venezuela' => 'VE',
        ];

        // Check if it's already a 2-letter code
        $code = strtoupper(trim($country));
        if (strlen($code) === 2) {
            // It's likely already a country code
        } else {
            // Try to find in map
            $code = $countryMap[strtolower(trim($country))] ?? null;
        }

        if (!$code || strlen($code) !== 2) {
            return '';
        }

        // Convert country code to flag emoji
        // Each letter is converted to regional indicator symbol
        $flag = '';
        foreach (str_split($code) as $char) {
            $flag .= mb_chr(ord($char) - ord('A') + 0x1F1E6);
        }

        return $flag;
    }
}
