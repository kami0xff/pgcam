<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrelanderApiController extends Controller
{
    /**
     * GET /api/v1/cam-models/online
     * Top online models sorted by viewers.
     */
    public function online(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 36), 48);

        $data = cache()->remember("prelander:online:{$limit}", 120, function () use ($limit) {
            return CamModel::where('is_online', true)
                ->whereNotNull('snapshot_url')
                ->where('snapshot_url', '!=', '')
                ->orderByRaw("CASE WHEN source_platform = 'chaturbate' THEN 1 ELSE 0 END ASC")
                ->orderBy('viewers_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn ($m) => $this->transformModel($m))
                ->values();
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'cached_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v1/cam-models/near-goal
     * Online models with active goals, sorted by closest to completion.
     */
    public function nearGoal(Request $request): JsonResponse
    {
        $minProgress = max(0, min(99, (int) $request->input('min_progress', 20)));
        $limit = min((int) $request->input('limit', 48), 64);

        $data = cache()->remember("prelander:goals:{$minProgress}:{$limit}", 120, function () use ($minProgress, $limit) {
            return CamModel::where('is_online', true)
                ->whereNotNull('goal_progress')
                ->where('goal_progress', '>=', $minProgress)
                ->where('goal_progress', '<', 100)
                ->whereNotNull('snapshot_url')
                ->where('snapshot_url', '!=', '')
                ->orderBy('goal_progress', 'desc')
                ->limit($limit)
                ->get()
                ->map(fn ($m) => $this->transformModel($m))
                ->values();
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * GET /api/v1/cam-models/stats
     * Aggregate stats for the hero section.
     */
    public function stats(Request $request): JsonResponse
    {
        $data = cache()->remember('prelander:stats', 120, function () {
            return [
                'online_count' => CamModel::where('is_online', true)->count(),
                'total_count' => CamModel::count(),
                'total_viewers' => (int) CamModel::where('is_online', true)->sum('viewers_count'),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }

    /** @param CamModel $m */
    private function transformModel($m): array
    {
        return [
            'username' => $m->username,
            'age' => $m->age,
            'gender' => $m->gender,
            'country' => $m->country,
            'platform' => $m->source_platform,
            'viewers_count' => $m->viewers_count,
            'is_online' => $m->is_online,
            'snapshot_url' => $m->snapshot_url,
            'preview_url' => $m->preview_url,
            'avatar_url' => $m->avatar_url,
            'stream_url' => $m->best_stream_url,
            'profile_url' => $m->affiliate_url,
            'goal_message' => $m->goal_message,
            'goal_progress' => $m->goal_progress,
            'goal_needed' => $m->goal_needed,
            'goal_earned' => $m->goal_earned,
            'stream_title' => $m->stream_title,
            'rating' => $m->rating ? number_format((float) $m->rating, 1) : null,
            'is_hd' => $m->is_hd,
            'tags' => array_slice($m->tags ?? [], 0, 8),
        ];
    }
}
