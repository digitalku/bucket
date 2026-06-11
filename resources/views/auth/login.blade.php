@extends('layouts.app')

@section('title', 'Login — Digitalku Bucket')

@section('content')
<div class="container" style="max-width:420px">
    <h2 style="text-align:center;margin-bottom:4px">Digitalku Bucket</h2>
    <p style="text-align:center;color:#6b7280;font-size:14px;margin-bottom:24px">Sign in to get started</p>

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}" autofocus autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Sign In</button>
    </form>
</div>
@endsection
