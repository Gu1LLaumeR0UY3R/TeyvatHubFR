<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleDriveBrowserService
{
    private const BASE_URL = 'https://www.googleapis.com/drive/v3/files';

    public function listFolder(string $apiKey, string $folderId, ?string $pageToken = null, int $pageSize = 100): array
    {
        $query = sprintf(
            "'%s' in parents and trashed = false and (mimeType = 'application/vnd.google-apps.folder' or mimeType contains 'image/')",
            addslashes($folderId)
        );

        $response = Http::timeout(20)->get(self::BASE_URL, [
            'key' => $apiKey,
            'q' => $query,
            'fields' => 'nextPageToken,files(id,name,mimeType,thumbnailLink,modifiedTime)',
            'orderBy' => 'folder,name',
            'pageSize' => max(1, min(200, $pageSize)),
            'pageToken' => $pageToken,
            'supportsAllDrives' => 'true',
            'includeItemsFromAllDrives' => 'true',
        ]);

        if ($response->failed()) {
            return [
                'ok' => false,
                'status' => $response->status(),
                'message' => $response->json('error.message') ?: 'Erreur Google Drive API',
                'folders' => [],
                'images' => [],
                'next_page_token' => null,
            ];
        }

        $files = $response->json('files', []);

        $folders = [];
        $images = [];

        foreach ($files as $file) {
            $mime = (string) ($file['mimeType'] ?? '');
            $id = (string) ($file['id'] ?? '');
            $name = (string) ($file['name'] ?? '');

            if ($id === '' || $name === '') {
                continue;
            }

            if ($mime === 'application/vnd.google-apps.folder') {
                $folders[] = [
                    'id' => $id,
                    'name' => $name,
                    'mime_type' => $mime,
                ];
                continue;
            }

            if (str_starts_with($mime, 'image/')) {
                $images[] = [
                    'id' => $id,
                    'name' => $name,
                    'mime_type' => $mime,
                    'thumbnail_url' => "https://drive.google.com/thumbnail?id={$id}&sz=w600",
                    'background_url' => "https://drive.google.com/thumbnail?id={$id}&sz=w2000",
                    'direct_url' => "https://drive.google.com/uc?export=view&id={$id}",
                    'modified_time' => $file['modifiedTime'] ?? null,
                ];
            }
        }

        return [
            'ok' => true,
            'status' => 200,
            'message' => null,
            'folders' => $folders,
            'images' => $images,
            'next_page_token' => $response->json('nextPageToken'),
        ];
    }

    public function extractFolderId(?string $raw): ?string
    {
        $source = trim((string) $raw);
        if ($source === '') {
            return null;
        }

        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $source, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/^[a-zA-Z0-9_-]{20,}$/', $source) === 1) {
            return $source;
        }

        return null;
    }
}
