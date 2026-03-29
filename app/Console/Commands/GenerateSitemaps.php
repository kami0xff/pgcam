<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemaps extends Command
{
    protected $signature = 'sitemap:generate
                            {--warm-cache : Clear sitemap cache so it rebuilds on next request}';

    protected $description = 'Clear the sitemap cache so it rebuilds on the next request';

    public function handle(): int
    {
        $this->info('Clearing sitemap cache...');

        Cache::forget('sitemap:flat');

        // Also clear any legacy keys from the old multi-sitemap setup
        $legacy = ['sitemap:index', 'sitemap:images'];
        foreach ($legacy as $key) {
            Cache::forget($key);
        }

        $this->info('Sitemap cache cleared. Will rebuild on next request.');

        return 0;
    }
}
