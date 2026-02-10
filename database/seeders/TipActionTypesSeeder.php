<?php

namespace Database\Seeders;

use App\Models\TipActionType;
use Illuminate\Database\Seeder;

class TipActionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            // Tease category
            [
                'name' => 'Flash',
                'slug' => 'flash',
                'emoji' => '💝',
                'category' => 'tease',
                'suggested_min_tokens' => 25,
                'suggested_max_tokens' => 75,
                'description' => 'Quick flash of body',
                'sort_order' => 1,
            ],
            [
                'name' => 'Boobs Flash',
                'slug' => 'boobs-flash',
                'emoji' => '🍒',
                'category' => 'tease',
                'suggested_min_tokens' => 20,
                'suggested_max_tokens' => 50,
                'description' => 'Show boobs briefly',
                'sort_order' => 2,
            ],
            [
                'name' => 'Ass Flash',
                'slug' => 'ass-flash',
                'emoji' => '🍑',
                'category' => 'tease',
                'suggested_min_tokens' => 20,
                'suggested_max_tokens' => 50,
                'description' => 'Show ass briefly',
                'sort_order' => 3,
            ],
            [
                'name' => 'Pussy Flash',
                'slug' => 'pussy-flash',
                'emoji' => '🌸',
                'category' => 'tease',
                'suggested_min_tokens' => 30,
                'suggested_max_tokens' => 80,
                'description' => 'Show pussy briefly',
                'sort_order' => 4,
            ],

            // Dance category
            [
                'name' => 'Dance',
                'slug' => 'dance',
                'emoji' => '💃',
                'category' => 'dance',
                'suggested_min_tokens' => 15,
                'suggested_max_tokens' => 40,
                'description' => 'Sexy dance moves',
                'sort_order' => 10,
            ],
            [
                'name' => 'Twerk',
                'slug' => 'twerk',
                'emoji' => '🔥',
                'category' => 'dance',
                'suggested_min_tokens' => 20,
                'suggested_max_tokens' => 50,
                'description' => 'Twerking performance',
                'sort_order' => 11,
            ],
            [
                'name' => 'Striptease',
                'slug' => 'striptease',
                'emoji' => '👙',
                'category' => 'dance',
                'suggested_min_tokens' => 40,
                'suggested_max_tokens' => 100,
                'description' => 'Slow strip tease',
                'sort_order' => 12,
            ],

            // Interactive category
            [
                'name' => 'Kiss',
                'slug' => 'kiss',
                'emoji' => '💋',
                'category' => 'interactive',
                'suggested_min_tokens' => 5,
                'suggested_max_tokens' => 15,
                'description' => 'Blow a kiss',
                'sort_order' => 20,
            ],
            [
                'name' => 'Lick Lips',
                'slug' => 'lick-lips',
                'emoji' => '👅',
                'category' => 'interactive',
                'suggested_min_tokens' => 10,
                'suggested_max_tokens' => 25,
                'description' => 'Lick lips seductively',
                'sort_order' => 21,
            ],
            [
                'name' => 'Wink',
                'slug' => 'wink',
                'emoji' => '😉',
                'category' => 'interactive',
                'suggested_min_tokens' => 5,
                'suggested_max_tokens' => 10,
                'description' => 'Cute wink',
                'sort_order' => 22,
            ],
            [
                'name' => 'Say My Name',
                'slug' => 'say-my-name',
                'emoji' => '🗣️',
                'category' => 'interactive',
                'suggested_min_tokens' => 10,
                'suggested_max_tokens' => 30,
                'description' => 'Say your name out loud',
                'sort_order' => 23,
            ],
            [
                'name' => 'Moan',
                'slug' => 'moan',
                'emoji' => '😩',
                'category' => 'interactive',
                'suggested_min_tokens' => 15,
                'suggested_max_tokens' => 35,
                'description' => 'Moan for you',
                'sort_order' => 24,
            ],

            // Special shows category
            [
                'name' => 'Oil Show',
                'slug' => 'oil-show',
                'emoji' => '💦',
                'category' => 'special',
                'suggested_min_tokens' => 80,
                'suggested_max_tokens' => 200,
                'description' => 'Oil all over body',
                'sort_order' => 30,
            ],
            [
                'name' => 'Feet Show',
                'slug' => 'feet-show',
                'emoji' => '🦶',
                'category' => 'special',
                'suggested_min_tokens' => 30,
                'suggested_max_tokens' => 70,
                'description' => 'Show and play with feet',
                'sort_order' => 31,
            ],
            [
                'name' => 'Spanking',
                'slug' => 'spanking',
                'emoji' => '👋',
                'category' => 'special',
                'suggested_min_tokens' => 20,
                'suggested_max_tokens' => 50,
                'description' => 'Spank ass',
                'sort_order' => 32,
            ],
            [
                'name' => 'Fingering',
                'slug' => 'fingering',
                'emoji' => '🤞',
                'category' => 'special',
                'suggested_min_tokens' => 50,
                'suggested_max_tokens' => 150,
                'description' => 'Finger play',
                'sort_order' => 33,
            ],
            [
                'name' => 'Toy Play',
                'slug' => 'toy-play',
                'emoji' => '🎀',
                'category' => 'special',
                'suggested_min_tokens' => 60,
                'suggested_max_tokens' => 200,
                'description' => 'Play with toy',
                'sort_order' => 34,
            ],
            [
                'name' => 'Close Up',
                'slug' => 'close-up',
                'emoji' => '🔍',
                'category' => 'special',
                'suggested_min_tokens' => 25,
                'suggested_max_tokens' => 60,
                'description' => 'Close up camera view',
                'sort_order' => 35,
            ],

            // Outfit category
            [
                'name' => 'Change Outfit',
                'slug' => 'change-outfit',
                'emoji' => '👗',
                'category' => 'outfit',
                'suggested_min_tokens' => 30,
                'suggested_max_tokens' => 80,
                'description' => 'Change into different outfit',
                'sort_order' => 40,
            ],
            [
                'name' => 'Wear Stockings',
                'slug' => 'wear-stockings',
                'emoji' => '🧦',
                'category' => 'outfit',
                'suggested_min_tokens' => 25,
                'suggested_max_tokens' => 60,
                'description' => 'Put on stockings',
                'sort_order' => 41,
            ],
            [
                'name' => 'High Heels',
                'slug' => 'high-heels',
                'emoji' => '👠',
                'category' => 'outfit',
                'suggested_min_tokens' => 20,
                'suggested_max_tokens' => 50,
                'description' => 'Put on high heels',
                'sort_order' => 42,
            ],
        ];

        foreach ($actions as $action) {
            TipActionType::updateOrCreate(
                ['slug' => $action['slug']],
                $action
            );
        }

        $this->command->info('Created ' . count($actions) . ' tip action types');
    }
}
