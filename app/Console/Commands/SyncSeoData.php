<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\Country;
use App\Models\Tag;
use App\Models\TagTranslation;
use App\Models\CountryTranslation;
use App\Services\TranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncSeoData extends Command
{
    protected $signature = 'seo:sync 
                            {--tags : Sync tags from cam models}
                            {--countries : Sync countries from cam models}
                            {--translate : Generate translations using AI}
                            {--locale=* : Specific locales to translate (default: all)}';

    protected $description = 'Sync SEO data (tags, countries) from cam models and generate translations';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    public function handle(): int
    {
        if ($this->option('tags')) {
            $this->syncTags();
        }

        if ($this->option('countries')) {
            $this->syncCountries();
        }

        if ($this->option('translate')) {
            $locales = $this->option('locale') ?: ['es', 'fr', 'de', 'pt'];
            $this->generateTranslations($locales);
        }

        // If no options, run everything
        if (!$this->option('tags') && !$this->option('countries') && !$this->option('translate')) {
            $this->syncTags();
            $this->syncCountries();
        }

        $this->info('SEO data sync complete!');
        return 0;
    }

    protected function syncTags(): void
    {
        $this->info('Syncing tags from cam models...');

        // Get all unique tags from cam models
        $rawTags = CamModel::whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc();

        $bar = $this->output->createProgressBar($rawTags->count());
        $bar->start();

        foreach ($rawTags as $tagName => $count) {
            $slug = Str::slug($tagName);
            
            if (empty($slug) || strlen($slug) < 2) {
                $bar->advance();
                continue;
            }

            // Determine category based on common patterns
            $category = $this->categorizeTag($tagName);

            Tag::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => ucfirst(str_replace('-', ' ', $tagName)),
                    'category' => $category,
                    'models_count' => $count,
                ]
            );

            // Create English translation
            $tag = Tag::where('slug', $slug)->first();
            TagTranslation::updateOrCreate(
                ['tag_id' => $tag->id, 'locale' => 'en'],
                [
                    'slug' => $slug,
                    'name' => $tag->name,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Synced {$rawTags->count()} tags");
    }

    protected function syncCountries(): void
    {
        $this->info('Syncing countries from cam models...');

        // Get all unique countries
        $countries = CamModel::whereNotNull('country')
            ->where('country', '!=', '')
            ->select('country', DB::raw('count(*) as count'))
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->get();

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        foreach ($countries as $row) {
            $countryName = $row->country;
            $slug = Str::slug($countryName);
            $code = $this->getCountryCode($countryName);
            $flag = country_flag($countryName);

            if (empty($slug)) {
                $bar->advance();
                continue;
            }

            Country::updateOrCreate(
                ['slug' => $slug],
                [
                    'code' => $code,
                    'name' => $countryName,
                    'flag' => $flag,
                    'models_count' => $row->count,
                ]
            );

            // Create English translation
            $country = Country::where('slug', $slug)->first();
            CountryTranslation::updateOrCreate(
                ['country_id' => $country->id, 'locale' => 'en'],
                [
                    'slug' => $slug,
                    'name' => $countryName,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Synced {$countries->count()} countries");
    }

    protected function generateTranslations(array $locales): void
    {
        $this->info('Generating translations for locales: ' . implode(', ', $locales));

        // Translate tags
        $tags = Tag::whereDoesntHave('translations', function ($q) use ($locales) {
            $q->whereIn('locale', $locales);
        })->limit(50)->get();

        if ($tags->isNotEmpty()) {
            $this->info("Translating {$tags->count()} tags...");
            $bar = $this->output->createProgressBar($tags->count() * count($locales));
            $bar->start();

            foreach ($tags as $tag) {
                foreach ($locales as $locale) {
                    try {
                        $translation = $this->translationService->translateTag($tag->name, $locale);
                        
                        TagTranslation::updateOrCreate(
                            ['tag_id' => $tag->id, 'locale' => $locale],
                            [
                                'slug' => $translation['slug'],
                                'name' => $translation['name'],
                                'meta_title' => $translation['meta_title'],
                                'meta_description' => $translation['meta_description'],
                                'page_content' => $translation['page_content'],
                            ]
                        );
                    } catch (\Exception $e) {
                        $this->warn("Failed to translate tag '{$tag->name}' to {$locale}: " . $e->getMessage());
                    }
                    
                    $bar->advance();
                    usleep(100000); // Rate limiting
                }
            }

            $bar->finish();
            $this->newLine();
        }

        // Translate countries
        $countries = Country::whereDoesntHave('translations', function ($q) use ($locales) {
            $q->whereIn('locale', $locales);
        })->limit(50)->get();

        if ($countries->isNotEmpty()) {
            $this->info("Translating {$countries->count()} countries...");
            $bar = $this->output->createProgressBar($countries->count() * count($locales));
            $bar->start();

            foreach ($countries as $country) {
                foreach ($locales as $locale) {
                    try {
                        $translation = $this->translationService->translateCountry(
                            $country->name,
                            $country->code,
                            $locale
                        );
                        
                        CountryTranslation::updateOrCreate(
                            ['country_id' => $country->id, 'locale' => $locale],
                            [
                                'slug' => $translation['slug'],
                                'name' => $translation['name'],
                                'meta_title' => $translation['meta_title'],
                                'meta_description' => $translation['meta_description'],
                                'page_content' => $translation['page_content'],
                            ]
                        );
                    } catch (\Exception $e) {
                        $this->warn("Failed to translate country '{$country->name}' to {$locale}: " . $e->getMessage());
                    }
                    
                    $bar->advance();
                    usleep(100000);
                }
            }

            $bar->finish();
            $this->newLine();
        }
    }

    protected function categorizeTag(string $tag): ?string
    {
        $tag = strtolower($tag);

        $categories = [
            'body' => ['big', 'small', 'petite', 'curvy', 'slim', 'thick', 'bbw', 'skinny', 'ass', 'boobs', 'tits', 'busty', 'natural', 'fake'],
            'ethnicity' => ['asian', 'latina', 'ebony', 'white', 'black', 'indian', 'arab', 'japanese', 'korean', 'chinese', 'filipina', 'thai'],
            'age' => ['teen', 'milf', 'mature', 'young', 'granny', 'cougar', 'college'],
            'hair' => ['blonde', 'brunette', 'redhead', 'ginger', 'black hair', 'brown hair'],
            'fetish' => ['feet', 'anal', 'bdsm', 'squirt', 'deepthroat', 'roleplay', 'domination', 'submission'],
            'appearance' => ['tattoo', 'piercing', 'glasses', 'hairy', 'shaved', 'trimmed'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($tag, $keyword)) {
                    return $category;
                }
            }
        }

        return null;
    }

    protected function getCountryCode(string $countryName): string
    {
        $codes = [
            'united states' => 'US', 'usa' => 'US', 'america' => 'US',
            'united kingdom' => 'GB', 'uk' => 'GB', 'england' => 'GB',
            'colombia' => 'CO', 'brazil' => 'BR', 'argentina' => 'AR',
            'mexico' => 'MX', 'spain' => 'ES', 'france' => 'FR',
            'germany' => 'DE', 'italy' => 'IT', 'russia' => 'RU',
            'ukraine' => 'UA', 'poland' => 'PL', 'romania' => 'RO',
            'netherlands' => 'NL', 'canada' => 'CA', 'australia' => 'AU',
            'japan' => 'JP', 'korea' => 'KR', 'china' => 'CN',
            'thailand' => 'TH', 'philippines' => 'PH', 'india' => 'IN',
            'indonesia' => 'ID', 'vietnam' => 'VN', 'czech republic' => 'CZ',
            'czechia' => 'CZ', 'hungary' => 'HU', 'portugal' => 'PT',
            'sweden' => 'SE', 'norway' => 'NO', 'finland' => 'FI',
            'denmark' => 'DK', 'belgium' => 'BE', 'switzerland' => 'CH',
            'austria' => 'AT', 'greece' => 'GR', 'turkey' => 'TR',
            'egypt' => 'EG', 'south africa' => 'ZA', 'nigeria' => 'NG',
            'kenya' => 'KE', 'peru' => 'PE', 'chile' => 'CL',
            'venezuela' => 'VE', 'ecuador' => 'EC', 'cuba' => 'CU',
        ];

        $lower = strtolower($countryName);
        return $codes[$lower] ?? strtoupper(substr($countryName, 0, 2));
    }
}
