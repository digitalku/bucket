<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TotpController extends Controller
{
    public function show(User $user)
    {
        return view('admin.users.2fa', compact('user'));
    }

    public function generate(User $user)
    {
        $google2fa = new Google2FA();
        $secret    = $google2fa->generateSecretKey();

        $user->update([
            'totp_secret'  => $secret,
            'totp_enabled' => false,
        ]);

        return redirect()->route('admin.users.2fa', $user)
            ->with('new_secret', $secret)
            ->with('success', 'New secret generated. Ask the user to scan the QR code, then verify before activating.');
    }

    public function verify(User $user)
    {
        if (! $user->totp_secret) {
            return back()->withErrors(['code' => 'This user has no secret yet. Generate one first.']);
        }

        $code = request()->validate([
            'code' => 'required|string|digits:6',
        ])['code'];

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($user->totp_secret, $code)) {
            return back()->withErrors(['code' => 'Invalid or expired code. Make sure the user scanned the QR code correctly.']);
        }

        $user->update(['totp_enabled' => true]);

        return back()->with('success', "2FA activated for {$user->username}. Code verified successfully.");
    }

    public function disable(User $user)
    {
        if (! $user->totp_enabled) {
            return back()->with('success', '2FA is already disabled.');
        }

        $user->update(['totp_enabled' => false]);

        return back()->with('success', "2FA disabled for {$user->username}.");
    }

    public function reset(User $user)
    {
        $user->update([
            'totp_secret'  => null,
            'totp_enabled' => false,
        ]);

        return back()->with('success', "2FA for {$user->username} has been reset. Secret deleted.");
    }
}
