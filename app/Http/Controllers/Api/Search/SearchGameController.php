<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class SearchGameController extends Controller
{
    protected function fallbackCover(): string
    {
        return url('/assets/images/FortuneTiger.webp');
    }

    public function index(Request $request)
    {
        try {
            $searchTerm = trim((string) $request->get('searchTerm', ''));

            $query = Game::query()
                ->leftJoin('providers', 'providers.id', '=', 'games.provider_id')
                ->where('games.status', 1)
                ->select([
                    'games.id',
                    'games.game_name',
                    'games.slug',
                    'games.game_code',
                    'games.cover',
                    'providers.id as provider_id',
                    'providers.name as provider_name',
                    'providers.code as provider_slug',
                ]);

            if ($searchTerm !== '') {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('games.game_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('games.game_code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('games.slug', 'like', '%' . $searchTerm . '%')
                        ->orWhere('providers.name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('providers.code', 'like', '%' . $searchTerm . '%');
                });
            }

            $games = $query
                ->orderBy('games.views', 'desc')
                ->paginate(12)
                ->appends($request->query());

            $games->getCollection()->transform(function ($game) {
                return [
                    'id' => $game->id,
                    'game_name' => $game->game_name,
                    'slug' => $game->slug,
                    'game_code' => $game->game_code,
                    'cover' => $game->cover ? url($game->cover) : $this->fallbackCover(),
                    'provider' => [
                        'id' => $game->provider_id,
                        'name' => $game->provider_name,
                        'slug' => $game->provider_slug,
                    ],
                ];
            });

            return response()->json([
                'games' => $games,
            ], 200);
        } catch (Throwable $e) {
            Log::error('SearchGameController@index failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'searchTerm' => $request->get('searchTerm'),
            ]);

            return response()->json([
                'debug' => true,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }
}