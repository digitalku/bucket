<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalFiles = File::count();
        $totalSize  = File::sum('size');
        $maxMb      = (int) config('app.max_upload_mb', 200);
        $phpLimit   = $this->phpUploadLimit();
        $warning    = $maxMb > $phpLimit ? "MAX_UPLOAD_MB ({$maxMb}MB) exceeds PHP upload_max_filesize ({$phpLimit}MB). Uploads above {$phpLimit}MB will fail." : null;

        return view('admin.dashboard', compact('totalUsers', 'totalFiles', 'totalSize', 'warning'));
    }

    private function phpUploadLimit(): int
    {
        $raw = ini_get('upload_max_filesize');
        $val = (int) $raw;

        if (stripos($raw, 'g') !== false) return $val * 1024;
        if (stripos($raw, 'm') !== false) return $val;

        return (int) ($val / 1024 / 1024);
    }
}
