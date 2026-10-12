<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\MfaSetupRequest;
use App\Services\AuditLogger;
use App\Services\QrCodeService;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MfaController extends Controller
{
    public function __construct(
        private readonly TotpService $totp,
        private readonly QrCodeService $qr,
        private readonly AuditLogger $audit,
    ) {
    }

    public function showSetup(Request $request)
    {
        $user = $request->user();

        if ($user->mfa_enabled) {
            return redirect()->route('home');
        }

        $secret = session('hms.mfa_pending_secret', fn () => $this->totp->generateSecret());
        session(['hms.mfa_pending_secret' => $secret]);

        $uri = $this->totp->provisioningUri($user->username, $secret);

        return view('auth.mfa-setup', [
            'secret' => $secret,
            'readableSecret' => $this->totp->readableSecret($secret),
            'qrDataUri' => $this->qr->dataUri($uri),
            'otpauthUri' => $uri,
        ]);
    }

    public function confirmSetup(MfaSetupRequest $request)
    {
        $user = $request->user();
        $secret = session('hms.mfa_pending_secret');

        if ($secret === null) {
            return redirect()->route('mfa.setup');
        }

        if (! $this->totp->verify($secret, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => __('mfa.code_invalid'),
            ]);
        }

        $user->forceFill([
            'mfa_secret' => $secret,
            'mfa_enabled' => true,
            'mfa_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('hms.mfa_pending_secret');
        $request->session()->put('hms.mfa_verified', true);

        $this->audit->log(AuditAction::MfaEnabled, actor: $user, description: 'TOTP MFA enrolled');

        return redirect()->route('home')->with('status', __('mfa.setup_complete'));
    }

    public function showChallenge()
    {
        $user = request()->user();

        if (! $user?->mfa_enabled) {
            return redirect()->route('home');
        }

        if (session('hms.mfa_verified')) {
            return redirect()->route('home');
        }

        return view('auth.mfa-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (! $user->mfa_enabled || ! $this->totp->verify((string) $user->mfa_secret, (string) $request->input('code'))) {
            $this->audit->log(AuditAction::LoginFailed, actor: $user, description: 'MFA challenge failed');

            throw ValidationException::withMessages([
                'code' => __('mfa.code_invalid'),
            ]);
        }

        $request->session()->put('hms.mfa_verified', true);
        $this->audit->log(AuditAction::Login, actor: $user, description: 'MFA verified');

        return redirect()->intended(route('home'));
    }

    public function disable(Request $request)
    {
        $user = $request->user();

        if (! $user->requiresMfa()) {
            $user->forceFill(['mfa_enabled' => false, 'mfa_secret' => null, 'mfa_confirmed_at' => null])->save();
            $this->audit->log(AuditAction::MfaDisabled, actor: $user, description: 'TOTP MFA disabled');
        }

        return redirect()->route('home');
    }
}