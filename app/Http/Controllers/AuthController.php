<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('auth_user_id')) {
            return redirect()->route('upload');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! password_verify($request->password, $user->password)) {
            return back()->withErrors(['username' => 'Invalid username or password.'])->withInput();
        }

        if ($user->totp_enabled) {
            session([
                'totp_pending_user_id' => $user->id,
            ]);

            return redirect()->route('login.totp');
        }

        $this->completeLogin($user);

        return redirect()->route('upload');
    }

    public function showTotp()
    {
        if (! session('totp_pending_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.totp');
    }

    public function verifyTotp(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('totp_pending_user_id');
        $user   = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('login');
        }

        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($user->totp_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        session()->forget('totp_pending_user_id');
        $this->completeLogin($user);

        return redirect()->route('upload');
    }

    public function logout(Request $request)
    {
        $request->session()->flush();

        return redirect()->route('login');
    }

    private function completeLogin(User $user): void
    {
        session([
            'auth_user_id'   => $user->id,
            'auth_username'  => $user->username,
            'auth_role'      => $user->role,
        ]);
    }
}
