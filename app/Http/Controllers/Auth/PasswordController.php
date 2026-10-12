<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function showChangeForm()
    {
        return view('auth.change-password');
    }

    public function change(ChangePasswordRequest $request)
    {
        $user = $request->user();

        $user->forceFill([
            'password' => $request->input('password'),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ])->save();

        $this->audit->log(AuditAction::Update, actor: $user, description: 'Password changed');

        return redirect()->route('home')->with('status', __('auth.password_changed'));
    }
}