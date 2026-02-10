<?php

namespace App\Console\Commands;

use App\Enums\StripchatTag;
use App\Models\Tag;
use Illuminate\Console\Command;

class SyncTags extends Command
{
    protected $signature = 'tags:sync {--seed : Seed tags from StripchatTag enum}';
    protected $description = 'Sync tags from the StripchatTag enum';

    public function handle(): int
    {
        if ($this->option('seed')) {
            return $this->seedFromEnum();
        }

        $this->info('Use --seed to create tags from StripchatTag enum');
        return Command::SUCCESS;
    }

    protected function seedFromEnum(): int
    {
        $this->info('Syncing tags from StripchatTag enum...');

        // Featured tags (popular ones)
        $featuredSlugs = [
            'teens', 'young', 'milfs', 'mature',
            'asian', 'ebony', 'latin', 'white',
            'big-tits', 'small-tits', 'big-ass',
            'anal', 'squirt', 'deepthroat', 'fingering',
            'lovense', 'interactive-toy',
            'new', 'hd',
        ];

        $count = 0;
        $order = 0;

        foreach (StripchatTag::grouped() as $category => $tags) {
            foreach ($tags as $tag) {
                Tag::updateOrCreate(
                    ['slug' => $tag->value],
                    [
                        'name' => $tag->label(),
                        'category' => $category,
                        'sort_order' => $order++,
                        'is_featured' => in_array($tag->value, $featuredSlugs),
                    ]
                );
                $count++;
            }
        }

        $this->info("Created/updated {$count} tags from StripchatTag enum");

        return Command::SUCCESS;
    }
}
