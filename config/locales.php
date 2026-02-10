<?php

/**
 * Supported Locales Configuration
 * 
 * Organized by region for clarity.
 * Each locale includes: name, native name, flag emoji, and RTL flag
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    */
    'default' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    */
    'fallback' => 'en',

    /*
    |--------------------------------------------------------------------------
    | All Supported Locales
    |--------------------------------------------------------------------------
    | 50+ languages covering Europe, Asia, Americas, and Middle East
    */
    'supported' => [
        // =====================================================================
        // WESTERN EUROPE
        // =====================================================================
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇬🇧',
            'rtl' => false,
            'region' => 'europe',
        ],
        'es' => [
            'name' => 'Spanish',
            'native' => 'Español',
            'flag' => '🇪🇸',
            'rtl' => false,
            'region' => 'europe',
        ],
        'fr' => [
            'name' => 'French',
            'native' => 'Français',
            'flag' => '🇫🇷',
            'rtl' => false,
            'region' => 'europe',
        ],
        'de' => [
            'name' => 'German',
            'native' => 'Deutsch',
            'flag' => '🇩🇪',
            'rtl' => false,
            'region' => 'europe',
        ],
        'it' => [
            'name' => 'Italian',
            'native' => 'Italiano',
            'flag' => '🇮🇹',
            'rtl' => false,
            'region' => 'europe',
        ],
        'pt' => [
            'name' => 'Portuguese',
            'native' => 'Português',
            'flag' => '🇵🇹',
            'rtl' => false,
            'region' => 'europe',
        ],
        'pt-BR' => [
            'name' => 'Brazilian Portuguese',
            'native' => 'Português (Brasil)',
            'flag' => '🇧🇷',
            'rtl' => false,
            'region' => 'americas',
        ],
        'nl' => [
            'name' => 'Dutch',
            'native' => 'Nederlands',
            'flag' => '🇳🇱',
            'rtl' => false,
            'region' => 'europe',
        ],
        'be' => [
            'name' => 'Belgian',
            'native' => 'Vlaams',
            'flag' => '🇧🇪',
            'rtl' => false,
            'region' => 'europe',
        ],
        'ca' => [
            'name' => 'Catalan',
            'native' => 'Català',
            'flag' => '🏴󠁥󠁳󠁣󠁴󠁿',
            'rtl' => false,
            'region' => 'europe',
        ],
        'gl' => [
            'name' => 'Galician',
            'native' => 'Galego',
            'flag' => '🇪🇸',
            'rtl' => false,
            'region' => 'europe',
        ],
        'eu' => [
            'name' => 'Basque',
            'native' => 'Euskara',
            'flag' => '🇪🇸',
            'rtl' => false,
            'region' => 'europe',
        ],

        // =====================================================================
        // NORTHERN EUROPE / SCANDINAVIA
        // =====================================================================
        'sv' => [
            'name' => 'Swedish',
            'native' => 'Svenska',
            'flag' => '🇸🇪',
            'rtl' => false,
            'region' => 'europe',
        ],
        'da' => [
            'name' => 'Danish',
            'native' => 'Dansk',
            'flag' => '🇩🇰',
            'rtl' => false,
            'region' => 'europe',
        ],
        'no' => [
            'name' => 'Norwegian',
            'native' => 'Norsk',
            'flag' => '🇳🇴',
            'rtl' => false,
            'region' => 'europe',
        ],
        'fi' => [
            'name' => 'Finnish',
            'native' => 'Suomi',
            'flag' => '🇫🇮',
            'rtl' => false,
            'region' => 'europe',
        ],
        'is' => [
            'name' => 'Icelandic',
            'native' => 'Íslenska',
            'flag' => '🇮🇸',
            'rtl' => false,
            'region' => 'europe',
        ],

        // =====================================================================
        // CENTRAL & EASTERN EUROPE
        // =====================================================================
        'pl' => [
            'name' => 'Polish',
            'native' => 'Polski',
            'flag' => '🇵🇱',
            'rtl' => false,
            'region' => 'europe',
        ],
        'cs' => [
            'name' => 'Czech',
            'native' => 'Čeština',
            'flag' => '🇨🇿',
            'rtl' => false,
            'region' => 'europe',
        ],
        'sk' => [
            'name' => 'Slovak',
            'native' => 'Slovenčina',
            'flag' => '🇸🇰',
            'rtl' => false,
            'region' => 'europe',
        ],
        'hu' => [
            'name' => 'Hungarian',
            'native' => 'Magyar',
            'flag' => '🇭🇺',
            'rtl' => false,
            'region' => 'europe',
        ],
        'ro' => [
            'name' => 'Romanian',
            'native' => 'Română',
            'flag' => '🇷🇴',
            'rtl' => false,
            'region' => 'europe',
        ],
        'bg' => [
            'name' => 'Bulgarian',
            'native' => 'Български',
            'flag' => '🇧🇬',
            'rtl' => false,
            'region' => 'europe',
        ],
        'hr' => [
            'name' => 'Croatian',
            'native' => 'Hrvatski',
            'flag' => '🇭🇷',
            'rtl' => false,
            'region' => 'europe',
        ],
        'sr' => [
            'name' => 'Serbian',
            'native' => 'Српски',
            'flag' => '🇷🇸',
            'rtl' => false,
            'region' => 'europe',
        ],
        'sl' => [
            'name' => 'Slovenian',
            'native' => 'Slovenščina',
            'flag' => '🇸🇮',
            'rtl' => false,
            'region' => 'europe',
        ],
        'mk' => [
            'name' => 'Macedonian',
            'native' => 'Македонски',
            'flag' => '🇲🇰',
            'rtl' => false,
            'region' => 'europe',
        ],
        'sq' => [
            'name' => 'Albanian',
            'native' => 'Shqip',
            'flag' => '🇦🇱',
            'rtl' => false,
            'region' => 'europe',
        ],

        // =====================================================================
        // BALTIC STATES
        // =====================================================================
        'lt' => [
            'name' => 'Lithuanian',
            'native' => 'Lietuvių',
            'flag' => '🇱🇹',
            'rtl' => false,
            'region' => 'europe',
        ],
        'lv' => [
            'name' => 'Latvian',
            'native' => 'Latviešu',
            'flag' => '🇱🇻',
            'rtl' => false,
            'region' => 'europe',
        ],
        'et' => [
            'name' => 'Estonian',
            'native' => 'Eesti',
            'flag' => '🇪🇪',
            'rtl' => false,
            'region' => 'europe',
        ],

        // =====================================================================
        // EASTERN EUROPE / CIS
        // =====================================================================
        'ru' => [
            'name' => 'Russian',
            'native' => 'Русский',
            'flag' => '🇷🇺',
            'rtl' => false,
            'region' => 'europe',
        ],
        'uk' => [
            'name' => 'Ukrainian',
            'native' => 'Українська',
            'flag' => '🇺🇦',
            'rtl' => false,
            'region' => 'europe',
        ],
        'be-BY' => [
            'name' => 'Belarusian',
            'native' => 'Беларуская',
            'flag' => '🇧🇾',
            'rtl' => false,
            'region' => 'europe',
        ],
        'kk' => [
            'name' => 'Kazakh',
            'native' => 'Қазақша',
            'flag' => '🇰🇿',
            'rtl' => false,
            'region' => 'asia',
        ],

        // =====================================================================
        // MEDITERRANEAN
        // =====================================================================
        'el' => [
            'name' => 'Greek',
            'native' => 'Ελληνικά',
            'flag' => '🇬🇷',
            'rtl' => false,
            'region' => 'europe',
        ],
        'tr' => [
            'name' => 'Turkish',
            'native' => 'Türkçe',
            'flag' => '🇹🇷',
            'rtl' => false,
            'region' => 'europe',
        ],
        'mt' => [
            'name' => 'Maltese',
            'native' => 'Malti',
            'flag' => '🇲🇹',
            'rtl' => false,
            'region' => 'europe',
        ],

        // =====================================================================
        // MIDDLE EAST & ARABIC
        // =====================================================================
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'flag' => '🇸🇦',
            'rtl' => true,
            'region' => 'middle_east',
        ],
        'he' => [
            'name' => 'Hebrew',
            'native' => 'עברית',
            'flag' => '🇮🇱',
            'rtl' => true,
            'region' => 'middle_east',
        ],
        'fa' => [
            'name' => 'Persian',
            'native' => 'فارسی',
            'flag' => '🇮🇷',
            'rtl' => true,
            'region' => 'middle_east',
        ],

        // =====================================================================
        // EAST ASIA
        // =====================================================================
        'zh' => [
            'name' => 'Chinese (Simplified)',
            'native' => '简体中文',
            'flag' => '🇨🇳',
            'rtl' => false,
            'region' => 'asia',
        ],
        'zh-TW' => [
            'name' => 'Chinese (Traditional)',
            'native' => '繁體中文',
            'flag' => '🇹🇼',
            'rtl' => false,
            'region' => 'asia',
        ],
        'ja' => [
            'name' => 'Japanese',
            'native' => '日本語',
            'flag' => '🇯🇵',
            'rtl' => false,
            'region' => 'asia',
        ],
        'ko' => [
            'name' => 'Korean',
            'native' => '한국어',
            'flag' => '🇰🇷',
            'rtl' => false,
            'region' => 'asia',
        ],

        // =====================================================================
        // SOUTHEAST ASIA
        // =====================================================================
        'th' => [
            'name' => 'Thai',
            'native' => 'ไทย',
            'flag' => '🇹🇭',
            'rtl' => false,
            'region' => 'asia',
        ],
        'vi' => [
            'name' => 'Vietnamese',
            'native' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'rtl' => false,
            'region' => 'asia',
        ],
        'id' => [
            'name' => 'Indonesian',
            'native' => 'Bahasa Indonesia',
            'flag' => '🇮🇩',
            'rtl' => false,
            'region' => 'asia',
        ],
        'ms' => [
            'name' => 'Malay',
            'native' => 'Bahasa Melayu',
            'flag' => '🇲🇾',
            'rtl' => false,
            'region' => 'asia',
        ],
        'tl' => [
            'name' => 'Filipino',
            'native' => 'Filipino',
            'flag' => '🇵🇭',
            'rtl' => false,
            'region' => 'asia',
        ],

        // =====================================================================
        // SOUTH ASIA
        // =====================================================================
        'hi' => [
            'name' => 'Hindi',
            'native' => 'हिन्दी',
            'flag' => '🇮🇳',
            'rtl' => false,
            'region' => 'asia',
        ],
        'bn' => [
            'name' => 'Bengali',
            'native' => 'বাংলা',
            'flag' => '🇧🇩',
            'rtl' => false,
            'region' => 'asia',
        ],
        'ta' => [
            'name' => 'Tamil',
            'native' => 'தமிழ்',
            'flag' => '🇮🇳',
            'rtl' => false,
            'region' => 'asia',
        ],
        'te' => [
            'name' => 'Telugu',
            'native' => 'తెలుగు',
            'flag' => '🇮🇳',
            'rtl' => false,
            'region' => 'asia',
        ],
        'ur' => [
            'name' => 'Urdu',
            'native' => 'اردو',
            'flag' => '🇵🇰',
            'rtl' => true,
            'region' => 'asia',
        ],

        // =====================================================================
        // AMERICAS (besides English & Portuguese)
        // =====================================================================
        'es-MX' => [
            'name' => 'Mexican Spanish',
            'native' => 'Español (México)',
            'flag' => '🇲🇽',
            'rtl' => false,
            'region' => 'americas',
        ],
        'es-AR' => [
            'name' => 'Argentine Spanish',
            'native' => 'Español (Argentina)',
            'flag' => '🇦🇷',
            'rtl' => false,
            'region' => 'americas',
        ],
        'es-CO' => [
            'name' => 'Colombian Spanish',
            'native' => 'Español (Colombia)',
            'flag' => '🇨🇴',
            'rtl' => false,
            'region' => 'americas',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Priority Locales (for generation order)
    |--------------------------------------------------------------------------
    | These locales will be generated first when bulk translating
    */
    'priority' => [
        'en', 'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru', 
        'ja', 'ko', 'zh', 'ar', 'tr', 'pt-BR', 'es-MX',
    ],

    /*
    |--------------------------------------------------------------------------
    | RTL Locales
    |--------------------------------------------------------------------------
    */
    'rtl' => ['ar', 'he', 'fa', 'ur'],

    /*
    |--------------------------------------------------------------------------
    | Locale Groups (for bulk operations)
    |--------------------------------------------------------------------------
    */
    'groups' => [
        'europe_west' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'nl', 'ca'],
        'europe_north' => ['sv', 'da', 'no', 'fi', 'is'],
        'europe_central' => ['pl', 'cs', 'sk', 'hu', 'ro', 'bg', 'hr', 'sr', 'sl'],
        'europe_east' => ['ru', 'uk', 'be-BY'],
        'baltic' => ['lt', 'lv', 'et'],
        'mediterranean' => ['el', 'tr', 'mt', 'sq'],
        'middle_east' => ['ar', 'he', 'fa'],
        'east_asia' => ['zh', 'zh-TW', 'ja', 'ko'],
        'southeast_asia' => ['th', 'vi', 'id', 'ms', 'tl'],
        'south_asia' => ['hi', 'bn', 'ta', 'te', 'ur'],
        'americas' => ['pt-BR', 'es-MX', 'es-AR', 'es-CO'],
    ],
];
