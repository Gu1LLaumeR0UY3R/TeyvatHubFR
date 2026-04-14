<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveBrowserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleDriveController extends Controller
{
    public function __construct(private readonly GoogleDriveBrowserService $driveBrowser)
    {
    }

    public function browse(Request $request): JsonResponse
    {
        $apiKey = (string) config('services.google_drive.api_key', '');
        if ($apiKey === '') {
            return response()->json([
                'ok' => false,
                'message' => 'GOOGLE_DRIVE_API_KEY est manquante.',
            ], 422);
        }

        $folderId = $this->driveBrowser->extractFolderId($request->query('folder_id'));
        if (!$folderId) {
            $folderId = $this->driveBrowser->extractFolderId((string) config('services.google_drive.folder_id', ''));
        }
        if (!$folderId) {
            $folderId = $this->driveBrowser->extractFolderId((string) config('services.google_drive.folder_url', ''));
        }

        if (!$folderId) {
            return response()->json([
                'ok' => false,
                'message' => 'GOOGLE_DRIVE_FOLDER_ID ou GOOGLE_DRIVE_FOLDER_URL est manquant.',
            ], 422);
        }

        $data = $this->driveBrowser->listFolder(
            $apiKey,
            $folderId,
            $request->query('page_token') ? (string) $request->query('page_token') : null,
            (int) $request->query('page_size', 100)
        );

        $status = (int) ($data['status'] ?? 500);

        return response()->json([
            'ok' => (bool) ($data['ok'] ?? false),
            'folder_id' => $folderId,
            'folders' => $data['folders'] ?? [],
            'images' => $data['images'] ?? [],
            'next_page_token' => $data['next_page_token'] ?? null,
            'message' => $data['message'] ?? null,
        ], $status);
    }
}
