@extends('layouts.app')

@section('title', 'Change Password — Digitalku Bucket')

@section('content')
<div class="container" style="max-width:440px">
    <h2>Change Password</h2>
    <p style="color:#6b7280;font-size:14px;margin-top:-8px">Account: <strong>{{ session('auth_username') }}</strong></p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('profile.password.update') }}">
        @csrf
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" autocomplete="current-password">
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="password" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Save Password</button>
    </form>
</div>
@endsection
