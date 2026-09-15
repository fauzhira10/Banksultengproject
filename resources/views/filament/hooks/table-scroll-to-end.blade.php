{{--
    Tombol panah bulat melayang untuk menggeser tabel ke kolom paling kanan (Aksi)
    dan berbalik arah untuk kembali ke kolom awal saat sudah di ujung kanan.
    - Menghilang sementara saat pengguna melakukan scroll up/down halaman agar tidak mengganggu pandangan,
      lalu muncul kembali dengan halus saat pengguna berhenti scroll.
    - Tetap tampil stabil selama pengguna sedang membaca data / diam di tabel yang mengalami overflow horizontal.
    - Memposisikan diri secara dinamis di tepi kanan tabel, lalu berpindah ke tepi kiri saat di ujung kanan agar tidak menutupi tombol Aksi.
    - Responsif terhadap perubahan ukuran layar, pergeseran scroll halaman, pergantian tab Livewire, filter, dan pagination.
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
    <span class="bs-scroll-end-tooltip" data-bs-tooltip>Ke Kolom Aksi &rarr;</span>
</button>

<style>
    .bs-scroll-end-btn {
        --bs-nudge-distance: 4px;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.25rem;
        height: 3.25rem;
        padding: 0;
        border: 0;
        border-radius: 9999px;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.95),
            0 14px 28px -6px rgba(29, 78, 216, 0.55),
            0 6px 12px -4px rgba(15, 23, 42, 0.25);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transform: scale(0.85);
        transition:
            opacity 180ms cubic-bezier(0.4, 0, 0.2, 1),
            transform 220ms cubic-bezier(0.34, 1.56, 0.64, 1),
            box-shadow 180ms ease,
            visibility 0s linear 180ms;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
    }

    .bs-scroll-end-btn.is-visible {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
        transition-delay: 0s;
    }

    .bs-scroll-end-btn.is-visible:hover {
        transform: scale(1.08);
        box-shadow:
            0 0 0 4px rgba(255, 255, 255, 0.98),
            0 18px 36px -6px rgba(29, 78, 216, 0.7),
            0 8px 16px -4px rgba(15, 23, 42, 0.3);
    }

    .bs-scroll-end-btn.is-visible:active {
        transform: scale(0.95);
    }

    .bs-scroll-end-btn:focus-visible {
        outline: 3px solid #60a5fa;
        outline-offset: 4px;
    }

    html.dark .bs-scroll-end-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        box-shadow:
            0 0 0 4px rgba(15, 23, 42, 0.95),
            0 14px 30px -6px rgba(0, 0, 0, 0.8),
            0 6px 14px -4px rgba(59, 130, 246, 0.3);
    }

    html.dark .bs-scroll-end-btn.is-visible:hover {
        box-shadow:
            0 0 0 4px rgba(15, 23, 42, 0.98),
            0 18px 36px -6px rgba(37, 99, 235, 0.6),
            0 8px 18px -4px rgba(0, 0, 0, 0.85);
    }

    .bs-scroll-end-btn svg {
        width: 1.5rem;
        height: 1.5rem;
        transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
    }

    .bs-scroll-end-btn.is-reversed {
        --bs-nudge-distance: -4px;
    }

    .bs-scroll-end-btn.is-reversed svg {
        transform: rotate(180deg);
    }

    .bs-scroll-end-btn.is-visible:not(:hover) svg {
        animation: bs-arrow-nudge 1.5s ease-in-out infinite 0.5s;
    }

    @keyframes bs-arrow-nudge {
        0%, 100% {
            translate: 0 0;
        }
        50% {
            translate: var(--bs-nudge-distance) 0;
        }
    }

    .bs-scroll-end-tooltip {
        position: absolute;
        white-space: nowrap;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        padding: 0.3rem 0.65rem;
        border-radius: 9999px;
        background-color: #0f172a;
        color: #f8fafc;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
        pointer-events: none;
        opacity: 0;
        transform: translateY(2px);
        transition: opacity 150ms ease, transform 150ms ease;
    }

    html.dark .bs-scroll-end-tooltip {
        background-color: #f8fafc;
        color: #0f172a;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.6);
    }

    .bs-scroll-end-btn:hover .bs-scroll-end-tooltip,
    .bs-scroll-end-btn:focus-visible .bs-scroll-end-tooltip {
        opacity: 1;
        transform: translateY(0);
    }

    .bs-scroll-end-btn:not(.is-reversed) .bs-scroll-end-tooltip {
        right: calc(100% + 10px);
    }

    .bs-scroll-end-btn.is-reversed .bs-scroll-end-tooltip {
        left: calc(100% + 10px);
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

        const tooltip = button.querySelector('[data-bs-tooltip]');
        const BUTTON_SIZE_PX = 52;
        const BUTTON_INSET_PX = 24;
        const MIN_OVERFLOW_PX = 10;
        const MIN_VISIBLE_HEIGHT_PX = 80;
        const VERTICAL_SCROLL_IDLE_MS = 200;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

        let activeContainer = null;
        let activeContainerScrollListener = null;
        let rafId = null;
        let verticalScrollTimer = null;
        let isVerticalScrolling = false;

        const getScrollContainer = () => document.querySelector('.fi-ta-content-ctn');

        const isVisible = () => button.classList.contains('is-visible');

        const hide = () => {
            if (! isVisible()) {
                return;
            }

            button.classList.remove('is-visible');
            button.setAttribute('aria-hidden', 'true');
            button.setAttribute('tabindex', '-1');

            if (document.activeElement === button) {
                button.blur();
            }
        };

        const show = () => {
            if (isVisible()) {
                return;
            }

            button.classList.add('is-visible');
            button.removeAttribute('aria-hidden');
            button.setAttribute('tabindex', '0');
        };

        const updateButtonState = () => {
            // Jangan tampilkan tombol jika pengguna masih aktif melakukan scroll vertikal
            if (isVerticalScrolling) {
                return;
            }

            const container = getScrollContainer();

            if (! container) {
                hide();
                return;
            }

            // Pasang horizontal scroll listener pada tabel container jika berganti
            if (activeContainer !== container) {
                if (activeContainer && activeContainerScrollListener) {
                    activeContainer.removeEventListener('scroll', activeContainerScrollListener);
                }

                activeContainer = container;
                activeContainerScrollListener = () => {
                    // Scroll horizontal pada tabel: perbarui arah panah langsung tanpa menyembunyikan tombol
                    scheduleImmediateUpdate();
                };
                container.addEventListener('scroll', activeContainerScrollListener, { passive: true });
            }

            const maxScrollLeft = container.scrollWidth - container.clientWidth;

            // Jika tabel tidak mengalami overflow horizontal sama sekali, sembunyikan tombol
            if (maxScrollLeft <= MIN_OVERFLOW_PX) {
                hide();
                return;
            }

            const tableRect = container.getBoundingClientRect();
            const topbar = document.querySelector('.fi-topbar') || document.querySelector('header');
            const topbarBottom = topbar ? topbar.getBoundingClientRect().bottom : 0;

            const visibleTop = Math.max(tableRect.top, topbarBottom);
            const visibleBottom = Math.min(tableRect.bottom, window.innerHeight);
            const visibleHeight = visibleBottom - visibleTop;

            // Jika tabel sedang berada di luar viewport vertikal, sembunyikan tombol
            if (visibleHeight < MIN_VISIBLE_HEIGHT_PX || tableRect.bottom <= topbarBottom || tableRect.top >= window.innerHeight) {
                hide();
                return;
            }

            // Cek apakah tabel sudah berada di ujung kanan (kolom Aksi)
            const isAtEnd = container.scrollLeft >= (maxScrollLeft - 16);
            const label = isAtEnd ? 'Kembali ke kolom awal' : 'Geser ke kolom Aksi';
            const tooltipText = isAtEnd ? '← Kembali ke Awal' : 'Ke Kolom Aksi →';

            button.classList.toggle('is-reversed', isAtEnd);
            button.setAttribute('aria-label', label);
            button.title = label;

            if (tooltip) {
                tooltip.textContent = tooltipText;
            }

            // Hitung koordinat tombol melayang
            const top = visibleTop + (visibleHeight / 2) - (BUTTON_SIZE_PX / 2);
            const left = isAtEnd
                ? Math.max(tableRect.left, 0) + BUTTON_INSET_PX
                : Math.min(tableRect.right, window.innerWidth) - BUTTON_SIZE_PX - BUTTON_INSET_PX;

            button.style.top = `${Math.round(top)}px`;
            button.style.left = `${Math.round(left)}px`;

            show();
        };

        const scheduleImmediateUpdate = () => {
            if (rafId) {
                cancelAnimationFrame(rafId);
            }

            rafId = requestAnimationFrame(() => {
                rafId = null;
                updateButtonState();
            });
        };

        /**
         * Handler saat pengguna melakukan scroll up / down pada halaman:
         * Sembunyikan tombol seketika saat scroll bergerak, dan tampilkan kembali
         * setelah pengguna berhenti scroll (idle).
         */
        const handleVerticalScroll = (event) => {
            // Jika event scroll berasal dari container tabel (scroll horizontal), abaikan penyembunyian
            const container = getScrollContainer();
            if (container && event.target === container) {
                return;
            }

            isVerticalScrolling = true;

            // Sembunyikan tombol seketika saat scroll ke atas/bawah dimulai (kecuali sedang di-hover)
            if (! button.matches(':hover')) {
                hide();
            }

            clearTimeout(verticalScrollTimer);
            verticalScrollTimer = setTimeout(() => {
                isVerticalScrolling = false;
                scheduleImmediateUpdate();
            }, VERTICAL_SCROLL_IDLE_MS);
        };

        // Klik tombol: scroll smooth ke ujung atau kembali ke awal
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const container = getScrollContainer();

            if (! container) {
                return;
            }

            const maxScrollLeft = container.scrollWidth - container.clientWidth;
            const isAtEnd = container.scrollLeft >= (maxScrollLeft - 16);

            container.scrollTo({
                left: isAtEnd ? 0 : maxScrollLeft,
                behavior: prefersReducedMotion.matches ? 'auto' : 'smooth',
            });
        });

        // Event listener scroll vertikal jendela & resize
        window.addEventListener('scroll', handleVerticalScroll, { capture: true, passive: true });
        window.addEventListener('resize', scheduleImmediateUpdate, { passive: true });

        // ResizeObserver & MutationObserver untuk mendeteksi perubahan tabel (tab, filter, pagination, query Livewire)
        const resizeObserver = new ResizeObserver(() => scheduleImmediateUpdate());
        resizeObserver.observe(document.body);

        const mutationObserver = new MutationObserver(() => scheduleImmediateUpdate());
        mutationObserver.observe(document.body, { childList: true, subtree: true });

        // Livewire lifecycle hooks
        document.addEventListener('livewire:navigated', scheduleImmediateUpdate);
        document.addEventListener('livewire:initialized', scheduleImmediateUpdate);

        if (window.Livewire) {
            window.Livewire.hook('morph.updated', () => scheduleImmediateUpdate());
            window.Livewire.hook('commit', () => scheduleImmediateUpdate());
        }

        // Inisialisasi awal
        scheduleImmediateUpdate();
        setTimeout(scheduleImmediateUpdate, 150);
        setTimeout(scheduleImmediateUpdate, 500);
    })();
</script>
