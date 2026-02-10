<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncCountries extends Command
{
    protected $signature = 'countries:sync';
    protected $description = 'Sync countries from the CamModel database';

    public function handle(): int
    {
        $this->info('Syncing countries from CamModel database...');

        try {
            // Get unique countries from cam models
            $countries = CamModel::on('cam')
                ->select('country')
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->distinct()
                ->pluck('country');

            $count = 0;
            foreach ($countries as $countryName) {
                if (empty($countryName)) continue;

                $code = $this->getCountryCode($countryName);
                $slug = Str::slug($countryName);

                Country::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $countryName,
                        'slug' => $slug,
                    ]
                );
                $count++;
            }

            $this->info("Synced {$count} countries");

        } catch (\Exception $e) {
            $this->error('Failed to sync countries: ' . $e->getMessage());
            $this->info('Make sure you are running this command inside Docker where the cam database is accessible.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Get ISO country code from country name
     */
    protected function getCountryCode(string $name): string
    {
        $codes = [
            'United States' => 'US',
            'USA' => 'US',
            'United Kingdom' => 'GB',
            'UK' => 'GB',
            'Germany' => 'DE',
            'France' => 'FR',
            'Spain' => 'ES',
            'Italy' => 'IT',
            'Netherlands' => 'NL',
            'Belgium' => 'BE',
            'Poland' => 'PL',
            'Ukraine' => 'UA',
            'Russia' => 'RU',
            'Romania' => 'RO',
            'Czech Republic' => 'CZ',
            'Czechia' => 'CZ',
            'Colombia' => 'CO',
            'Brazil' => 'BR',
            'Argentina' => 'AR',
            'Mexico' => 'MX',
            'Chile' => 'CL',
            'Peru' => 'PE',
            'Venezuela' => 'VE',
            'Philippines' => 'PH',
            'Thailand' => 'TH',
            'Japan' => 'JP',
            'China' => 'CN',
            'South Korea' => 'KR',
            'Korea' => 'KR',
            'India' => 'IN',
            'Indonesia' => 'ID',
            'Australia' => 'AU',
            'Canada' => 'CA',
            'Latvia' => 'LV',
            'Estonia' => 'EE',
            'Lithuania' => 'LT',
            'Hungary' => 'HU',
            'Serbia' => 'RS',
            'Croatia' => 'HR',
            'Bulgaria' => 'BG',
            'Moldova' => 'MD',
            'Ecuador' => 'EC',
            'Portugal' => 'PT',
            'Austria' => 'AT',
            'Switzerland' => 'CH',
            'Sweden' => 'SE',
            'Norway' => 'NO',
            'Denmark' => 'DK',
            'Finland' => 'FI',
            'Greece' => 'GR',
            'Turkey' => 'TR',
            'South Africa' => 'ZA',
            'Egypt' => 'EG',
            'Kenya' => 'KE',
            'Nigeria' => 'NG',
            'Israel' => 'IL',
            'United Arab Emirates' => 'AE',
            'UAE' => 'AE',
            'Singapore' => 'SG',
            'Malaysia' => 'MY',
            'Vietnam' => 'VN',
            'Taiwan' => 'TW',
            'Hong Kong' => 'HK',
            'New Zealand' => 'NZ',
            'Ireland' => 'IE',
            'Slovakia' => 'SK',
            'Slovenia' => 'SI',
            'Cyprus' => 'CY',
            'Malta' => 'MT',
            'Luxembourg' => 'LU',
            'Iceland' => 'IS',
            'Belarus' => 'BY',
            'Kazakhstan' => 'KZ',
            'Georgia' => 'GE',
            'Armenia' => 'AM',
            'Azerbaijan' => 'AZ',
            'Uzbekistan' => 'UZ',
            'Costa Rica' => 'CR',
            'Panama' => 'PA',
            'Guatemala' => 'GT',
            'Honduras' => 'HN',
            'El Salvador' => 'SV',
            'Nicaragua' => 'NI',
            'Dominican Republic' => 'DO',
            'Puerto Rico' => 'PR',
            'Jamaica' => 'JM',
            'Cuba' => 'CU',
            'Bolivia' => 'BO',
            'Paraguay' => 'PY',
            'Uruguay' => 'UY',
        ];

        // Try exact match first
        if (isset($codes[$name])) {
            return $codes[$name];
        }

        // Try case-insensitive match
        foreach ($codes as $countryName => $code) {
            if (strtolower($countryName) === strtolower($name)) {
                return $code;
            }
        }

        // Generate a code from the name (first 2 letters uppercase)
        return strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2));
    }
}
