<?php

namespace App\Jobs;

use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationDownloadLink;
use App\Models\Game;
use App\Models\GameDownloadLink;
use App\Models\Movie;
use App\Models\MovieDownloadLink;
use App\Models\Series;
use App\Models\SeriesDownloadLink;
use App\Models\Software;
use App\Models\SoftwareDownloadLink;
use App\Models\Wallpaper;
use App\Models\WallpaperDownloadLink;
use App\Models\WebTv;
use App\Models\WebTvDownloadLink;
use App\Services\GoogleDriveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SyncAdminGoogleDriveContent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $adminId) {}

    public function handle(GoogleDriveService $drive): void
    {
        $admin = Admin::query()->findOrFail($this->adminId);
        $admin->update([
            'gdrive_sync_status' => 'running',
            'gdrive_last_error' => null,
        ]);

        try {
            if (! $admin->connect_g_drive) {
                throw new RuntimeException('Connect G Drive is disabled for this admin.');
            }

            $files = $drive->listFilesRecursively();
            DB::transaction(function () use ($files, $admin): void {
                foreach ($files as $file) {
                    $this->importOneFile($admin, $file);
                }
            });

            $admin->update([
                'gdrive_sync_status' => 'completed',
                'gdrive_last_error' => null,
                'gdrive_last_synced_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $admin->update([
                'gdrive_sync_status' => 'failed',
                'gdrive_last_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $file
     */
    private function importOneFile(Admin $admin, array $file): void
    {
        $map = $this->resolveModule($file);
        if ($map === null) {
            return;
        }

        $fileId = (string) ($file['id'] ?? '');
        $name = (string) ($file['name'] ?? '');
        if ($fileId === '' || $name === '') {
            return;
        }

        $baseName = pathinfo($name, PATHINFO_FILENAME);
        $title = trim($baseName !== '' ? $baseName : $name);
        $slugBase = Str::slug($title);
        if ($slugBase === '') {
            $slugBase = 'item';
        }
        $slug = $slugBase.'-'.substr(md5($fileId), 0, 8);

        $url = (string) ($file['webViewLink'] ?? '');
        if ($url === '') {
            $url = sprintf('https://drive.google.com/file/d/%s/view', $fileId);
        }

        $description = 'Imported from Google Drive'.(isset($file['_path']) ? ': '.(string) $file['_path'] : '');
        $modelClass = $map['model'];
        $content = $modelClass::query()->firstOrCreate(
            ['created_by' => (int) $admin->getKey(), 'slug' => $slug],
            [
                'title' => $title,
                'description' => $description,
                'status' => 'published',
                'created_by' => (int) $admin->getKey(),
                'slug' => $slug,
            ]
        );

        $linkClass = $map['link_model'];
        $fk = $map['fk'];
        $exists = $linkClass::query()
            ->where($fk, $content->getKey())
            ->where('file_url', $url)
            ->exists();
        if (! $exists) {
            $sortOrder = (int) $linkClass::query()->where($fk, $content->getKey())->max('sort_order') + 1;
            $linkClass::query()->create([
                $fk => $content->getKey(),
                'label' => 'Google Drive',
                'file_name' => $name,
                'file_url' => $url,
                'file_size' => isset($file['size']) ? (int) $file['size'] : null,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $file
     * @return array{model: class-string<Model>, link_model: class-string<Model>, fk: string}|null
     */
    private function resolveModule(array $file): ?array
    {
        $path = strtolower((string) ($file['_path'] ?? ''));
        $name = strtolower((string) ($file['name'] ?? ''));
        $scope = $path.' '.$name;

        $rules = [
            'movies' => ['movie'],
            'series' => ['series', 'episode'],
            'web_tv' => ['web tv', 'web-tv', 'channel'],
            'wallpapers' => ['wallpaper', 'poster'],
            'games' => ['game'],
            'software' => ['software'],
            'applications' => ['application', 'app'],
        ];

        $matched = null;
        foreach ($rules as $module => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($scope, $keyword)) {
                    $matched = $module;
                    break 2;
                }
            }
        }
        if ($matched === null) {
            return null;
        }

        return match ($matched) {
            'movies' => ['model' => Movie::class, 'link_model' => MovieDownloadLink::class, 'fk' => 'movie_id'],
            'series' => ['model' => Series::class, 'link_model' => SeriesDownloadLink::class, 'fk' => 'series_id'],
            'web_tv' => ['model' => WebTv::class, 'link_model' => WebTvDownloadLink::class, 'fk' => 'web_tv_id'],
            'wallpapers' => ['model' => Wallpaper::class, 'link_model' => WallpaperDownloadLink::class, 'fk' => 'wallpaper_id'],
            'games' => ['model' => Game::class, 'link_model' => GameDownloadLink::class, 'fk' => 'game_id'],
            'software' => ['model' => Software::class, 'link_model' => SoftwareDownloadLink::class, 'fk' => 'software_id'],
            'applications' => ['model' => Application::class, 'link_model' => ApplicationDownloadLink::class, 'fk' => 'application_id'],
            default => null,
        };
    }
}
