<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    protected string $key;
    protected string $host;
    protected string $endpoint = 'https://api.indexnow.org/indexnow';

    public function __construct()
    {
        $this->key = config('services.indexnow.key', '');
        $this->host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'pornguru.cam';
    }

    /**
     * Submit a single URL to IndexNow.
     */
    public function submitUrl(string $url): bool
    {
        return $this->submitUrls([$url]);
    }

    /**
     * Submit multiple URLs to IndexNow (max 10,000 per request).
     */
    public function submitUrls(array $urls): bool
    {
        if (empty($this->key) || empty($urls)) {
            return false;
        }

        // IndexNow accepts max 10,000 URLs per request
        $chunks = array_chunk($urls, 10000);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::timeout(15)->post($this->endpoint, [
                    'host' => $this->host,
                    'key' => $this->key,
                    'keyLocation' => "https://{$this->host}/{$this->key}.txt",
                    'urlList' => $chunk,
                ]);

                if ($response->successful() || $response->status() === 202) {
                    Log::channel('single')->info('IndexNow: submitted ' . count($chunk) . ' URLs', [
                        'status' => $response->status(),
                    ]);
                } else {
                    Log::channel('single')->warning('IndexNow: submission failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'url_count' => count($chunk),
                    ]);
                    return false;
                }
            } catch (\Exception $e) {
                Log::channel('single')->error('IndexNow: exception', [
                    'message' => $e->getMessage(),
                    'url_count' => count($chunk),
                ]);
                return false;
            }
        }

        return true;
    }
}
