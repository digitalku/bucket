<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = File::with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        $files = $query->paginate(30)->withQueryString();
        $users = User::orderBy('username')->get();

        return view('admin.files.index', compact('files', 'users'));
    }

    public function destroy(File $file)
    {
        Storage::disk()->delete($file->path);
        $file->delete();

        return back()->with('success', 'File deleted successfully.');
    }

    public function changeOwner(Request $request, File $file)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $file->update(['user_id' => $request->user_id]);

        return back()->with('success', 'File owner updated successfully.');
    }
}
