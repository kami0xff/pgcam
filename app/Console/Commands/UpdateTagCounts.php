<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\Country;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateTagCounts extends Command
{
    protected $signature = 'tags:update-counts {--countries : Also update country counts}';
    protected $description = 'Update model counts for tags and countries from CamModel database';

    public function handle(): int
    {
        $this->updateTagCounts();

        if ($this->option('countries')) {
            $this->updateCountryCounts();
        }

        $this->info('Counts updated successfully!');
        return Command::SUCCESS;
    }

    protected function updateTagCounts(): void
    {
        $this->info('Updating tag model counts...');

        $tags = Tag::all();
        $bar = $this->output->createProgressBar($tags->count());
        $bar->start();

        foreach ($tags as $tag) {
            try {
                // Count models that have this tag (in any format)
                $count = CamModel::on('cam')
                    ->where(function ($q) use ($tag) {
                        // Search for tag in various formats
                        $q->whereRaw("tags::text ILIKE ?", ['%"' . $tag->slug . '"%'])
                          ->orWhereRaw("tags::text ILIKE ?", ['%/' . $tag->slug . '"%'])
                          ->orWhereRaw("tags::text ILIKE ?", ['%"' . $tag->name . '"%']);
                    })
                    ->count();

                $tag->update(['models_count' => $count]);
            } catch (\Exception $e) {
                $this->warn("Error counting for {$tag->slug}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated counts for {$tags->count()} tags");
    }

    protected function updateCountryCounts(): void
    {
        $this->info('Updating country model counts...');

        $countries = Country::all();
        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        foreach ($countries as $country) {
            try {
                $count = CamModel::on('cam')
                    ->where(function ($q) use ($country) {
                        $q->where('country', $country->name)
                          ->orWhere('country', $country->code);
                    })
                    ->count();

                $country->update(['models_count' => $count]);
            } catch (\Exception $e) {
                $this->warn("Error counting for {$country->name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Updated counts for {$countries->count()} countries");
    }
}
