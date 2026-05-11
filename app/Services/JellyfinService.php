<?php

namespace App\Services;

use App\Models\JellyfinConnection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class JellyfinService
{
    public function getConnection(): ?JellyfinConnection
    {
        return JellyfinConnection::query()->first();
    }

    public function saveConnection(string $serverUrl, string $apiKey, string $userId): JellyfinConnection
    {
        $serverUrl = rtrim(trim($serverUrl), '/');
        $apiKey = trim($apiKey);
        $userId = trim($userId);
        if ($serverUrl === '' || $apiKey === '' || $userId === '') {
            throw new RuntimeException('Jellyfin Server URL, API Key, and User ID are required.');
        }

        $info = Http::timeout(10)
            ->withHeaders(['X-Emby-Token' => $apiKey])
            ->get($serverUrl.'/System/Info');

        if (! $info->ok()) {
            throw new RuntimeException('Could not connect to Jellyfin. Check URL/API key.');
        }
        /** @var array<string, mixed> $payload */
        $payload = $info->json() ?? [];

        $conn = JellyfinConnection::query()->firstOrNew();
        $conn->server_url = $serverUrl;
        $conn->api_key = $apiKey;
        $conn->user_id = $userId;
        $conn->server_name = isset($payload['ServerName']) ? (string) $payload['ServerName'] : null;
        $conn->save();

        return $conn;
    }

    public function disconnect(): void
    {
        JellyfinConnection::query()->delete();
    }

    public function triggerLibraryScan(): void
    {
        $conn = $this->requireConnection();
        $response = Http::timeout(20)
            ->withHeaders(['X-Emby-Token' => $conn->api_key])
            ->post($conn->server_url.'/Library/Refresh');
        if (! $response->successful()) {
            throw new RuntimeException('Failed to trigger Jellyfin library scan.');
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchMediaItems(): array
    {
        $conn = $this->requireConnection();
        $response = Http::timeout(30)
            ->withHeaders(['X-Emby-Token' => $conn->api_key])
            ->get($conn->server_url.'/Users/'.$conn->user_id.'/Items', [
                'Recursive' => 'true',
                'IncludeItemTypes' => 'Movie,Series,Episode',
                'Fields' => 'Overview,CommunityRating,ProductionYear,ProviderIds,Path',
                'SortBy' => 'DateCreated',
                'SortOrder' => 'Descending',
                'Limit' => 5000,
            ]);
        if (! $response->ok()) {
            throw new RuntimeException('Failed to fetch media items from Jellyfin.');
        }
        /** @var array<string, mixed> $payload */
        $payload = $response->json() ?? [];
        /** @var list<array<string,mixed>> $items */
        $items = $payload['Items'] ?? [];

        return $items;
    }

    public function posterUrl(string $itemId): ?string
    {
        $conn = $this->requireConnection();
        if ($itemId === '') {
            return null;
        }

        return $conn->server_url.'/Items/'.$itemId.'/Images/Primary?quality=90&tag=0';
    }

    private function requireConnection(): JellyfinConnection
    {
        $conn = $this->getConnection();
        if (! $conn) {
            throw new RuntimeException('Jellyfin is not connected yet.');
        }

        return $conn;
    }
}
