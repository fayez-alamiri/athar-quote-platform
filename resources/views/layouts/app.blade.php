<!DOCTYPE html>
<html
    lang="{{ $currentLocale ?? app()->getLocale() }}"
    dir="{{ $textDirection ?? (app()->getLocale() === 'ar' ? 'rtl' : 'ltr') }}"
    class="antialiased"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        @hasSection('title')
            @yield('title') — {{ $brandName ?? config('brand.technical_name', 'QuoteIt') }}
        @else
            {{ $brandName ?? config('brand.technical_name', 'QuoteIt') }} — {{ $brandSubtitle ?? '' }}
        @endif
    </title>

    {{-- Early no-flicker theme initialization (respects saved choice, falls back to OS preference on first visit) --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('quoteit_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = saved ? saved : (prefersDark ? 'dark' : 'light');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
            } catch (e) {}
        })();
    </script>

    {{-- Literary Typography: Tajawal (Arabic), Lora (English literary serif), Inter (English UI) with safe fallbacks --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lora:ital,wght@0,500;0,600;0,700;1,400&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col bg-editorial-main text-editorial-primary selection:bg-[#B99A68]/30">

    {{-- Shared Navigation Header --}}
    @include('partials.navbar')

    {{-- Flash Status / Notification Banners --}}
    @if (session('status') || session('success') || session('error'))
        <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
            @if (session('status') || session('success'))
                <div role="alert" class="rounded-xl border border-[#B99A68] bg-editorial-card px-4 py-3 text-sm font-medium text-editorial-primary shadow-sm flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#B99A68] shrink-0"></span>
                        <span>{{ session('status') ?? session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div role="alert" class="rounded-xl border border-red-400/70 bg-red-50 dark:bg-red-950/40 px-4 py-3 text-sm font-medium text-red-800 dark:text-red-200 shadow-sm flex items-center justify-between gap-3 mt-2">
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Main Page Content --}}
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    {{-- Shared Editorial Footer --}}
    @include('partials.footer')

    {{-- Guest Sign-In / Register Prompt Modal (Triggered when guests click Like, Save, Saved Quotes, or Publish) --}}
    <x-modal id="guest-auth-modal" :title="__('Sign in required')">
        <div class="space-y-4">
            <p class="text-sm leading-relaxed text-editorial-secondary">
                {{ __('Please sign in or create an account to like quotes, save them to your collection, or publish your own quotes.') }}
            </p>
            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    data-modal-close="guest-auth-modal"
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-secondary cursor-pointer"
                >
                    {{ __('Continue Browsing') }}
                </button>
                <a
                    href="{{ Route::has('login') ? route('login') : '#auth-stage-2' }}"
                    @if(!Route::has('login')) data-modal-close="guest-auth-modal" @endif
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-secondary"
                >
                    {{ __('Sign In') }}
                </a>
                <a
                    href="{{ Route::has('register') ? route('register') : '#auth-stage-2' }}"
                    @if(!Route::has('register')) data-modal-close="guest-auth-modal" @endif
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-primary"
                >
                    {{ __('Register') }}
                </a>
            </div>
        </div>
    </x-modal>

    {{-- Floating Toast Container --}}
    <div
        id="quoteit-toast-container"
        class="fixed bottom-5 end-5 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4 sm:px-0"
        aria-live="polite"
    ></div>

    @stack('modals')
    @stack('scripts')
</body>
</html>