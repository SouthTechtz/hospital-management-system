<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Per-user language switch (SW / EN), stored as a user preference (Part A).
 */
class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (! in_array($locale, config('hms.locale.supported', ['en', 'sw']), true)) {
            abort(404);
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        $user = $request->user();
        if ($user) {
            $user->forceFill(['preferred_locale' => $locale])->save();
            $user->forgetPermissionCache();
        }

        return redirect()->back()->with('status', __('ui.language_changed'));
    }
}