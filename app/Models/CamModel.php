<?php

namespace App\Models;

use App\Enums\StripchatTag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CamModel extends Model
{
    /**
     * The database connection to use (external cam database - read only)
     */
    protected $connection = 'cam';

    /**
     * The table associated with the model.
     */
    protected $table = 'cam_models';

    protected $fillable = [
        'source_platform',
        'source_id',
        'username',
        'avatar_url',
        'preview_url',
        'snapshot_url',
        'gallery_urls',
        'live_image_url',
        'profile_url',
        'description',
        'age',
        'country',
        'gender',
        'ethnicity',
        'body_type',
        'hair_color',
        'eye_color',
        'height',
        'weight',
        'measurements',
        'sexual_preference',
        'turnons',
        'turnoffs',
        'tags',
        'languages',
        'is_online',
        'stream_status',
        'stream_url',
        'stream_urls',
        'stream_width',
        'stream_height',
        'is_hd',
        'is_vr',
        'viewers_count',
        'favorited_count',
        'rating',
        'goal_message',
        'goal_needed',
        'goal_earned',
        'goal_progress',
        'blocked_countries',
        'blocked_regions',
        'blocked_languages',
        'last_online_at',
        'last_synced_at',
        'discovered_at',
        'creator_id',
    ];

    protected $casts = [
        'tags' => 'array',
        'languages' => 'array',
        'gallery_urls' => 'array',
        'measurements' => 'array',
        'stream_urls' => 'array',
        'blocked_countries' => 'array',
        'blocked_regions' => 'array',
        'blocked_languages' => 'array',
        'is_online' => 'boolean',
        'is_hd' => 'boolean',
        'is_vr' => 'boolean',
        'last_online_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'discovered_at' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Get the linked internal creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    /**
     * Get the source profile (XLove or Stripchat)
     */
    public function sourceProfile()
    {
        if ($this->source_platform === 'stripchat') {
            return StripchatProfile::where('stripchat_id', $this->source_id)->first();
        }

        if ($this->source_platform === 'xlovecam') {
            return XloveProfile::where('xlove_id', $this->source_id)->first();
        }

        return null;
    }

    /**
     * Check if model is blocked for a given country
     */
    public function isBlockedForCountry(string $countryCode): bool
    {
        $blocked = $this->blocked_countries ?? [];
        return in_array(strtolower($countryCode), array_map('strtolower', $blocked));
    }

    /**
     * Scope for online models only
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    /**
     * Scope for models with public stream
     */
    public function scopePublicStream($query)
    {
        return $query->where('stream_status', 'public');
    }

    /**
     * Scope for specific platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('source_platform', $platform);
    }

    /**
     * Scope for specific gender
     */
    public function scopeGender($query, string $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope for specific country
     */
    public function scopeCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to exclude geobanned models for a country
     */
    public function scopeNotBlockedFor($query, string $countryCode)
    {
        return $query->where(function ($q) use ($countryCode) {
            $q->whereNull('blocked_countries')
                ->orWhereRaw("NOT (blocked_countries @> ?)", [json_encode([$countryCode])]);
        });
    }

    /**
     * Scope for models with specific tags
     * Supports both simple tags ("young") and niche/tag format ("girls/young")
     */
    public function scopeWithTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            // Use ILIKE for compatibility with json column (not jsonb)
            $query->whereRaw("tags::text ILIKE ?", ['%"' . $tag . '"%']);
        }
        return $query;
    }

    /**
     * Scope for models speaking specific languages
     */
    public function scopeSpeaksLanguage($query, string $language)
    {
        return $query->whereRaw("languages::text ILIKE ?", ['%"' . $language . '"%']);
    }

    /**
     * Scope for HD streams only
     */
    public function scopeHdOnly($query)
    {
        return $query->where('is_hd', true);
    }

    /**
     * Scope for recently online (within last X seconds)
     */
    public function scopeRecentlyOnline($query, int $seconds = 60)
    {
        return $query->where('last_online_at', '>=', now()->subSeconds($seconds));
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->username;
    }

    /**
     * Get best available image URL
     */
    public function getBestImageUrlAttribute(): string
    {
        // Prefer live snapshot when online
        if ($this->is_online && $this->snapshot_url) {
            return $this->snapshot_url;
        }

        // Fall back to preview/avatar/placeholder
        return $this->preview_url
            ?? $this->avatar_url
            ?? 'https://via.placeholder.com/400x300/1a1a1a/666?text=' . urlencode($this->username ?? 'Model');
    }

    /**
     * Get best quality HLS stream URL from database
     * Prefers the highest resolution URL from stream_urls, falls back to stream_url
     */
    public function getBestStreamUrlAttribute(): ?string
    {
        // First priority: Check stream_urls array for best quality
        if (!empty($this->stream_urls) && is_array($this->stream_urls)) {
            // Quality tiers in descending preference order
            $qualityTiers = ['1080', '4k', '2160', '1440', '720', 'hd', 'high', '480', 'medium', 'sd', '360', 'low'];

            foreach ($qualityTiers as $quality) {
                foreach ($this->stream_urls as $key => $url) {
                    if (!is_string($url) || empty($url)) continue;
                    $keyStr = is_string($key) ? $key : '';
                    if (stripos($keyStr, $quality) !== false || stripos($url, $quality) !== false) {
                        return $url;
                    }
                }
            }

            // No quality match found — return the first valid URL
            foreach ($this->stream_urls as $url) {
                if (is_string($url) && !empty($url)) {
                    return $url;
                }
            }
        }

        // Fallback: Use the single stream_url
        if ($this->stream_url) {
            return $this->stream_url;
        }

        return null;
    }

    /**
     * Get stream aspect ratio from database or default to 16:9
     * For very tall portrait streams, clamp to a reasonable minimum ratio
     */
    public function getStreamAspectRatioAttribute(): string
    {
        if ($this->stream_width && $this->stream_height && $this->stream_height > 0) {
            $ratio = $this->stream_width / $this->stream_height;

            // For extreme portrait ratios (taller than 9:16), clamp to 9:16
            // to prevent absurdly tall players
            if ($ratio < 0.5625) {
                return '9 / 16';
            }

            return $this->stream_width . ' / ' . $this->stream_height;
        }
        return '16 / 9';
    }

    /**
     * Check if this is a portrait/mobile stream
     */
    public function getIsPortraitStreamAttribute(): bool
    {
        if ($this->stream_width && $this->stream_height && $this->stream_height > 0) {
            return $this->stream_height > $this->stream_width;
        }
        return false;
    }

    /**
     * Get tags with their display labels
     * For Stripchat models, uses the StripchatTag enum for proper labels
     * 
     * @return array<array{slug: string, label: string, category: string|null}>
     */
    public function getTagsWithLabels(): array
    {
        $tags = $this->tags ?? [];
        $result = [];

        foreach ($tags as $tagSlug) {
            if ($this->source_platform === 'stripchat') {
                $enum = StripchatTag::fromSlug($tagSlug);
                $result[] = [
                    'slug' => $tagSlug,
                    'label' => $enum?->label() ?? ucwords(str_replace('-', ' ', $tagSlug)),
                    'category' => $enum?->category(),
                ];
            } else {
                // For other platforms, just format the slug nicely
                $result[] = [
                    'slug' => $tagSlug,
                    'label' => ucwords(str_replace(['-', '_'], ' ', $tagSlug)),
                    'category' => null,
                ];
            }
        }

        return $result;
    }

    /**
     * Get tags as StripchatTag enum instances (only for Stripchat models)
     * 
     * @return array<StripchatTag>
     */
    public function getStripchatTagEnums(): array
    {
        if ($this->source_platform !== 'stripchat') {
            return [];
        }

        $tags = $this->tags ?? [];
        $enums = [];

        foreach ($tags as $tagSlug) {
            $enum = StripchatTag::fromSlug($tagSlug);
            if ($enum) {
                $enums[] = $enum;
            }
        }

        return $enums;
    }

    /**
     * Get tags grouped by category (only meaningful for Stripchat models)
     * 
     * @return array<string, array>
     */
    public function getTagsByCategory(): array
    {
        $tagsWithLabels = $this->getTagsWithLabels();
        $grouped = [];

        foreach ($tagsWithLabels as $tag) {
            $category = $tag['category'] ?? 'Other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $tag;
        }

        return $grouped;
    }

    /**
     * Check if model has a specific Stripchat tag
     */
    public function hasStripchatTag(StripchatTag $tag): bool
    {
        return in_array($tag->value, $this->tags ?? []);
    }

    /**
     * Scope for models with a specific Stripchat tag
     */
    public function scopeWithStripchatTag($query, StripchatTag $tag)
    {
        return $query->whereRaw("tags::text ILIKE ?", ['%"' . $tag->value . '"%']);
    }

    /**
     * Scope for models with any of the given Stripchat tags
     * 
     * @param array<StripchatTag> $tags
     */
    public function scopeWithAnyStripchatTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereRaw("tags::text ILIKE ?", ['%"' . $tag->value . '"%']);
            }
        });
    }

    /**
     * Scope for models in a specific niche (girls, couples, men, trans)
     * Uses gender from cam_models or broadcast_gender from stripchat_profiles
     */
    public function scopeInNiche($query, string $niche)
    {
        // Map niche URL to gender database values
        $genderMap = [
            'girls' => ['female'],
            'men' => ['male'], 
            'couples' => ['couple', 'group'],
            'trans' => ['trans'],
        ];

        $genders = $genderMap[$niche] ?? [$niche];
        
        // For "girls" we can use cam_models.gender directly (most common case)
        if ($niche === 'girls') {
            return $query->whereRaw("LOWER(gender) = ?", ['female']);
        }
        
        // For other niches, join with stripchat_profiles.broadcast_gender
        return $query->whereExists(function ($subquery) use ($genders) {
            $subquery->select(\DB::raw(1))
                ->from('stripchat_profiles')
                ->whereColumn('stripchat_profiles.username', 'cam_models.username')
                ->whereIn(\DB::raw('LOWER(stripchat_profiles.broadcast_gender)'), array_map('strtolower', $genders));
        });
    }

    /**
     * Scope for models with a niche+tag combination (e.g., "girls" + "young" = "girls/young")
     * Uses PostgreSQL json_array_elements_text() for proper JSON array searching
     */
    public function scopeWithNicheTag($query, string $niche, string $tag)
    {
        // First filter by niche (gender)
        $query->inNiche($niche);
        
        // Full tag format: "girls/young"
        $fullTag = $niche . '/' . $tag;
        
        // Use EXISTS with json_array_elements_text for proper JSON array search
        return $query->whereExists(function ($subquery) use ($fullTag, $tag, $niche) {
            $subquery->select(\DB::raw(1))
                ->from('stripchat_profiles')
                ->whereColumn('stripchat_profiles.username', 'cam_models.username')
                ->whereRaw("EXISTS (
                    SELECT 1 FROM json_array_elements_text(stripchat_profiles.tags) AS elem
                    WHERE elem = ? 
                       OR elem LIKE ?
                       OR elem LIKE ?
                )", [$fullTag, $niche . '/%-' . $tag, $niche . '/' . $tag . '-%']);
        });
    }

    /**
     * Scope for models with any matching tag (simpler search)
     * Uses PostgreSQL json_array_elements_text() for proper JSON array searching
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereExists(function ($subquery) use ($tag) {
            $subquery->select(\DB::raw(1))
                ->from('stripchat_profiles')
                ->whereColumn('stripchat_profiles.username', 'cam_models.username')
                ->whereRaw("EXISTS (
                    SELECT 1 FROM json_array_elements_text(stripchat_profiles.tags) AS elem
                    WHERE elem LIKE ? OR elem LIKE ?
                )", ['%/' . $tag, '%-' . $tag]);
        });
    }
}
