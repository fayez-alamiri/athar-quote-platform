<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Set the application locale and text direction (RTL for Arabic, LTR for English).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('brand.supported_locales', ['ar', 'en']);
        $default = config('brand.default_locale', 'ar');

        $userLocale = Auth::check() ? (Auth::user()->locale ?? null) : null;
        $sessionLocale = $request->session()->get('locale');
        $cookieLocale = $request->cookie('quoteit_locale');

        $locale = $sessionLocale ?: ($userLocale ?: ($cookieLocale ?: $default));

        if (! in_array($locale, $supported, true)) {
            $locale = $default;
        }

        App::setLocale($locale);

        $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        $brandName = config("brand.name.{$locale}", config('brand.technical_name', 'QuoteIt'));
        $brandSubtitle = config("brand.wordmark_subtitle.{$locale}", '');
        $brandTagline = config("brand.tagline.{$locale}", '');

        View::share([
            'currentLocale' => $locale,
            'textDirection' => $dir,
            'brandName' => $brandName,
            'brandSubtitle' => $brandSubtitle,
            'brandTagline' => $brandTagline,
        ]);

        return $next($request);
    }
}