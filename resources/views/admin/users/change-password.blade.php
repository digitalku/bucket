@extends('layouts.app')

@section('title', 'Change Password — {{ $user->username }}')

@section('content')
<div class="container" style="max-width:440px">
    <div style="margin-bottom:16px">
        <a href="{{ route('admin.users.index') }}" style="color:#6b7280;font-size:14px">← Back to Users</a>
    </div>

    <h2>Change Password — {{ $user->username }}</h2>
    <p style="color:#6b7280;font-size:14px;margin-top:-8px">
        Admin does not need to enter the current password.
    </p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.password.update', $user) }}">
        @csrf
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" autocomplete="new-password" autofocus>
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Save Password</button>
    </form>
</div>
@endsection
