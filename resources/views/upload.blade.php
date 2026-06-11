@extends('layouts.app')

@section('title', 'Upload — Digitalku Bucket')

@push('styles')
<style>
    #drop-area {
        border: 2px dashed #c7c7c7;
        border-radius: 12px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        color: #6b7280;
        transition: all .2s ease;
        margin-bottom: 20px;
    }

    #drop-area.dragover {
        border-color: #2563eb;
        background: #eff6ff;
    }

    #drop-area .icon { font-size: 40px; margin-bottom: 8px; }
    #drop-area p { margin: 6px 0 0; font-size: 14px; }
    #drop-area small { color: #9ca3af; }

    .progress-wrapper { margin-bottom: 16px; }

    .progress-bar-track {
        width: 100%;
        height: 8px;
        background: #e5e7eb;
        border-radius: 6px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
        transition: width .2s ease;
    }

    .progress-text { font-size: 12px; color: #6b7280; margin-top: 4px; text-align: right; }

    .result-item {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .result-item.error { border-color: #fca5a5; background: #fff5f5; }

    .result-item .filename {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 10px;
        color: #111827;
        word-break: break-all;
    }

    .result-item .filename.error-text { color: #dc2626; }

    .copy-row {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }

    .copy-row label {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        width: 80px;
        flex-shrink: 0;
    }

    .copy-input {
        flex: 1;
        padding: 7px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 12px;
        background: #fff;
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: text;
        min-width: 0;
    }

    .copy-btn {
        padding: 7px 12px;
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        cursor: pointer;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .copy-btn:hover { background: #1d4ed8; }
    .copy-btn.copied { background: #16a34a; }
</style>
@endpush

@section('content')
<div class="container">
    <h2>Upload Files</h2>
    <p style="color:#6b7280;font-size:14px;margin-top:-8px">
        Images & videos · Max {{ $maxMb }} MB per file
    </p>

    @if($warning)
        <div class="alert alert-warning">⚠️ {{ $warning }}</div>
    @endif

    <div id="drop-area">
        <div class="icon">☁️</div>
        <p><strong>Drag & Drop</strong> files here<br>or click to browse</p>
        <small>Images & videos only · Multiple files supported · Ctrl+V to paste</small>
        <input type="file" id="fileInput" multiple accept="image/*,video/*" hidden>
    </div>

    <div id="progress-wrapper" class="progress-wrapper" style="display:none">
        <div class="progress-bar-track">
            <div class="progress-bar-fill" id="progressFill"></div>
        </div>
        <div class="progress-text" id="progressText"></div>
    </div>

    <div id="results"></div>
</div>
@endsection

@push('scripts')
<script>
const dropArea  = document.getElementById('drop-area');
const fileInput = document.getElementById('fileInput');
const progWrap  = document.getElementById('progress-wrapper');
const progFill  = document.getElementById('progressFill');
const progText  = document.getElementById('progressText');
const results   = document.getElementById('results');
const maxMb     = {{ $maxMb }};

dropArea.addEventListener('click', () => fileInput.click());

dropArea.addEventListener('dragover', e => {
    e.preventDefault();
    dropArea.classList.add('dragover');
});

dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));

dropArea.addEventListener('drop', e => {
    e.preventDefault();
    dropArea.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', e => handleFiles(e.target.files));

document.addEventListener('paste', e => {
    const items = Array.from(e.clipboardData?.items ?? []);
    const imageItem = items.find(i => i.kind === 'file' && i.type.startsWith('image/'));
    if (!imageItem) return;

    const blob = imageItem.getAsFile();
    const file = new File([blob], blob.name, { type: imageItem.type });

    handleFiles([file]);
});

function handleFiles(files) {
    if (!files || files.length === 0) return;

    const oversized = Array.from(files).filter(f => f.size > maxMb * 1024 * 1024);
    if (oversized.length > 0) {
        alert(`The following files exceed the ${maxMb}MB limit:\n${oversized.map(f => f.name).join('\n')}`);
        return;
    }

    upload(files);
}

function upload(files) {
    const formData = new FormData();
    Array.from(files).forEach(f => formData.append('files[]', f));
    formData.append('_token', '{{ csrf_token() }}');

    results.innerHTML = '';
    progWrap.style.display = 'block';
    progFill.style.width = '0%';
    progText.textContent = 'Uploading...';

    const startTime = Date.now();
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '{{ route('upload.store') }}', true);

    xhr.upload.onprogress = e => {
        if (e.lengthComputable) {
            const pct     = Math.round(e.loaded / e.total * 100);
            const elapsed = (Date.now() - startTime) / 1000;
            const speed   = (e.loaded / 1024 / 1024 / elapsed).toFixed(1);
            progFill.style.width = pct + '%';
            progText.textContent = `${pct}% · ${speed} MB/s`;
        }
    };

    xhr.onload = () => {
        progWrap.style.display = 'none';
        fileInput.value = '';

        let res;
        try { res = JSON.parse(xhr.responseText); }
        catch (e) {
            results.innerHTML = `<div class="alert alert-error">Server error: ${xhr.responseText}</div>`;
            return;
        }

        res.results.forEach(r => renderResult(r));
    };

    xhr.onerror = () => {
        progWrap.style.display = 'none';
        results.innerHTML = '<div class="alert alert-error">Failed to connect to server.</div>';
    };

    xhr.send(formData);
}

function renderResult(r) {
    if (!r.success) {
        results.innerHTML += `
            <div class="result-item error">
                <div class="filename error-text">✗ ${esc(r.name)}</div>
                <div style="font-size:13px;color:#dc2626">${esc(r.message)}</div>
            </div>`;
        return;
    }

    results.innerHTML += `
        <div class="result-item">
            <div class="filename">✓ ${esc(r.name)}</div>
            <div class="copy-row">
                <label>URL</label>
                <input class="copy-input" type="text" readonly value="${esc(r.url)}">
                <button class="copy-btn" onclick="copyText(this, '${esc(r.url)}')">Copy</button>
            </div>
            <div class="copy-row">
                <label>Markdown</label>
                <input class="copy-input" type="text" readonly value="${esc(r.markdown)}">
                <button class="copy-btn" onclick="copyText(this, '${esc(r.markdown)}')">Copy</button>
            </div>
        </div>`;
}

function copyText(btn, text) {
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'Copied!';
        btn.classList.add('copied');
        setTimeout(() => {
            btn.textContent = 'Copy';
            btn.classList.remove('copied');
        }, 1500);
    });
}

function esc(str) {
    return String(str)
        .replace(/&/g,'&amp;')
        .replace(/"/g,'&quot;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;');
}
</script>
@endpush
