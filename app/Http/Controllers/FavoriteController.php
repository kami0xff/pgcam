<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite status for a model
     */
    public function toggle(Request $request, CamModel $model)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to favorite models',
                'requiresAuth' => true,
            ], 401);
        }

        $isFavorited = Auth::user()->toggleFavorite($model);

        return response()->json([
            'success' => true,
            'isFavorited' => $isFavorited,
            'message' => $isFavorited ? 'Added to favorites' : 'Removed from favorites',
        ]);
    }

    /**
     * Get user's favorites (for API)
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'favorites' => [],
            ]);
        }

        $favorites = Auth::user()->favoriteCamModelIds();

        return response()->json([
            'success' => true,
            'favorites' => $favorites,
        ]);
    }
}
