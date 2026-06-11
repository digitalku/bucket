<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    private const ALLOWED_MIME = [
        // Images
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'image/svg+xml', 'image/bmp', 'image/tiff', 'image/avif',
        // Videos
        'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime',
        'video/x-msvideo', 'video/x-matroska', 'video/3gpp',
        'video/mpeg', 'video/x-ms-wmv',
    ];

    public function index()
    {
        $maxMb      = (int) config('app.max_upload_mb', 200);
        $phpLimit   = $this->phpUploadLimit();
        $warning    = $maxMb > $phpLimit ? "MAX_UPLOAD_MB ({$maxMb}MB) exceeds PHP upload_max_filesize ({$phpLimit}MB). Uploads above {$phpLimit}MB will fail." : null;

        return view('upload', compact('maxMb', 'warning'));
    }

    public function store(Request $request)
    {
        $maxMb  = (int) config('app.max_upload_mb', 200);
        $maxKb  = $maxMb * 1024;
        $userId = session('auth_user_id');

        $request->validate([
            'files'   => 'required|array|min:1',
            'files.*' => "required|file|max:{$maxKb}",
        ]);

        $results = [];

        foreach ($request->file('files') as $uploadedFile) {
            $mime = $uploadedFile->getMimeType();

            if (! in_array($mime, self::ALLOWED_MIME, true)) {
                $results[] = [
                    'name'    => $uploadedFile->getClientOriginalName(),
                    'success' => false,
                    'message' => 'Only image and video files are allowed.',
                ];
                continue;
            }

            $originalName = $uploadedFile->getClientOriginalName();
            $filename     = $this->uniqueFilename($originalName);
            $year         = date('Y');
            $month        = date('m');
            $path         = "{$year}/{$month}/{$filename}";

            Storage::disk()->put($path, file_get_contents($uploadedFile->getRealPath()));

            $file = File::create([
                'user_id'       => $userId,
                'filename'      => $filename,
                'original_name' => $originalName,
                'path'          => $path,
                'mime_type'     => $mime,
                'size'          => $uploadedFile->getSize(),
            ]);

            $url = $file->publicUrl();

            $results[] = [
                'name'     => $originalName,
                'success'  => true,
                'url'      => $url,
                'markdown' => '![](' . $url . ')',
            ];
        }

        return response()->json(['results' => $results]);
    }

    private function uniqueFilename(string $originalName): string
    {
        $year      = date('Y');
        $month     = date('m');
        $info      = pathinfo($originalName);
        $name      = $info['filename'];
        $ext       = isset($info['extension']) ? '.' . $info['extension'] : '';
        $filename  = $originalName;
        $counter   = 2;

        while (Storage::disk()->exists("{$year}/{$month}/{$filename}")) {
            $filename = "{$name}-{$counter}{$ext}";
            $counter++;
        }

        return $filename;
    }

    private function phpUploadLimit(): int
    {
        $raw = ini_get('upload_max_filesize');
        $val = (int) $raw;

        if (stripos($raw, 'g') !== false) {
            return $val * 1024;
        }

        if (stripos($raw, 'm') !== false) {
            return $val;
        }

        return (int) ($val / 1024 / 1024);
    }
}
