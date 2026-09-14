{{--
    Tombol panah bulat untuk menggeser tabel tiket langsung ke kolom paling kanan (Aksi).
    - Posisi fixed: selalu di tepi kanan area tabel yang terlihat, di tengah layar pengguna.
    - Hanya muncul sebentar saat pengguna berhenti scroll, lalu hilang sendiri (kecuali sedang di-hover/fokus).
    - Langsung disembunyikan saat pengguna scroll lagi.
    - Saat tabel sudah di ujung kanan, panah berbalik (di tepi kiri) untuk kembali ke kolom awal.
--}}
<button
    type="button"
    class="bs-scroll-end-btn"
    data-bs-scroll-end
    aria-label="Geser ke kolom Aksi"
    title="Geser ke kolom Aksi"
    aria-hidden="true"
    tabindex="-1"
>
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3" />
    </svg>
</button>

<style>
    .bs-scroll-end-btn {
        --bs-nudge-distance: 3px;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 25;
        display: grid;
        place-items: center;
        width: 3rem;
        height: 3rem;
        padding: 0;
        border: 0;
        border-radius: 9999px;
        color: #ffffff;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.92),
            0 12px 28px -8px rgba(29, 78, 216, 0.6);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transform: scale(0.8);
        transition:
            opacity 220ms cubic-bezier(0.4, 0, 0.2, 1),
            transform 260ms cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 200ms ease,
            visibility 0s linear 220ms;
        -webkit-tap-highlight-color: transparent;
    }

    .bs-scroll-end-btn.is-visible {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
        transition-delay: 0s;
    }

    .bs-scroll-end-btn.is-visible:hover {
        transform: scale(1.07);
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.95),
            0 16px 32px -8px rgba(29, 78, 216, 0.7);
    }

    .bs-scroll-end-btn.is-visible:active {
        transform: scale(0.95);
    }

    .bs-scroll-end-btn:focus-visible {
        outline: 3px solid #93c5fd;
        outline-offset: 4px;
    }

    html.dark .bs-scroll-end-btn {
        box-shadow:
            0 0 0 4px rgba(15, 23, 42, 0.92),
            0 12px 28px -8px rgba(0, 0, 0, 0.75);
    }

    html.dark .bs-scroll-end-btn.is-visible:hover {
        box-shadow:
            0 0 0 4px rgba(15, 23, 42, 0.95),
            0 16px 32px -8px rgba(59, 130, 246, 0.55);
    }

    .bs-scroll-end-btn svg {
        width: 1.375rem;
        height: 1.375rem;
        transition: rotate 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .bs-scroll-end-btn.is-reversed {
        --bs-nudge-distance: -3px;
    }

    .bs-scroll-end-btn.is-reversed svg {
        rotate: 180deg;
    }

    .bs-scroll-end-btn.is-visible:not(:hover) svg {
        animation: bs-arrow-nudge 700ms ease-in-out 150ms 2;
    }

    @keyframes bs-arrow-nudge {
        0%, 100% {
            translate: 0 0;
        }

        50% {
            translate: var(--bs-nudge-distance) 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .bs-scroll-end-btn,
        .bs-scroll-end-btn svg {
            transition-duration: 0s !important;
            animation: none !important;
        }
    }
</style>

<script>
    (() => {
        const button = document.querySelector('[data-bs-scroll-end]');

        if (! button || button.dataset.bsInitialized) {
            return;
        }

        button.dataset.bsInitialized = 'true';

        const IDLE_DELAY_MS = 200;
        const AUTO_HIDE_DELAY_MS = 1500;
        const EDGE_TOLERANCE_PX = 24;
        const BUTTON_SIZE_PX = 48;
        const BUTTON_INSET_PX = 20;
        const MIN_VISIBLE_TABLE_HEIGHT_PX = 140;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        let idleTimer = null;
        let autoHideTimer = null;
        let refreshTimer = null;
        let observedContainer = null;
        let lastObservedWidth = null;

        const getScrollContainer = () => document.querySelector('.fi-ta-content-ctn');

        const resizeObserver = new ResizeObserver((entries) => {
            const width = Math.round(entries[0].contentRect.width);
            const isFirstMeasurement = lastObservedWidth === null;

            if (width === lastObservedWidth) {
                return;
            }

            lastObservedWidth = width;

            if (! isFirstMeasurement) {
                hideUntilIdle();
            }
        });

        const observeContainer = (container) => {
            if (observedContainer === container) {
                return;
            }

            if (observedContainer) {
                resizeObserver.unobserve(observedContainer);
            }

            lastObservedWidth = null;
            observedContainer = container;
            resizeObserver.observe(container);
        };

        const isVisible = () => button.classList.contains('is-visible');

        const isBeingUsed = () => button.matches(':hover') || button.matches(':focus-visible');

        const hide = () => {
            clearTimeout(autoHideTimer);
            button.classList.remove('is-visible');
            button.setAttribute('aria-hidden', 'true');
            button.setAttribute('tabindex', '-1');

            if (document.activeElement === button) {
                button.blur();
            }
        };

        const startAutoHide = () => {
            clearTimeout(autoHideTimer);
            autoHideTimer = setTimeout(() => {
                if (! isBeingUsed()) {
                    hide();
                }
            }, AUTO_HIDE_DELAY_MS);
        };

        /**
         * Hitung arah & posisi tombol. Mengembalikan false jika tombol tidak perlu ditampilkan.
         */
        const positionButton = () => {
            const container = getScrollContainer();

            if (! container) {
                return false;
            }

            observeContainer(container);

            const maxScrollLeft = container.scrollWidth - container.clientWidth;

            if (maxScrollLeft <= EDGE_TOLERANCE_PX) {
                return false;
            }

            const tableRect = container.getBoundingClientRect();
            const topbarBottom = document.querySelector('.fi-topbar-ctn')?.getBoundingClientRect().bottom ?? 0;
            const visibleTop = Math.max(tableRect.top, topbarBottom, 0);
            const visibleBottom = Math.min(tableRect.bottom, window.innerHeight);

            if ((visibleBottom - visibleTop) < MIN_VISIBLE_TABLE_HEIGHT_PX) {
                return false;
            }

            const isAtEnd = container.scrollLeft >= (maxScrollLeft - EDGE_TOLERANCE_PX);
            const label = isAtEnd ? 'Kembali ke kolom awal' : 'Geser ke kolom Aksi';

            button.classList.toggle('is-reversed', isAtEnd);
            button.setAttribute('aria-label', label);
            button.title = label;

            // Saat di ujung kanan, tombol pindah ke tepi kiri agar tidak menutupi tombol di kolom Aksi.
            const top = visibleTop + ((visibleBottom - visibleTop) / 2) - (BUTTON_SIZE_PX / 2);
            const left = isAtEnd
                ? Math.max(tableRect.left, 0) + BUTTON_INSET_PX
                : Math.min(tableRect.right, window.innerWidth) - BUTTON_SIZE_PX - BUTTON_INSET_PX;

            button.style.top = `${Math.round(top)}px`;
            button.style.left = `${Math.round(left)}px`;

            return true;
        };

        const showAfterIdle = () => {
            idleTimer = null;

            if (! positionButton()) {
                return hide();
            }

            button.classList.add('is-visible');
            button.removeAttribute('aria-hidden');
            button.setAttribute('tabindex', '0');
            startAutoHide();
        };

        const hideUntilIdle = () => {
            hide();
            clearTimeout(idleTimer);
            idleTimer = setTimeout(showAfterIdle, IDLE_DELAY_MS);
        };

        const isRelevantScroll = (target) => {
            const container = getScrollContainer();

            return target === document
                || target === document.documentElement
                || target === container
                || (container && target instanceof Element && target.contains(container));
        };

        document.addEventListener('scroll', (event) => {
            if (isRelevantScroll(event.target)) {
                hideUntilIdle();
            }
        }, { capture: true, passive: true });

        window.addEventListener('resize', hideUntilIdle, { passive: true });

        // Tabel dirender ulang oleh Livewire (ganti tab, filter, halaman): perbarui posisi tombol yang sedang tampil.
        new MutationObserver(() => {
            if (idleTimer || ! isVisible()) {
                return;
            }

            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(() => {
                if (isVisible() && ! positionButton()) {
                    hide();
                }
            }, 150);
        }).observe(document.body, { childList: true, subtree: true });

        // Selama tombol di-hover atau difokus (keyboard), jangan hilang; hitung ulang setelah ditinggalkan.
        button.addEventListener('pointerenter', () => clearTimeout(autoHideTimer));
        button.addEventListener('focus', () => {
            if (button.matches(':focus-visible')) {
                clearTimeout(autoHideTimer);
            }
        });

        button.addEventListener('pointerleave', () => {
            if (isVisible()) {
                startAutoHide();
            }
        });

        button.addEventListener('blur', () => {
            if (isVisible()) {
                startAutoHide();
            }
        });

        button.addEventListener('click', () => {
            const container = getScrollContainer();

            if (! container) {
                return;
            }

            container.scrollTo({
                left: button.classList.contains('is-reversed') ? 0 : container.scrollWidth,
                behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
            });
        });

        hideUntilIdle();
    })();
</script>
