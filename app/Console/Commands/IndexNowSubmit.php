<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\Tag;
use App\Services\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'indexnow:submit
        {--type=all : What to submit: all, models, tags, pages}
        {--changed-since= : Only submit URLs changed since (e.g. "1 hour ago")}
        {--limit=5000 : Max URLs to submit}';

    protected $description = 'Submit URLs to IndexNow (Bing, Yandex, etc.)';

    public function handle(IndexNowService $indexNow): int
    {
        $type = $this->option('type');
        $limit = (int) $this->option('limit');
        $changedSince = $this->option('changed-since');

        $urls = [];

        if (in_array($type, ['all', 'pages'])) {
            $urls[] = url('/');
            $urls[] = url('/tags');
            $urls[] = url('/countries');
        }

        if (in_array($type, ['all', 'models'])) {
            $query = CamModel::query()->where('is_online', true);

            if ($changedSince) {
                $query->where('updated_at', '>=', now()->parse($changedSince));
            }

            $models = $query->limit($limit)->pluck('username');

            foreach ($models as $username) {
                $urls[] = url("/model/{$username}");
            }

            $this->info("Collected {$models->count()} model URLs");
        }

        if (in_array($type, ['all', 'tags'])) {
            $tags = Tag::pluck('slug');
            foreach ($tags as $slug) {
                $urls[] = url("/girls/{$slug}");
            }
            $this->info("Collected {$tags->count()} tag URLs");
        }

        $urls = array_slice(array_unique($urls), 0, $limit);

        if (empty($urls)) {
            $this->warn('No URLs to submit.');
            return 0;
        }

        $this->info("Submitting " . count($urls) . " URLs to IndexNow...");

        if ($indexNow->submitUrls($urls)) {
            $this->info('Submission successful!');
            return 0;
        }

        $this->error('Submission failed. Check logs.');
        return 1;
    }
}
