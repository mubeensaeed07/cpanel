<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\MovieDownloadLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminMovieController extends AdminModulePostController
{
    protected static function definition(): array
    {
        return [
            'route_prefix' => 'admin.movies',
            'model' => Movie::class,
            'link_model' => MovieDownloadLink::class,
            'foreign_key' => 'movie_id',
            'slug_fallback' => 'movie',
            'label' => 'Movies',
            'entity_singular' => 'movie',
            'route_param' => 'movie',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function extraValidationRules(): array
    {
        return [
            'imdb_id' => ['nullable', 'string', 'max:20'],
            'poster_url' => ['nullable', 'url', 'max:2000'],
            'release_year' => ['nullable', 'string', 'max:20'],
            'imdb_rating' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function extraCreatePayload(array $validated): array
    {
        return [
            'imdb_id' => $this->normalizeNullableString($validated['imdb_id'] ?? null),
            'poster_url' => $this->normalizeNullableString($validated['poster_url'] ?? null),
            'release_year' => $this->normalizeNullableString($validated['release_year'] ?? null),
            'imdb_rating' => $this->normalizeNullableString($validated['imdb_rating'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function extraUpdatePayload(array $validated): array
    {
        return $this->extraCreatePayload($validated);
    }

    public function imdbLookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:150'],
        ]);

        $apiKey = trim((string) config('services.omdb.key'));
        if ($apiKey === '') {
            return response()->json([
                'message' => 'OMDb API key is missing. Add OMDB_API_KEY in your .env and retry.',
            ], 422);
        }

        $query = trim((string) $validated['query']);
        $params = [
            'apikey' => $apiKey,
            'plot' => 'full',
        ];

        if (preg_match('/^tt\d{5,}$/i', $query) === 1) {
            $params['i'] = $query;
        } else {
            $params['t'] = $query;
        }

        $response = Http::timeout(10)->acceptJson()->get('https://www.omdbapi.com/', $params);
        if (! $response->ok()) {
            return response()->json([
                'message' => 'Movie API is unavailable right now. Try again.',
            ], 502);
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];
        if (($payload['Response'] ?? 'False') !== 'True') {
            return response()->json([
                'message' => (string) ($payload['Error'] ?? 'Movie not found.'),
            ], 404);
        }

        $poster = (string) ($payload['Poster'] ?? '');
        if (strcasecmp($poster, 'N/A') === 0) {
            $poster = '';
        }

        return response()->json([
            'title' => $this->normalizeNullableString($payload['Title'] ?? null),
            'description' => $this->normalizeNullableString($payload['Plot'] ?? null),
            'imdb_id' => $this->normalizeNullableString($payload['imdbID'] ?? null),
            'poster_url' => $poster !== '' ? $poster : null,
            'release_year' => $this->normalizeNullableString($payload['Year'] ?? null),
            'imdb_rating' => $this->normalizeNullableString($payload['imdbRating'] ?? null),
        ]);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $out = trim((string) $value);

        if ($out === '' || strcasecmp($out, 'N/A') === 0) {
            return null;
        }

        return $out;
    }
}
