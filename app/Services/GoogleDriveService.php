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
     *
     * $nombrePersonalizado permite guardarlo con otro nombre visible en Drive
     * (ej. Investigaciones lo usa para nombrar "grupo - alumno - trabajo.ext" en vez del
     * nombre original del archivo, para que se pueda identificar navegando la
     * carpeta compartida a mano). Si no se pasa, se usa el nombre original.
     */
    public function upload(string $folderId, UploadedFile $file, ?string $nombrePersonalizado = null): string
    {
        $metadata = new DriveFile([
            'name'    => $nombrePersonalizado ?: $file->getClientOriginalName(),
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
     * ID de la subcarpeta $nombre dentro de $padreId (en una unidad compartida también); la crea si no
     * existe. Se guarda en caché para no buscarla en cada subida.
     */
    public function carpeta(string $padreId, string $nombre): string
    {
        return Cache::rememberForever('gdrive_carpeta_'.md5($padreId.'|'.$nombre), function () use ($padreId, $nombre) {
            $q = sprintf("name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                str_replace("'", "\\'", $nombre), $padreId);
            $existente = $this->drive->files->listFiles([
                'q' => $q, 'fields' => 'files(id)', 'pageSize' => 1,
                'supportsAllDrives' => true, 'includeItemsFromAllDrives' => true,
            ])->getFiles();
            if (! empty($existente)) {
                return $existente[0]->getId();
            }

            return $this->drive->files->create(new DriveFile([
                'name' => $nombre, 'parents' => [$padreId], 'mimeType' => 'application/vnd.google-apps.folder',
            ]), ['fields' => 'id', 'supportsAllDrives' => true])->id;
        });
    }

    /**
     * Elimina un archivo de Drive por su ID. Devuelve false si no se pudo
     * (queda en el log). supportsAllDrives hace falta para archivos dentro de
     * una unidad compartida — sin él Drive responde 404 aunque exista.
     */
    public function delete(string $fileId): bool
    {
        try {
            $this->drive->files->delete($fileId, ['supportsAllDrives' => true]);
            Cache::forget("gdrive_meta_{$fileId}");

            return true;
        } catch (\Exception $e) {
            Log::warning("GoogleDrive: no se pudo eliminar {$fileId}: {$e->getMessage()}");

            return false;
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

        $guzzle   = new GuzzleClient(['allow_redirects' => true]);
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
