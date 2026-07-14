const THEME_STORAGE_KEY = 'color-theme';
const themeToggleSelector = '[data-theme-toggle]';
const darkIconSelector = '[data-theme-dark-icon]';
const lightIconSelector = '[data-theme-light-icon]';

function getStoredTheme() {
    try {
        const theme = localStorage.getItem(THEME_STORAGE_KEY);
        return theme === 'dark' || theme === 'light' ? theme : null;
    } catch {
        return null;
    }
}

function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = theme;

    document.querySelectorAll(darkIconSelector).forEach((icon) => {
        icon.classList.toggle('hidden', !isDark);
    });

    document.querySelectorAll(lightIconSelector).forEach((icon) => {
        icon.classList.toggle('hidden', isDark);
    });
}

function syncTheme() {
    const storedTheme = getStoredTheme();
    const fallbackTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const theme = storedTheme ?? fallbackTheme;

    if (!storedTheme) {
        try {
            localStorage.setItem(THEME_STORAGE_KEY, theme);
        } catch {
            // Ignore storage failures in private mode or restricted contexts.
        }
    }

    applyTheme(theme);
}

function toggleTheme() {
    const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    try {
        localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
    } catch {
        // Ignore storage failures in private mode or restricted contexts.
    }

    applyTheme(nextTheme);
}

function bootTheme() {
    syncTheme();

    document.addEventListener('click', (event) => {
        if (event.target.closest(themeToggleSelector)) {
            toggleTheme();
        }
    });

    document.addEventListener('livewire:navigated', () => {
        window.requestAnimationFrame(syncTheme);
    });

    window.addEventListener('pageshow', () => {
        syncTheme();
    });

    window.addEventListener('storage', (event) => {
        if (event.key === THEME_STORAGE_KEY) {
            syncTheme();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootTheme, { once: true });
} else {
    bootTheme();
}
