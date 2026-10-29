<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    /**
     * Switch the active interface language between Arabic (ar) and English (en).
     */
    public function switch(Request $request, string $locale): JsonResponse|RedirectResponse
    {
        $supported = config('brand.supported_locales', ['ar', 'en']);

        if (! in_array($locale, $supported, true)) {
            $locale = config('brand.default_locale', 'ar');
        }

        $request->session()->put('locale', $locale);
        Cookie::queue('quoteit_locale', $locale, 60 * 24 * 365);

        if (Auth::check()) {
            $user = Auth::user();
            if (($user->locale ?? null) !== $locale) {
                $user->forceFill(['locale' => $locale])->save();
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'locale' => $locale,
                'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            ]);
        }

        return redirect()->back();
    }
}