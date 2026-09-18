{{-- Indikator & Animasi Loading Persisten Halaman Login Bank Sulteng --}}
<div
    id="bs-login-loading-overlay"
    class="pointer-events-none fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 opacity-0 backdrop-blur-md transition-all duration-300"
    aria-hidden="true"
>
    <div class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-blue-500/30 bg-slate-900/95 p-6 text-center shadow-2xl shadow-blue-950/60 ring-1 ring-white/10 sm:p-8">
        {{-- Aksen Garis Gradasi Atas --}}
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-600 via-cyan-400 to-indigo-600"></div>

        {{-- Ambient Glow --}}
        <div class="pointer-events-none absolute -top-12 left-1/2 h-32 w-32 -translate-x-1/2 rounded-full bg-blue-500/20 blur-2xl filter"></div>

        {{-- Logo & Spinner Wrapper --}}
        <div class="relative mx-auto mb-5 flex h-20 w-20 items-center justify-center">
            {{-- Outer Spinning Ring --}}
            <div class="absolute inset-0 rounded-full border-2 border-transparent border-t-cyan-400 border-r-blue-500 animate-spin"></div>
            {{-- Reverse Inner Pulse Ring --}}
            <div class="absolute inset-1.5 rounded-full border border-blue-400/20 animate-pulse"></div>

            {{-- Bank Logo Box --}}
            <div class="relative flex h-14 w-14 items-center justify-center rounded-xl bg-white p-2 shadow-md">
                <img src="{{ asset('images/logo-bank-sulteng.png') }}" alt="Bank Sulteng" class="h-auto max-h-10 w-auto object-contain">
            </div>
        </div>

        {{-- Judul Sistem & Keterangan Status --}}
        <h3 class="text-base font-bold tracking-tight text-white sm:text-lg">
            Sistem Monitoring SLA ATM
        </h3>
        <p class="mt-1 text-xs text-slate-400">
            PT Bank Pembangunan Daerah Sulawesi Tengah
        </p>

        {{-- Status Pesan Loading Dinamis --}}
        <div class="mt-5 inline-flex items-center gap-2 rounded-full border border-blue-500/30 bg-blue-950/60 px-3.5 py-1.5 text-xs font-semibold text-blue-300">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-cyan-500"></span>
            </span>
            <span id="bs-loading-status-text">Memverifikasi Kredensial...</span>
        </div>

        {{-- Baris Progress Bar Halus Bergerak --}}
        <div class="mt-5 h-1.5 w-full overflow-hidden rounded-full bg-slate-800">
            <div class="h-full w-full origin-left bg-gradient-to-r from-blue-500 via-cyan-400 to-indigo-500 animate-[bs-progress_1.2s_ease-in-out_infinite]"></div>
        </div>
        <p class="mt-3 text-[11px] text-slate-400">
            Mohon tunggu sebentar, sedang menyiapkan portal...
        </p>
    </div>
</div>

<style>
    @keyframes bs-progress {
        0% {
            transform: translateX(-100%);
        }
        50% {
            transform: translateX(0%);
        }
        100% {
            transform: translateX(100%);
        }
    }
</style>

<script>
    (function () {
        const overlay = document.getElementById('bs-login-loading-overlay');
        const statusText = document.getElementById('bs-loading-status-text');
        let isRedirecting = false;
        let originalButtonHtml = null;

        function getSubmitButton() {
            return document.getElementById('bs-login-submit-btn') ||
                document.querySelector('.fi-simple-main button[type="submit"]') ||
                document.querySelector('form button[type="submit"]');
        }

        function showLoading(message) {
            if (statusText && message) {
                statusText.textContent = message;
            }

            const btn = getSubmitButton();
            if (btn) {
                if (!originalButtonHtml && !btn.dataset.loadingActive) {
                    originalButtonHtml = btn.innerHTML;
                }
                btn.dataset.loadingActive = 'true';
                btn.disabled = true;
                btn.classList.add('opacity-90', 'cursor-wait');

                const currentText = message || 'Memverifikasi...';
                btn.innerHTML = `
                    <span class="inline-flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>${currentText}</span>
                    </span>
                `;
            }
        }

        function showOverlay(message) {
            if (!overlay) return;
            if (statusText && message) {
                statusText.textContent = message;
            }
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100', 'pointer-events-auto');
            overlay.setAttribute('aria-hidden', 'false');
        }

        function hideLoading() {
            if (isRedirecting) {
                // Jika sedang proses pengalihan rute ke sistem, jangan pernah menyembunyikan animasi!
                return;
            }

            if (overlay) {
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                overlay.setAttribute('aria-hidden', 'true');
            }

            const btn = getSubmitButton();
            if (btn && btn.dataset.loadingActive === 'true') {
                delete btn.dataset.loadingActive;
                btn.disabled = false;
                btn.classList.remove('opacity-90', 'cursor-wait');
                if (originalButtonHtml) {
                    btn.innerHTML = originalButtonHtml;
                }
            }
        }

        // Tangani submit form login
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (form && (form.getAttribute('wire:submit') || form.closest('.fi-simple-main'))) {
                showLoading('Memverifikasi Kredensial...');
            }
        }, true);

        // Integrasi dengan siklus Livewire 3
        function bindLivewireHooks() {
            if (typeof Livewire === 'undefined') {
                return;
            }

            Livewire.hook('commit', ({ component, commit, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    // Periksa apakah server memberikan sinyal redirect (autentikasi berhasil)
                    if (effect && (effect.redirect || effect.url)) {
                        isRedirecting = true;
                        showLoading('Berhasil Masuk! Membuka Portal...');
                        showOverlay('Berhasil Masuk! Membuka Portal...');
                        // Animasi loading DITAHAN TETAP AKTIF sampai window berpindah halaman
                        return;
                    }

                    // Jika ada error validasi di snapshot
                    const errors = snapshot && snapshot.memo && snapshot.memo.errors;
                    const hasErrors = errors && Object.keys(errors).length > 0;

                    if (hasErrors) {
                        isRedirecting = false;
                        setTimeout(hideLoading, 50);
                    } else {
                        // Cek apakah ada pesan error di DOM setelah render
                        setTimeout(function () {
                            const errorInDom = document.querySelector('.fi-fo-field-wrp-error-message, .text-danger-600, [aria-live="assertive"]');
                            if (errorInDom) {
                                isRedirecting = false;
                                hideLoading();
                            }
                        }, 60);
                    }
                });

                fail(() => {
                    isRedirecting = false;
                    hideLoading();
                });
            });

            // Pantau error yang mungkin dimunculkan oleh event notifikasi atau DOM
            Livewire.hook('morph.updated', () => {
                if (!isRedirecting) {
                    const hasError = document.querySelector('.fi-fo-field-wrp-error-message, .text-danger-600');
                    if (hasError) {
                        hideLoading();
                    }
                }
            });
        }

        if (window.Livewire) {
            bindLivewireHooks();
        } else {
            document.addEventListener('livewire:init', bindLivewireHooks);
        }

        // Pastikan overlay tetap menyala saat browser mulai unload/navigasi ke URL sistem
        window.addEventListener('beforeunload', function () {
            if (isRedirecting) {
                showOverlay('Menyiapkan Tampilan Sistem...');
            }
        });
    })();
</script>
