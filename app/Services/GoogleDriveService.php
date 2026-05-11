<?php

namespace App\Services;

use App\Models\GoogleDriveConnection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleDriveService
{
    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.readonly https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.$query;
    }

    public function exchangeCode(string $code): GoogleDriveConnection
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->ok()) {
            throw new RuntimeException('Google OAuth token exchange failed.');
        }

        /** @var array<string, mixed> $token */
        $token = $response->json() ?? [];
        $accessToken = (string) ($token['access_token'] ?? '');
        if ($accessToken === '') {
            throw new RuntimeException('Google OAuth returned no access token.');
        }

        $profile = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
        /** @var array<string, mixed> $user */
        $user = $profile->ok() ? ($profile->json() ?? []) : [];

        $conn = GoogleDriveConnection::query()->firstOrNew(['provider' => 'google_drive']);
        $conn->access_token = $accessToken;
        if (! empty($token['refresh_token'])) {
            $conn->refresh_token = (string) $token['refresh_token'];
        }
        $expiresIn = (int) ($token['expires_in'] ?? 0);
        $conn->token_expires_at = $expiresIn > 0 ? Carbon::now()->addSeconds($expiresIn - 60) : null;
        $conn->google_email = isset($user['email']) ? (string) $user['email'] : null;
        $conn->google_name = isset($user['name']) ? (string) $user['name'] : null;
        $conn->save();

        return $conn;
    }

    public function getConnection(): ?GoogleDriveConnection
    {
        return GoogleDriveConnection::query()->where('provider', 'google_drive')->first();
    }

    public function disconnect(): void
    {
        GoogleDriveConnection::query()->where('provider', 'google_drive')->delete();
    }

    public function ensureAccessToken(): string
    {
        $conn = $this->getConnection();
        if (! $conn) {
            throw new RuntimeException('Google Drive is not connected yet.');
        }

        if ($conn->token_expires_at === null || $conn->token_expires_at->isFuture()) {
            return $conn->access_token;
        }

        if (! $conn->refresh_token) {
            throw new RuntimeException('Google refresh token missing. Reconnect Google Drive.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $conn->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->ok()) {
            throw new RuntimeException('Could not refresh Google Drive access token.');
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];
        $accessToken = (string) ($payload['access_token'] ?? '');
        if ($accessToken === '') {
            throw new RuntimeException('Google token refresh returned no access token.');
        }

        $conn->access_token = $accessToken;
        $expiresIn = (int) ($payload['expires_in'] ?? 0);
        $conn->token_expires_at = $expiresIn > 0 ? Carbon::now()->addSeconds($expiresIn - 60) : null;
        $conn->save();

        return $conn->access_token;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listFilesRecursively(): array
    {
        $rootFolder = trim((string) config('services.google_drive.root_folder_id'));
        if ($rootFolder === '') {
            throw new RuntimeException('GOOGLE_DRIVE_ROOT_FOLDER_ID is not configured.');
        }

        $token = $this->ensureAccessToken();
        $files = [];
        $queue = [[$rootFolder, '']];

        while ($queue !== []) {
            [$folderId, $pathPrefix] = array_shift($queue);
            $pageToken = null;
            do {
                $query = sprintf("'%s' in parents and trashed = false", addslashes((string) $folderId));
                $response = Http::withToken($token)
                    ->get('https://www.googleapis.com/drive/v3/files', [
                        'q' => $query,
                        'fields' => 'nextPageToken,files(id,name,mimeType,size,webViewLink,parents,modifiedTime)',
                        'pageSize' => 1000,
                        'supportsAllDrives' => 'true',
                        'includeItemsFromAllDrives' => 'true',
                        'pageToken' => $pageToken,
                    ]);

                if (! $response->ok()) {
                    throw new RuntimeException('Google Drive file listing failed.');
                }
                /** @var array<string, mixed> $payload */
                $payload = $response->json() ?? [];
                /** @var list<array<string, mixed>> $items */
                $items = $payload['files'] ?? [];
                foreach ($items as $item) {
                    $name = (string) ($item['name'] ?? '');
                    $mimeType = (string) ($item['mimeType'] ?? '');
                    $currentPath = ltrim($pathPrefix.'/'.$name, '/');
                    if ($mimeType === 'application/vnd.google-apps.folder') {
                        $queue[] = [(string) ($item['id'] ?? ''), $currentPath];

                        continue;
                    }
                    $item['_path'] = $currentPath;
                    $files[] = $item;
                }
                $pageToken = isset($payload['nextPageToken']) ? (string) $payload['nextPageToken'] : null;
            } while ($pageToken !== null && $pageToken !== '');
        }

        return $files;
    }

    private function clientId(): string
    {
        $v = trim((string) config('services.google_drive.client_id'));
        if ($v === '') {
            throw new RuntimeException('GOOGLE_DRIVE_CLIENT_ID is missing.');
        }

        return $v;
    }

    private function clientSecret(): string
    {
        $v = trim((string) config('services.google_drive.client_secret'));
        if ($v === '') {
            throw new RuntimeException('GOOGLE_DRIVE_CLIENT_SECRET is missing.');
        }

        return $v;
    }

    private function redirectUri(): string
    {
        $v = trim((string) config('services.google_drive.redirect_uri'));
        if ($v === '') {
            throw new RuntimeException('GOOGLE_DRIVE_REDIRECT_URI is missing.');
        }

        return $v;
    }
}
