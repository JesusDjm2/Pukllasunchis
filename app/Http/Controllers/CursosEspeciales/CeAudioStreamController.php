<?php

namespace App\Http\Controllers\CursosEspeciales;

use App\Http\Controllers\Controller;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CeAudioStreamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function stream(Request $request, string $fileId): StreamedResponse
    {
        /** @var GoogleDriveService $drive */
        $drive = app(GoogleDriveService::class);
        $range = $request->header('Range');

        try {
            $meta   = $drive->getMeta($fileId);
            $result = $drive->streamFile($fileId, $range);

            $isPartial = $range && $result['status'] === 206;
            $status    = $isPartial ? 206 : 200;

            $headers = [
                'Content-Type'  => $meta['mimeType'],
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'private, max-age=3600',
            ];

            if ($isPartial && $result['contentRange']) {
                $headers['Content-Range']  = $result['contentRange'];
                $headers['Content-Length'] = $result['contentLen'];
            } else {
                $headers['Content-Length'] = $meta['size'];
            }

            $body = $result['body'];

            return response()->stream(function () use ($body) {
                while (!$body->eof()) {
                    echo $body->read(8192);
                    flush();
                }
            }, $status, $headers);

        } catch (\Exception $e) {
            abort(404);
        }
    }
}
