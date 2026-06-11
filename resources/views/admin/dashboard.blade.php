@extends('layouts.app')

@section('title', 'Admin — Digitalku Bucket')

@section('content')
<div class="container-wide">
    <h2>Admin Dashboard</h2>

    @if($warning)
        <div class="alert alert-warning">⚠️ {{ $warning }}</div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:28px">
        <div style="background:#eff6ff;border-radius:10px;padding:20px">
            <div style="font-size:28px;font-weight:700;color:#1d4ed8">{{ $totalUsers }}</div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px">Total Users</div>
        </div>
        <div style="background:#f0fdf4;border-radius:10px;padding:20px">
            <div style="font-size:28px;font-weight:700;color:#15803d">{{ $totalFiles }}</div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px">Total Files</div>
        </div>
        <div style="background:#fef3c7;border-radius:10px;padding:20px">
            <div style="font-size:28px;font-weight:700;color:#b45309">{{ number_format($totalSize / 1024 / 1024, 1) }} MB</div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px">Total Storage Used</div>
        </div>
    </div>

    <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage Users</a>
        <a href="{{ route('admin.files.index') }}" class="btn btn-secondary">Manage Files</a>
    </div>
</div>
@endsection
