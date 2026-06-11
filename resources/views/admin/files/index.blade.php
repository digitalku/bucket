@extends('layouts.app')

@section('title', 'Manage Files — Admin')

@push('styles')
<style>
    .file-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
    }

    .video-icon {
        width: 48px;
        height: 48px;
        background: #1e293b;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .filter-bar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
        align-items: flex-end;
    }

    .filter-bar input,
    .filter-bar select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13px;
    }
</style>
@endpush

@section('content')
<div class="container-wide">
    <h2>Manage All Files</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search filename...">
        <select name="user_id">
            <option value="">All Users</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                    {{ $u->username }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.files.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <p style="font-size:13px;color:#6b7280">{{ $files->total() }} files found</p>

    <table>
        <thead>
            <tr>
                <th style="width:60px"></th>
                <th>Filename</th>
                <th>Owner</th>
                <th>Size</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($files as $file)
            <tr>
                <td>
                    @if(str_starts_with($file->mime_type, 'image/'))
                        <img class="file-thumb" src="{{ $file->publicUrl() }}" alt="" loading="lazy">
                    @else
                        <div class="video-icon">🎬</div>
                    @endif
                </td>
                <td>
                    <div style="font-weight:600;font-size:13px;word-break:break-all">{{ $file->original_name }}</div>
                    <div style="font-size:11px;color:#9ca3af">{{ $file->path }}</div>
                </td>
                <td style="font-size:13px">{{ $file->user?->username ?? '—' }}</td>
                <td style="font-size:12px;color:#6b7280;white-space:nowrap">
                    {{ number_format($file->size / 1024 / 1024, 2) }} MB
                </td>
                <td style="font-size:12px;color:#9ca3af;white-space:nowrap">
                    {{ $file->created_at->format('d M Y H:i') }}
                </td>
                <td>
                    <div style="display:flex;flex-direction:column;gap:6px">
                        {{-- Change owner --}}
                        <form method="POST" action="{{ route('admin.files.owner', $file) }}" style="display:flex;gap:4px">
                            @csrf
                            @method('PATCH')
                            <select name="user_id" style="padding:4px 6px;border:1px solid #d1d5db;border-radius:6px;font-size:11px">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $file->user_id == $u->id ? 'selected' : '' }}>
                                        {{ $u->username }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-secondary" style="padding:4px 8px;font-size:11px">
                                Transfer
                            </button>
                        </form>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.files.destroy', $file) }}"
                              onsubmit="return confirm('Delete this file?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding:4px 8px;font-size:11px;width:100%">
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top:20px">
        {{ $files->links() }}
    </div>
</div>
@endsection
