<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Movie;
use App\Models\MovieDownloadLink;
use App\Models\Series;
use App\Models\SeriesDownloadLink;
use App\Services\JellyfinService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class SyncAdminFromJellyfin implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $adminId) {}

    public function handle(JellyfinService $jellyfin): void
    {
        $admin = Admin::query()->findOrFail($this->adminId);
        $admin->update([
            'jellyfin_sync_status' => 'running',
            'jellyfin_last_error' => null,
        ]);

        try {
            if (! $admin->connect_jellyfin) {
                throw new \RuntimeException('Connect Jellyfin is disabled for this admin.');
            }

            $jellyfin->triggerLibraryScan();
            $items = $jellyfin->fetchMediaItems();
            foreach ($items as $item) {
                $this->importItem($admin, $item, $jellyfin);
            }

            $admin->update([
                'jellyfin_sync_status' => 'completed',
                'jellyfin_last_error' => null,
                'jellyfin_last_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $admin->update([
                'jellyfin_sync_status' => 'failed',
                'jellyfin_last_error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * @param  array<string,mixed>  $item
     */
    private function importItem(Admin $admin, array $item, JellyfinService $jellyfin): void
    {
        $type = (string) ($item['Type'] ?? '');
        $itemId = (string) ($item['Id'] ?? '');
        $name = trim((string) ($item['Name'] ?? ''));
        if ($itemId === '' || $name === '') {
            return;
        }

        $overview = (string) ($item['Overview'] ?? '');
        $rating = isset($item['CommunityRating']) ? (string) $item['CommunityRating'] : null;
        $year = isset($item['ProductionYear']) ? (string) $item['ProductionYear'] : null;
        $slug = Str::slug($name).'-jf-'.substr(md5($itemId), 0, 8);
        $poster = $jellyfin->posterUrl($itemId);
        $path = isset($item['Path']) ? (string) $item['Path'] : null;

        if ($type === 'Movie') {
            $movie = Movie::query()->firstOrCreate(
                ['created_by' => (int) $admin->getKey(), 'slug' => $slug],
                [
                    'title' => $name,
                    'slug' => $slug,
                    'description' => $overview !== '' ? $overview : 'Imported from Jellyfin',
                    'status' => 'published',
                    'created_by' => (int) $admin->getKey(),
                    'imdb_id' => isset($item['ProviderIds']['Imdb']) ? (string) $item['ProviderIds']['Imdb'] : null,
                    'poster_url' => $poster,
                    'release_year' => $year,
                    'imdb_rating' => $rating,
                ]
            );
            $this->ensureLink(MovieDownloadLink::class, 'movie_id', (int) $movie->getKey(), $path, $name);

            return;
        }

        if ($type === 'Series' || $type === 'Episode') {
            $series = Series::query()->firstOrCreate(
                ['created_by' => (int) $admin->getKey(), 'slug' => $slug],
                [
                    'title' => $name,
                    'slug' => $slug,
                    'description' => $overview !== '' ? $overview : 'Imported from Jellyfin',
                    'status' => 'published',
                    'created_by' => (int) $admin->getKey(),
                ]
            );
            $this->ensureLink(SeriesDownloadLink::class, 'series_id', (int) $series->getKey(), $path, $name);
        }
    }

    /**
     * @param  class-string<Model>  $linkClass
     */
    private function ensureLink(string $linkClass, string $fk, int $contentId, ?string $path, string $name): void
    {
        $url = $path ?: 'jellyfin://'.$name;
        $exists = $linkClass::query()
            ->where($fk, $contentId)
            ->where('file_url', $url)
            ->exists();
        if ($exists) {
            return;
        }

        $sortOrder = (int) $linkClass::query()->where($fk, $contentId)->max('sort_order') + 1;
        $linkClass::query()->create([
            $fk => $contentId,
            'label' => 'Jellyfin',
            'file_name' => $name,
            'file_url' => $url,
            'file_size' => null,
            'sort_order' => $sortOrder,
        ]);
    }
}
