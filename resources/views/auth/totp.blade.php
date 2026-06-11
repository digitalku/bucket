@extends('layouts.app')

@section('title', '2FA Verification — Digitalku Bucket')

@section('content')
<div class="container" style="max-width:420px">
    <h2 style="text-align:center;margin-bottom:4px">2FA Verification</h2>
    <p style="text-align:center;color:#6b7280;font-size:14px;margin-bottom:24px">
        Enter the 6-digit code from your authenticator app
    </p>

    @if($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.totp.post') }}">
        @csrf
        <div class="form-group">
            <label for="code">Authenticator Code</label>
            <input id="code" type="text" name="code" inputmode="numeric" pattern="\d{6}"
                   maxlength="6" autofocus autocomplete="one-time-code"
                   placeholder="000000" style="letter-spacing:6px;font-size:22px;text-align:center">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Verify</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:13px">
        <a href="{{ route('login') }}" style="color:#6b7280">← Back to login</a>
    </p>
</div>
@endsection
