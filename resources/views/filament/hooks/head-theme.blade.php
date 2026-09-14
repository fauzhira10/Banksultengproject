<script>
    (function () {
        // Pastikan default tema pertama kali terbuka adalah Terang (Light Mode)
        if (!localStorage.getItem('theme_preference_v1')) {
            localStorage.setItem('theme', 'light');
            localStorage.setItem('theme_preference_v1', 'true');
        }

        const theme = localStorage.getItem('theme');
        if (theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (theme === 'system') {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    })();
</script>

<script>
    /**
     * Transisi halus saat berganti tema Terang/Gelap.
     *
     * Semua tombol tema (topbar & menu user Filament) memicu event `theme-changed`.
     * Event dicegat lebih dulu (capture), lalu perubahan kelas `dark` dijalankan di dalam
     * View Transition API (crossfade GPU, tetap mulus walau tabel berisi banyak baris).
     * Browser tanpa View Transition memakai fallback transisi warna CSS.
     */
    (function () {
        const root = document.documentElement;
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        const resolveTheme = (mode) => (mode === 'dark' || (mode === 'system' && prefersDark.matches)) ? 'dark' : 'light';

        window.addEventListener('theme-changed', (event) => {
            if (event.bsIsReplayed) {
                return;
            }

            const mode = event.detail;
            const isDark = resolveTheme(mode) === 'dark';

            if (root.classList.contains('dark') === isDark) {
                return;
            }

            event.stopImmediatePropagation();

            const applyTheme = () => {
                root.classList.toggle('dark', isDark);

                const replayedEvent = new CustomEvent('theme-changed', { detail: mode });
                replayedEvent.bsIsReplayed = true;
                window.dispatchEvent(replayedEvent);

                // Beri kesempatan Alpine menyelesaikan update reaktif sebelum snapshot baru diambil.
                return new Promise((resolve) => setTimeout(resolve, 0));
            };

            if (prefersReducedMotion.matches) {
                applyTheme();

                return;
            }

            if (typeof document.startViewTransition === 'function') {
                root.classList.add('bs-theme-view-transition');

                document.startViewTransition(applyTheme).finished.finally(() => {
                    root.classList.remove('bs-theme-view-transition');
                });

                return;
            }

            root.classList.add('bs-theme-fading');
            applyTheme();
            setTimeout(() => root.classList.remove('bs-theme-fading'), 450);
        }, true);
    })();
</script>

<style>
    /* Transisi tema: crossfade halus (View Transition API) */
    html.bs-theme-view-transition::view-transition-old(root),
    html.bs-theme-view-transition::view-transition-new(root) {
        animation-duration: 450ms;
        animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Fallback untuk browser tanpa View Transition API */
    html.bs-theme-fading *,
    html.bs-theme-fading *::before,
    html.bs-theme-fading *::after {
        transition-property: background-color, border-color, color, fill, stroke, box-shadow, outline-color !important;
        transition-duration: 400ms !important;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
        transition-delay: 0s !important;
    }

    /* Light Mode: Sidebar lebih gelap sedikit dari konten */
    html:not(.dark) .fi-sidebar,
    html:not(.dark) #fi-main-sidebar {
        background-color: #eaedf2 !important;
        border-right: 1px solid rgba(203, 213, 225, 0.9) !important;
        box-shadow: 2px 0 8px -2px rgba(15, 23, 42, 0.04) !important;
    }
    html:not(.dark) .fi-sidebar-header,
    html:not(.dark) .fi-sidebar-header-ctn,
    html:not(.dark) .fi-sidebar-nav,
    html:not(.dark) .fi-sidebar-footer {
        background-color: transparent !important;
    }
    html:not(.dark) .fi-sidebar-header {
        border-bottom: 1px solid rgba(203, 213, 225, 0.75) !important;
    }
    html:not(.dark) .fi-sidebar-item-btn {
        color: #334155 !important;
    }
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: #ffffff !important;
        color: #1d4ed8 !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08) !important;
        border: 1px solid rgba(203, 213, 225, 0.8) !important;
    }
    html:not(.dark) .fi-main-ctn,
    html:not(.dark) body.fi-body {
        background-color: #f8fafc !important;
    }

    /* Dark Mode: Sidebar lebih gelap pekat dari konten */
    html.dark .fi-sidebar,
    html.dark #fi-main-sidebar {
        background-color: #080d18 !important;
        border-right: 1px solid rgba(30, 41, 59, 0.9) !important;
        box-shadow: 2px 0 12px -2px rgba(0, 0, 0, 0.5) !important;
    }
    html.dark .fi-sidebar-header,
    html.dark .fi-sidebar-header-ctn,
    html.dark .fi-sidebar-nav,
    html.dark .fi-sidebar-footer {
        background-color: transparent !important;
    }
    html.dark .fi-sidebar-header {
        border-bottom: 1px solid rgba(30, 41, 59, 0.8) !important;
    }
    html.dark .fi-sidebar-item-btn {
        color: #cbd5e1 !important;
    }
    html.dark .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: rgba(37, 99, 235, 0.22) !important;
        color: #93c5fd !important;
        border: 1px solid rgba(59, 130, 246, 0.35) !important;
    }
    html.dark .fi-main-ctn,
    html.dark body.fi-body {
        background-color: #0d1322 !important;
    }

    /* Header kolom aksi agar posisinya di tengah (center) */
    th.fi-ta-header-cell.fi-align-end {
        text-align: center !important;
    }
    th.fi-ta-header-cell.fi-align-end .fi-ta-header-cell-sort-btn {
        justify-content: center !important;
    }
</style>
