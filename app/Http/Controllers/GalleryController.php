<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $files = File::where('user_id', session('auth_user_id'))
            ->latest()
            ->paginate(24);

        return view('gallery', compact('files'));
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== session('auth_user_id')) {
            abort(403);
        }

        Storage::disk()->delete($file->path);
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
