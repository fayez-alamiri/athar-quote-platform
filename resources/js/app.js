/**
 * QuoteIt — Shared Application Shell & Editorial UI Interactions
 * Handles:
 *  - Persistent Light/Dark theme switching (with Sun/Moon icon state & aria-label updates)
 *  - Mobile navigation drawer & Account dropdown menu
 *  - Accessible Modal dialogs & Guest Sign-In prompt modal
 *  - Clipboard copy action with localized toast feedback
 */

const STORAGE_THEME_KEY = 'quoteit_theme';

function getCurrentTheme() {
    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
}

function applyTheme(theme, persist = true) {
    const root = document.documentElement;
    const isDark = theme === 'dark';

    root.classList.toggle('dark', isDark);
    root.setAttribute('data-theme', isDark ? 'dark' : 'light');

    if (persist) {
        try {
            localStorage.setItem(STORAGE_THEME_KEY, isDark ? 'dark' : 'light');
        } catch (e) {
            // Ignore localStorage restrictions in private browsing
        }
    }

    // Update all theme toggle buttons (desktop & mobile)
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        const lightLabel = btn.getAttribute('data-label-light') || 'Light mode active — switch to dark mode';
        const darkLabel = btn.getAttribute('data-label-dark') || 'Dark mode active — switch to light mode';
        const label = isDark ? darkLabel : lightLabel;

        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
        btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');

        const sunIcon = btn.querySelector('[data-theme-icon="sun"]');
        const moonIcon = btn.querySelector('[data-theme-icon="moon"]');

        // Specification Rule (Section 7):
        // Show a SUN icon when light mode is active and a MOON icon when dark mode is active.
        if (sunIcon && moonIcon) {
            sunIcon.classList.toggle('hidden', isDark);
            moonIcon.classList.toggle('hidden', !isDark);
        }
    });
}

function toggleTheme() {
    const nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
    applyTheme(nextTheme, true);
}

function showToast(message, type = 'success') {
    const container = document.getElementById('quoteit-toast-container');
    if (!container || !message) return;

    const toast = document.createElement('div');
    const isError = type === 'error';

    toast.className = [
        'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg text-sm font-medium transition-all duration-200',
        isError
            ? 'bg-red-950/90 text-red-100 border-red-700'
            : 'bg-[#2B1D17] text-[#F8F4EC] dark:bg-[#F2E8DA] dark:text-[#211A16] border-[#B99A68]',
    ].join(' ');
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');

    const dot = document.createElement('span');
    dot.className = `w-2 h-2 rounded-full shrink-0 ${isError ? 'bg-red-400' : 'bg-[#B99A68] dark:bg-[#4A3328]'}`;

    const textSpan = document.createElement('span');
    textSpan.textContent = message;

    toast.appendChild(dot);
    toast.appendChild(textSpan);
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        setTimeout(() => toast.remove(), 220);
    }, 3200);
}

async function copyQuoteText(text, successMsg, errorMsg) {
    if (!text) return false;

    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            showToast(successMsg, 'success');
            return true;
        }
    } catch (err) {
        // Fall through to textarea fallback
    }

    try {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        textarea.style.pointerEvents = 'none';
        document.body.appendChild(textarea);
        textarea.select();
        const copied = document.execCommand('copy');
        document.body.removeChild(textarea);

        if (copied) {
            showToast(successMsg, 'success');
            return true;
        }
    } catch (fallbackErr) {
        // Fall through to error toast
    }

    showToast(errorMsg, 'error');
    return false;
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');

    const focusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if (focusable) {
        setTimeout(() => focusable.focus(), 30);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
}

function promptGuestAuth() {
    openModal('guest-auth-modal');
}

// Expose global helper namespace for inline/progressive handlers
window.QuoteItUI = {
    toggleTheme,
    applyTheme,
    showToast,
    copyQuoteText,
    openModal,
    closeModal,
    promptGuestAuth,
};

document.addEventListener('DOMContentLoaded', () => {
    // Sync theme icons on initial load
    applyTheme(getCurrentTheme(), false);

    // Bind theme toggle buttons
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => toggleTheme());
    });

    // Bind mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle');
    const mobileMenuPanel = document.getElementById('mobile-menu-panel');
    if (mobileMenuBtn && mobileMenuPanel) {
        mobileMenuBtn.addEventListener('click', () => {
            const expanded = mobileMenuBtn.getAttribute('aria-expanded') === 'true';
            mobileMenuBtn.setAttribute('aria-expanded', (!expanded).toString());
            mobileMenuPanel.classList.toggle('hidden', expanded);
        });
    }

    // Bind account dropdown menu
    const accountMenuBtn = document.getElementById('account-menu-button');
    const accountMenuDropdown = document.getElementById('account-menu-dropdown');
    if (accountMenuBtn && accountMenuDropdown) {
        accountMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = accountMenuDropdown.classList.contains('hidden');
            accountMenuDropdown.classList.toggle('hidden', !isHidden);
            accountMenuBtn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });

        document.addEventListener('click', (e) => {
            if (!accountMenuDropdown.contains(e.target) && !accountMenuBtn.contains(e.target)) {
                accountMenuDropdown.classList.add('hidden');
                accountMenuBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Bind declarative modal triggers
    document.addEventListener('click', (e) => {
        const openTrigger = e.target.closest('[data-modal-open]');
        if (openTrigger) {
            e.preventDefault();
            openModal(openTrigger.getAttribute('data-modal-open'));
            return;
        }

        const closeTrigger = e.target.closest('[data-modal-close]');
        if (closeTrigger) {
            e.preventDefault();
            closeModal(closeTrigger.getAttribute('data-modal-close'));
            return;
        }

        const guestPromptTrigger = e.target.closest('[data-require-auth]');
        if (guestPromptTrigger) {
            e.preventDefault();
            promptGuestAuth();
            return;
        }

        const copyTrigger = e.target.closest('[data-copy-quote]');
        if (copyTrigger) {
            e.preventDefault();
            const text = copyTrigger.getAttribute('data-copy-quote') || '';
            const author = copyTrigger.getAttribute('data-copy-author') || '';
            const formatted = author ? `"${text}" — ${author}` : text;
            const successMsg = copyTrigger.getAttribute('data-copy-success') || 'Copied';
            const errorMsg = copyTrigger.getAttribute('data-copy-error') || 'Failed to copy';
            copyQuoteText(formatted, successMsg, errorMsg);
        }
    });

    // Close modals and dropdowns on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal-backdrop]:not(.hidden)').forEach((modal) => {
                closeModal(modal.id);
            });
            if (accountMenuDropdown && !accountMenuDropdown.classList.contains('hidden')) {
                accountMenuDropdown.classList.add('hidden');
                accountMenuBtn?.setAttribute('aria-expanded', 'false');
            }
        }
    });
});