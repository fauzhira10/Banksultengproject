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
     * Transisi tema yang sangat halus, elegan, dan mulus (silky smooth crossfade 300ms).
     * Menggunakan View Transition API (GPU-accelerated) dengan fallback transisi menyeluruh.
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
            };

            if (prefersReducedMotion.matches) {
                applyTheme();
                return;
            }

            // Jalur 1: View Transitions API (GPU Accelerated Seamless Crossfade)
            if (typeof document.startViewTransition === 'function') {
                try {
                    document.startViewTransition(applyTheme);
                    return;
                } catch (e) {
                    // Fallback jika startViewTransition gagal
                }
            }

            // Jalur 2: Fallback halus menyeluruh untuk browser tanpa View Transitions API
            root.classList.add('theme-transitioning');
            applyTheme();
            setTimeout(() => {
                root.classList.remove('theme-transitioning');
            }, 320);
        }, true);
    })();
</script>

<style>
    /* ==========================================================================
       1. View Transition API: Silky Smooth Seamless Crossfade (300ms)
          Tampilan lama tetap solid di bawah, tampilan baru memudar halus di atasnya.
       ========================================================================== */
    ::view-transition-group(root) {
        animation-duration: 300ms;
        animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }

    ::view-transition-old(root),
    ::view-transition-new(root) {
        mix-blend-mode: normal;
    }

    ::view-transition-old(root) {
        animation: none !important;
        z-index: 1;
    }

    ::view-transition-new(root) {
        animation: bs-theme-fade-in 300ms cubic-bezier(0.4, 0, 0.2, 1) !important;
        z-index: 2;
    }

    @keyframes bs-theme-fade-in {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* ==========================================================================
       2. Fallback Transisi Halus Menyeluruh (Browser tanpa View Transitions)
          Diterapkan serentak ke semua elemen selama proses pergantian tema (300ms)
       ========================================================================== */
    html.theme-transitioning,
    html.theme-transitioning *,
    html.theme-transitioning *::before,
    html.theme-transitioning *::after {
        transition: background-color 300ms cubic-bezier(0.4, 0, 0.2, 1),
                    border-color 300ms cubic-bezier(0.4, 0, 0.2, 1),
                    color 300ms cubic-bezier(0.4, 0, 0.2, 1),
                    fill 300ms cubic-bezier(0.4, 0, 0.2, 1),
                    stroke 300ms cubic-bezier(0.4, 0, 0.2, 1),
                    box-shadow 300ms cubic-bezier(0.4, 0, 0.2, 1) !important;
        transition-delay: 0s !important;
    }

    /* ==========================================================================
       3. Sidebar & Komponen Styling
       ========================================================================== */
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
