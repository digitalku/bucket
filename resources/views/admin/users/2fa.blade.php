@extends('layouts.app')

@section('title', '2FA Setup — {{ $user->username }}')

@section('content')
<div class="container" style="max-width:520px">
    <div style="margin-bottom:16px">
        <a href="{{ route('admin.users.index') }}" style="color:#6b7280;font-size:14px">← Back to Users</a>
    </div>

    <h2>2FA Settings — {{ $user->username }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->has('code'))
        <div class="alert alert-error">{{ $errors->first('code') }}</div>
    @endif

    @if($errors->has('error'))
        <div class="alert alert-error">{{ $errors->first('error') }}</div>
    @endif

    {{-- Status --}}
    <div style="margin-bottom:20px;padding:14px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb">
        <div style="font-size:13px;color:#6b7280;margin-bottom:6px">2FA Status</div>
        @if($user->totp_enabled)
            <span style="color:#16a34a;font-weight:700;font-size:15px">✓ Active</span>
        @elseif($user->totp_secret)
            <span style="color:#d97706;font-weight:700;font-size:15px">⚠ Secret set, pending verification</span>
        @else
            <span style="color:#9ca3af;font-weight:700;font-size:15px">✗ Not configured</span>
        @endif
    </div>

    {{-- QR code after generate --}}
    @if(session('new_secret'))
        @php
            $secret    = session('new_secret');
            $google2fa = new \PragmaRX\Google2FA\Google2FA();
            $otpUrl    = $google2fa->getQRCodeUrl(config('app.name'), $user->username, $secret);
            $renderer  = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $qrSvg = (new \BaconQrCode\Writer($renderer))->writeString($otpUrl);
        @endphp
        <div style="margin-bottom:20px;padding:16px;background:#f0fdf4;border:1px solid #86efac;border-radius:10px">
            <p style="font-size:13px;color:#166534;margin:0 0 12px">
                <strong>Step 1:</strong> Ask <strong>{{ $user->username }}</strong> to scan this QR code with Google Authenticator or any TOTP app.
            </p>
            <div style="text-align:center;background:#fff;padding:12px;border-radius:8px;display:inline-block">
                {!! $qrSvg !!}
            </div>
            <div style="margin-top:12px">
                <div style="font-size:12px;color:#6b7280;margin-bottom:4px">Manual entry secret:</div>
                <code style="background:#e5e7eb;padding:6px 10px;border-radius:6px;font-size:13px;word-break:break-all">{{ $secret }}</code>
            </div>
            <p style="font-size:12px;color:#374151;margin-top:10px;margin-bottom:0">
                <strong>Step 2:</strong> After scanning, verify the code below to activate 2FA.
            </p>
        </div>
    @endif

    {{-- Verify & activate --}}
    @if($user->totp_secret && ! $user->totp_enabled)
        <div style="margin-bottom:20px;padding:16px;background:#fefce8;border:1px solid #fcd34d;border-radius:10px">
            <p style="font-size:13px;color:#92400e;margin:0 0 12px">
                <strong>Verify the code</strong> from the authenticator app before activating 2FA.
                This ensures the setup is correct.
            </p>
            <form method="POST" action="{{ route('admin.users.2fa.verify', $user) }}">
                @csrf
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
                           placeholder="000000" autofocus
                           style="padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:20px;
                                  letter-spacing:6px;text-align:center;width:160px">
                    <button type="submit" class="btn btn-primary">Verify & Activate</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Disable --}}
    @if($user->totp_enabled)
        <div style="margin-bottom:20px;padding:16px;background:#fff5f5;border:1px solid #fca5a5;border-radius:10px">
            <p style="font-size:13px;color:#991b1b;margin:0 0 12px">
                Disable 2FA for this user. They will no longer need a code to log in.
            </p>
            <form method="POST" action="{{ route('admin.users.2fa.disable', $user) }}"
                  onsubmit="return confirm('Disable 2FA for {{ $user->username }}?')">
                @csrf
                <button type="submit" class="btn btn-danger">Disable 2FA</button>
            </form>
        </div>
    @endif

    {{-- Generate / re-generate --}}
    <form method="POST" action="{{ route('admin.users.2fa.generate', $user) }}"
          onsubmit="return confirm('{{ $user->totp_secret ? 'Re-generate secret? The old QR code will no longer work.' : 'Generate a new secret?' }}')"
          style="margin-bottom:10px">
        @csrf
        <button type="submit" class="btn btn-secondary" style="width:100%">
            🔑 {{ $user->totp_secret ? 'Re-generate Secret (New QR)' : 'Generate Secret' }}
        </button>
    </form>

    {{-- Reset --}}
    @if($user->totp_secret)
        <form method="POST" action="{{ route('admin.users.2fa.reset', $user) }}"
              onsubmit="return confirm('Reset 2FA for {{ $user->username }}? The secret will be deleted and 2FA disabled.')">
            @csrf
            <button type="submit" class="btn btn-danger" style="width:100%">
                🗑 Reset 2FA (Delete Secret)
            </button>
        </form>
    @endif
</div>
@endsection
