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
                var theme = (saved === 'dark' || saved === 'light') ? saved : (prefersDark ? 'dark' : 'light');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.style.colorScheme = theme;
            } catch (e) {}
        })();
    </script>

    {{-- Critical Theme & Icon CSS Rules (Guarantees Light/Dark switching even if Vite CSS cache is stale) --}}
    <style>
        :root, html:not(.dark) {
            --bg-main: #F8F4EC;
            --bg-card: #FFFEFA;
            --bg-secondary: #EFE5D5;
            --bg-elevated: #F5EFE4;
            --text-primary: #2B1D17;
            --text-heading: #2B1D17;
            --text-secondary: #827469;
            --brand-primary: #4A3328;
            --brand-primary-hover: #3B281F;
            --brand-contrast: #FFFEFA;
            --accent-gold: #B99A68;
            --accent-gold-soft: rgba(185, 154, 104, 0.16);
            --border-subtle: #E4D9C8;
            --border-strong: #D2C2AC;
            --shadow-card: 0 6px 24px -6px rgba(43, 29, 23, 0.06), 0 2px 6px -1px rgba(43, 29, 23, 0.04);
            color-scheme: light;
        }
        html.dark {
            --bg-main: #211A16;
            --bg-card: #2C231D;
            --bg-secondary: #362B24;
            --bg-elevated: #261E19;
            --text-primary: #F2E8DA;
            --text-heading: #F2E8DA;
            --text-secondary: #C3B4A0;
            --brand-primary: #C9A66B;
            --brand-primary-hover: #D6B782;
            --brand-contrast: #211A16;
            --accent-gold: #C9A66B;
            --accent-gold-soft: rgba(201, 166, 107, 0.18);
            --border-subtle: #41342C;
            --border-strong: #56453B;
            --shadow-card: 0 8px 28px -6px rgba(0, 0, 0, 0.35), 0 2px 8px -2px rgba(0, 0, 0, 0.25);
            color-scheme: dark;
        }
        html, body {
            background-color: var(--bg-main) !important;
            color: var(--text-primary) !important;
        }
        .bg-editorial-main { background-color: var(--bg-main) !important; }
        .bg-editorial-card, .editorial-card { background-color: var(--bg-card) !important; }
        .bg-editorial-secondary { background-color: var(--bg-secondary) !important; }
        .text-editorial-primary { color: var(--text-primary) !important; }
        .text-editorial-heading { color: var(--text-heading) !important; }
        .text-editorial-secondary { color: var(--text-secondary) !important; }
        .border-editorial { border-color: var(--border-subtle) !important; }
        .btn-editorial-primary, .theme-brand-pill {
            background-color: var(--brand-primary) !important;
            color: var(--brand-contrast) !important;
        }
        .btn-editorial-secondary {
            background-color: var(--bg-secondary) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-subtle) !important;
        }
        .input-editorial {
            background-color: var(--bg-card) !important;
            color: var(--text-primary) !important;
            border: 1px solid var(--border-strong) !important;
        }
        html:not(.dark) [data-theme-icon="sun"] { display: block !important; }
        html:not(.dark) [data-theme-icon="moon"] { display: none !important; }
        html.dark [data-theme-icon="sun"] { display: none !important; }
        html.dark [data-theme-icon="moon"] { display: block !important; }
    </style>

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
                    href="{{ Route::has('login') ? route('login') : '/login' }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-secondary"
                >
                    {{ __('Sign In') }}
                </a>
                <a
                    href="{{ Route::has('register') ? route('register') : '/register' }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium btn-editorial-primary"
                >
                    {{ __('Register') }}
                </a>
            </div>
        </div>
    </x-modal>

    {{-- Shared PNG Quote Share Generator Modal (Available to Guests & Authenticated Users) --}}
    @include('partials.share-modal')

    {{-- Floating Toast Container --}}
    <div
        id="quoteit-toast-container"
        class="fixed bottom-5 end-5 z-50 flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4 sm:px-0"
        aria-live="polite"
    ></div>

    @stack('modals')
    @stack('scripts')

    {{-- Self-Contained Fallback Bootstrapper (Runs if Vite JS bundle is cached/not yet rebuilt) --}}
    <script>
        (function () {
            function bootQuoteItFallback() {
                if (window.__QUOTEIT_BOOTSTRAPPED__) return;
                window.__QUOTEIT_BOOTSTRAPPED__ = true;

                var STORAGE_THEME_KEY = 'quoteit_theme';

                function getCurrentTheme() {
                    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                }

                function applyTheme(theme, persist) {
                    var root = document.documentElement;
                    var isDark = theme === 'dark';

                    root.classList.toggle('dark', isDark);
                    root.setAttribute('data-theme', isDark ? 'dark' : 'light');
                    root.style.colorScheme = isDark ? 'dark' : 'light';

                    if (persist) {
                        try {
                            localStorage.setItem(STORAGE_THEME_KEY, isDark ? 'dark' : 'light');
                        } catch (e) {}
                    }

                    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                        var lightLabel = btn.getAttribute('data-label-light') || 'Light mode active — switch to dark mode';
                        var darkLabel = btn.getAttribute('data-label-dark') || 'Dark mode active — switch to light mode';
                        var label = isDark ? darkLabel : lightLabel;

                        btn.setAttribute('aria-label', label);
                        btn.setAttribute('title', label);
                        btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');

                        var sunIcon = btn.querySelector('[data-theme-icon="sun"]');
                        var moonIcon = btn.querySelector('[data-theme-icon="moon"]');
                        if (sunIcon && moonIcon) {
                            sunIcon.classList.toggle('hidden', isDark);
                            moonIcon.classList.toggle('hidden', !isDark);
                        }
                    });
                }

                function toggleTheme() {
                    var next = getCurrentTheme() === 'dark' ? 'light' : 'dark';
                    applyTheme(next, true);
                }

                function showToast(message, type) {
                    var container = document.getElementById('quoteit-toast-container');
                    if (!container || !message) return;
                    var isError = type === 'error';
                    var toast = document.createElement('div');
                    toast.className = 'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg text-sm font-medium transition-all duration-200 ' +
                        (isError ? 'bg-red-950/90 text-red-100 border-red-700' : 'bg-[#2B1D17] text-[#F8F4EC] border-[#B99A68]');
                    toast.setAttribute('role', 'status');
                    var dot = document.createElement('span');
                    dot.className = 'w-2 h-2 rounded-full shrink-0 ' + (isError ? 'bg-red-400' : 'bg-[#B99A68]');
                    var span = document.createElement('span');
                    span.textContent = message;
                    toast.appendChild(dot);
                    toast.appendChild(span);
                    container.appendChild(toast);
                    setTimeout(function () {
                        toast.style.opacity = '0';
                        setTimeout(function () { toast.remove(); }, 220);
                    }, 3000);
                }

                async function copyQuoteText(text, successMsg, errorMsg) {
                    if (!text) return false;
                    try {
                        if (navigator.clipboard && window.isSecureContext) {
                            await navigator.clipboard.writeText(text);
                            showToast(successMsg || 'Copied', 'success');
                            return true;
                        }
                    } catch (e) {}
                    try {
                        var ta = document.createElement('textarea');
                        ta.value = text;
                        ta.setAttribute('readonly', '');
                        ta.style.position = 'fixed';
                        ta.style.top = '0';
                        ta.style.opacity = '0';
                        document.body.appendChild(ta);
                        ta.select();
                        var ok = document.execCommand('copy');
                        document.body.removeChild(ta);
                        if (ok) {
                            showToast(successMsg || 'Copied', 'success');
                            return true;
                        }
                    } catch (e) {}
                    showToast(errorMsg || 'Unable to copy', 'error');
                    return false;
                }

                function openModal(modalId) {
                    var modal = document.getElementById(modalId);
                    if (!modal) return;
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal(modalId) {
                    var modal = document.getElementById(modalId);
                    if (!modal) return;
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                }

                function promptGuestAuth() {
                    openModal('guest-auth-modal');
                }

                function resolveSameOriginAction(actionUrl) {
                    try {
                        var parsed = new URL(actionUrl, window.location.origin);
                        return parsed.pathname + parsed.search;
                    } catch (e) {
                        return actionUrl;
                    }
                }

                function getCsrfToken(form) {
                    var input = form ? form.querySelector('input[name="_token"]') : null;
                    if (input && input.value) return input.value;
                    var meta = document.querySelector('meta[name="csrf-token"]');
                    return meta ? (meta.getAttribute('content') || '') : '';
                }

                async function handleLikeFormSubmit(form, ev) {
                    if (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                    }
                    if (!form || form.dataset.submitting === 'true') return;

                    var quoteId = form.getAttribute('data-like-form');
                    var button = form.querySelector('[data-like-button]');
                    form.dataset.submitting = 'true';
                    if (button) button.classList.add('opacity-75', 'pointer-events-none');

                    try {
                        var csrf = getCsrfToken(form);
                        var formData = new FormData(form);
                        if (csrf && !formData.has('_token')) formData.append('_token', csrf);

                        var response = await fetch(resolveSameOriginAction(form.action), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        if (response.status === 401 || response.status === 419) {
                            promptGuestAuth();
                            return;
                        }
                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        var data = await response.json();
                        var isLiked = Boolean(data.liked);

                        document.querySelectorAll('[data-like-button="' + quoteId + '"]').forEach(function (btn) {
                            btn.setAttribute('aria-pressed', isLiked ? 'true' : 'false');
                            btn.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer ' +
                                (isLiked ? 'theme-brand-pill' : 'btn-editorial-secondary');
                        });

                        document.querySelectorAll('[data-like-icon="' + quoteId + '"]').forEach(function (icon) {
                            icon.classList.toggle('fill-current', isLiked);
                            icon.classList.toggle('fill-none', !isLiked);
                        });

                        document.querySelectorAll('[data-like-count="' + quoteId + '"]').forEach(function (cnt) {
                            cnt.textContent = String(data.likes_count ?? 0);
                        });

                        if (typeof data.received_likes_count === 'number') {
                            document.querySelectorAll('[data-navbar-likes-received], [data-stat-received-likes]').forEach(function (el) {
                                el.textContent = String(data.received_likes_count);
                            });
                        }

                        if (data.message) showToast(data.message, 'success');
                    } catch (err) {
                        showToast(document.documentElement.lang === 'ar' ? 'تعذر تحديث الإعجاب حالياً.' : 'Unable to update like right now.', 'error');
                    } finally {
                        form.dataset.submitting = 'false';
                        if (button) button.classList.remove('opacity-75', 'pointer-events-none');
                    }
                }

                async function handleSaveFormSubmit(form, ev) {
                    if (ev) {
                        ev.preventDefault();
                        ev.stopPropagation();
                    }
                    if (!form || form.dataset.submitting === 'true') return;

                    var quoteId = form.getAttribute('data-save-form');
                    var button = form.querySelector('[data-save-button]');
                    form.dataset.submitting = 'true';
                    if (button) button.classList.add('opacity-75', 'pointer-events-none');

                    try {
                        var csrf = getCsrfToken(form);
                        var formData = new FormData(form);
                        if (csrf && !formData.has('_token')) formData.append('_token', csrf);

                        var response = await fetch(resolveSameOriginAction(form.action), {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        if (response.status === 401 || response.status === 419) {
                            promptGuestAuth();
                            return;
                        }
                        if (!response.ok) throw new Error('HTTP ' + response.status);

                        var data = await response.json();
                        var isSaved = Boolean(data.saved);

                        document.querySelectorAll('[data-save-button="' + quoteId + '"]').forEach(function (btn) {
                            btn.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
                            btn.className = 'px-3 py-1.5 rounded-xl text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer ' +
                                (isSaved ? 'bg-[#B99A68] text-[#2B1D17]' : 'btn-editorial-secondary');
                        });

                        document.querySelectorAll('[data-save-icon="' + quoteId + '"]').forEach(function (icon) {
                            icon.classList.toggle('fill-current', isSaved);
                            icon.classList.toggle('fill-none', !isSaved);
                        });

                        document.querySelectorAll('[data-save-label="' + quoteId + '"]').forEach(function (lbl) {
                            lbl.textContent = data.label || (isSaved ? 'Saved' : 'Save');
                        });

                        var savedGrid = document.querySelector('[data-saved-page-grid]');
                        if (savedGrid && !isSaved) {
                            var card = document.getElementById('quote-card-' + quoteId);
                            if (card) {
                                card.remove();
                                if (savedGrid.querySelectorAll('[data-quote-card]').length === 0) {
                                    window.location.reload();
                                }
                            }
                        }

                        if (data.message) showToast(data.message, 'success');
                    } catch (err) {
                        showToast(document.documentElement.lang === 'ar' ? 'تعذر تحديث الحفظ حالياً.' : 'Unable to update saved state right now.', 'error');
                    } finally {
                        form.dataset.submitting = 'false';
                        if (button) button.classList.remove('opacity-75', 'pointer-events-none');
                    }
                }

                // Account page avatar file input & auto-submit handling
                var avatarInput = document.getElementById('avatar');
                var avatarForm = document.getElementById('account-avatar-form');
                var previewImg = document.getElementById('account-avatar-preview-img');
                var filenameLabel = document.getElementById('account-avatar-filename');
                var pendingAutoSubmit = false;

                if (avatarInput) {
                    avatarInput.addEventListener('change', function (e) {
                        var file = e.target.files && e.target.files[0];
                        if (!file) return;
                        if (filenameLabel) {
                            filenameLabel.textContent = file.name;
                            filenameLabel.classList.remove('hidden');
                        }
                        if (previewImg) {
                            var reader = new FileReader();
                            reader.onload = function (ev) {
                                previewImg.src = ev.target.result;
                                previewImg.classList.remove('hidden');
                                var initials = previewImg.parentElement ? previewImg.parentElement.querySelector('[data-avatar-initials]') : null;
                                if (initials) initials.classList.add('hidden');
                            };
                            reader.readAsDataURL(file);
                        }
                        if (pendingAutoSubmit && avatarForm) {
                            pendingAutoSubmit = false;
                            avatarForm.submit();
                        }
                    });
                }

                if (avatarForm && avatarInput) {
                    avatarForm.addEventListener('submit', function (e) {
                        if (!avatarInput.files || avatarInput.files.length === 0) {
                            e.preventDefault();
                            pendingAutoSubmit = true;
                            avatarInput.click();
                        }
                    });
                }

                window.QuoteItUI = {
                    toggleTheme: toggleTheme,
                    applyTheme: applyTheme,
                    showToast: showToast,
                    copyQuoteText: copyQuoteText,
                    openModal: openModal,
                    closeModal: closeModal,
                    promptGuestAuth: promptGuestAuth
                };

                applyTheme(getCurrentTheme(), false);

                document.addEventListener('submit', function (e) {
                    var likeForm = e.target.closest('form[data-like-form]');
                    if (likeForm) {
                        handleLikeFormSubmit(likeForm, e);
                        return;
                    }
                    var saveForm = e.target.closest('form[data-save-form]');
                    if (saveForm) {
                        handleSaveFormSubmit(saveForm, e);
                    }
                });

                document.addEventListener('click', function (e) {
                    var themeBtn = e.target.closest('[data-theme-toggle]');
                    if (themeBtn) {
                        e.preventDefault();
                        toggleTheme();
                        return;
                    }

                    var likeBtn = e.target.closest('[data-like-button]');
                    if (likeBtn) {
                        var parentLikeForm = likeBtn.closest('form[data-like-form]');
                        if (parentLikeForm) {
                            handleLikeFormSubmit(parentLikeForm, e);
                            return;
                        }
                    }

                    var saveBtn = e.target.closest('[data-save-button]');
                    if (saveBtn) {
                        var parentSaveForm = saveBtn.closest('form[data-save-form]');
                        if (parentSaveForm) {
                            handleSaveFormSubmit(parentSaveForm, e);
                            return;
                        }
                    }

                    var guestPromptTrigger = e.target.closest('[data-require-auth]');
                    if (guestPromptTrigger) {
                        e.preventDefault();
                        e.stopPropagation();
                        promptGuestAuth();
                        return;
                    }

                    var accountMenuBtn = e.target.closest('#account-menu-button');
                    var accountMenuDropdown = document.getElementById('account-menu-dropdown');
                    if (accountMenuBtn && accountMenuDropdown) {
                        e.preventDefault();
                        e.stopPropagation();
                        var isHidden = accountMenuDropdown.classList.contains('hidden');
                        accountMenuDropdown.classList.toggle('hidden', !isHidden);
                        accountMenuBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                        return;
                    }

                    if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
                        if (!accountMenuDropdown.contains(e.target)) {
                            accountMenuDropdown.classList.add('hidden');
                            var navBtn = document.getElementById('account-menu-button');
                            if (navBtn) navBtn.setAttribute('aria-expanded', 'false');
                        }
                    }

                    var mobileMenuBtn = e.target.closest('#mobile-menu-toggle');
                    var mobileMenuPanel = document.getElementById('mobile-menu-panel');
                    if (mobileMenuBtn && mobileMenuPanel) {
                        e.preventDefault();
                        var expanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
                        mobileMenuBtn.setAttribute('aria-expanded', (!expanded).toString());
                        mobileMenuPanel.classList.toggle('hidden', expanded);
                        return;
                    }

                    var avatarTrigger = e.target.closest('[data-avatar-trigger]');
                    if (avatarTrigger) {
                        e.preventDefault();
                        var fileInput = document.getElementById('avatar');
                        if (fileInput) fileInput.click();
                        return;
                    }

                    var openTrigger = e.target.closest('[data-modal-open]');
                    if (openTrigger) {
                        e.preventDefault();
                        openModal(openTrigger.getAttribute('data-modal-open'));
                        return;
                    }

                    var closeTrigger = e.target.closest('[data-modal-close]');
                    if (closeTrigger) {
                        e.preventDefault();
                        closeModal(closeTrigger.getAttribute('data-modal-close'));
                        return;
                    }

                    var copyTrigger = e.target.closest('[data-copy-quote]');
                    if (copyTrigger) {
                        e.preventDefault();
                        var text = copyTrigger.getAttribute('data-copy-quote') || '';
                        var author = copyTrigger.getAttribute('data-copy-author') || '';
                        var formatted = author ? ('"' + text + '" — ' + author) : text;
                        var successMsg = copyTrigger.getAttribute('data-copy-success') || 'Copied';
                        var errorMsg = copyTrigger.getAttribute('data-copy-error') || 'Failed to copy';
                        copyQuoteText(formatted, successMsg, errorMsg);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        document.querySelectorAll('[data-modal-backdrop]:not(.hidden)').forEach(function (modal) {
                            closeModal(modal.id);
                        });
                        var accountMenuDropdown = document.getElementById('account-menu-dropdown');
                        if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
                            accountMenuDropdown.classList.add('hidden');
                            var navBtn = document.getElementById('account-menu-button');
                            if (navBtn) navBtn.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootQuoteItFallback);
            } else {
                bootQuoteItFallback();
            }
        })();
    </script>
</body>
</html>