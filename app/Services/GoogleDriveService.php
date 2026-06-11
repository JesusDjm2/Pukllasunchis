<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private Client $client;
    private Drive  $drive;

    public function __construct()
    {
        $credentials = base_path(
            config('services.google_drive.credentials', 'storage/app/google-drive-credentials.json')
        );

        $impersonate = config('services.google_drive.impersonate');

        $this->client = new Client();
        $this->client->setAuthConfig($credentials);
        $this->client->addScope(Drive::DRIVE);
        if ($impersonate) {
            $this->client->setSubject($impersonate);
        }

        $this->drive = new Drive($this->client);
    }

    /**
     * Sube un archivo a una carpeta de Drive.
     * Retorna el file ID de Drive.
     */
    public function upload(string $folderId, UploadedFile $file): string
    {
        $metadata = new DriveFile([
            'name'    => $file->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        $driveFile = $this->drive->files->create($metadata, [
            'data'              => file_get_contents($file->getRealPath()),
            'mimeType'          => $file->getMimeType() ?: 'audio/mpeg',
            'uploadType'        => 'multipart',
            'fields'            => 'id',
            'supportsAllDrives' => true,
        ]);

        return $driveFile->id;
    }

    /**
     * Elimina un archivo de Drive por su ID.
     */
    public function delete(string $fileId): void
    {
        try {
            $this->drive->files->delete($fileId);
            Cache::forget("gdrive_meta_{$fileId}");
        } catch (\Exception $e) {
            Log::warning("GoogleDrive: no se pudo eliminar {$fileId}: {$e->getMessage()}");
        }
    }

    /**
     * Devuelve metadatos del archivo (cacheados 1 h).
     */
    public function getMeta(string $fileId): array
    {
        return Cache::remember("gdrive_meta_{$fileId}", 3600, function () use ($fileId) {
            $file = $this->drive->files->get($fileId, [
                'fields'            => 'id,name,size,mimeType',
                'supportsAllDrives' => true,
            ]);
            return [
                'name'     => $file->getName(),
                'size'     => (int) $file->getSize(),
                'mimeType' => $file->getMimeType() ?: 'audio/mpeg',
            ];
        });
    }

    /**
     * Hace streaming del archivo desde Drive, con soporte de Range requests.
     * Retorna array con status, body (stream), contentType, contentRange y contentLen.
     */
    public function streamFile(string $fileId, ?string $rangeHeader = null): array
    {
        $token = $this->getAccessToken();

        $headers = ['Authorization' => "Bearer {$token}"];
        if ($rangeHeader) {
            $headers['Range'] = $rangeHeader;
        }

        $guzzle   = new GuzzleClient(['stream' => true, 'allow_redirects' => true]);
        $response = $guzzle->request(
            'GET',
            "https://www.googleapis.com/drive/v3/files/{$fileId}?alt=media",
            ['headers' => $headers, 'http_errors' => false]
        );

        return [
            'status'       => $response->getStatusCode(),
            'body'         => $response->getBody(),
            'contentType'  => $response->getHeader('Content-Type')[0]  ?? 'audio/mpeg',
            'contentRange' => $response->getHeader('Content-Range')[0] ?? null,
            'contentLen'   => $response->getHeader('Content-Length')[0] ?? null,
        ];
    }

    private function getAccessToken(): string
    {
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithAssertion();
        }

        $token = $this->client->getAccessToken();

        if (empty($token['access_token'])) {
            throw new \RuntimeException('No se pudo obtener el access token de Google Drive.');
        }

        return $token['access_token'];
    }
}
