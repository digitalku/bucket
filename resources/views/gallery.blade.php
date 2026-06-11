@extends('layouts.app')

@section('title', 'Gallery — Digitalku Bucket')

@push('styles')
<style>
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
        margin-top: 16px;
    }

    .gallery-card {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow .2s;
    }

    .gallery-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }

    .gallery-thumb {
        width: 100%;
        height: 140px;
        object-fit: cover;
        display: block;
        background: #f3f4f6;
    }

    .video-thumb {
        width: 100%;
        height: 140px;
        background: #1e293b;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 32px;
    }

    .gallery-card-body {
        padding: 10px;
    }

    .gallery-card-name {
        font-size: 12px;
        font-weight: 600;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 6px;
    }

    .gallery-card-size {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 8px;
    }

    .gallery-actions {
        display: flex;
        gap: 4px;
    }

    .gallery-actions a,
    .gallery-actions button {
        flex: 1;
        text-align: center;
        padding: 5px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: none;
    }

    .btn-copy-sm {
        background: #2563eb;
        color: #fff;
    }

    .btn-copy-sm:hover { background: #1d4ed8; }

    .btn-delete-sm {
        background: #fee2e2;
        color: #dc2626;
    }

    .btn-delete-sm:hover { background: #fca5a5; }

    .empty-state {
        text-align: center;
        color: #9ca3af;
        padding: 60px 20px;
    }

    .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="container-wide">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <h2 style="margin:0">My Gallery</h2>
        <a href="{{ route('upload') }}" class="btn btn-primary" style="font-size:13px">+ Upload</a>
    </div>
    <p style="color:#6b7280;font-size:14px;margin-top:4px">{{ $files->total() }} file</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($files->isEmpty())
        <div class="empty-state">
            <div class="icon">📂</div>
            <p>No files yet. <a href="{{ route('upload') }}">Upload now</a></p>
        </div>
    @else
        <div class="gallery-grid" id="gallery">
            @foreach($files as $file)
                <div class="gallery-card">
                    @if(str_starts_with($file->mime_type, 'image/'))
                        <img class="gallery-thumb" src="{{ $file->publicUrl() }}" alt="{{ $file->original_name }}" loading="lazy">
                    @else
                        <div class="video-thumb">🎬</div>
                    @endif
                    <div class="gallery-card-body">
                        <div class="gallery-card-name" title="{{ $file->original_name }}">
                            {{ $file->original_name }}
                        </div>
                        <div class="gallery-card-size">
                            {{ number_format($file->size / 1024 / 1024, 2) }} MB ·
                            {{ $file->created_at->format('d M Y') }}
                        </div>
                        <div class="gallery-actions">
                            <button class="btn-copy-sm" onclick="copyUrl('{{ $file->publicUrl() }}', this)">
                                Copy URL
                            </button>
                            <button class="btn-copy-sm" onclick="copyUrl('{{ $file->markdownLink() }}', this)" style="background:#7c3aed">
                                MD
                            </button>
                            <form method="POST" action="{{ route('files.destroy', $file) }}"
                                  onsubmit="return confirm('Delete this file?')" style="flex:1;display:flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-delete-sm" style="width:100%">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:20px;text-align:center">
            {{ $files->links() }}
        </div>
    @endif
</div>

<div id="toast" style="
    position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
    background:#111827;color:#fff;padding:10px 20px;border-radius:8px;
    font-size:13px;display:none;z-index:999;
">Copied!</div>
@endsection

@push('scripts')
<script>
function copyUrl(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const old = btn.textContent;
        btn.textContent = '✓';
        setTimeout(() => btn.textContent = old, 1500);

        const toast = document.getElementById('toast');
        toast.style.display = 'block';
        setTimeout(() => toast.style.display = 'none', 1500);
    });
}
</script>
@endpush
