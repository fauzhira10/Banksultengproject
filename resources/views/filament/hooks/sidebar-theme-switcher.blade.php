<div
    x-data="{
        theme: localStorage.getItem('theme') || 'light',
        setTheme(mode) {
            this.theme = mode;
            localStorage.setItem('theme', mode);
            localStorage.setItem('theme_preference_v1', 'true');
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: mode }));

            if (mode === 'dark') {
                document.documentElement.classList.add('dark');
            } else if (mode === 'light') {
                document.documentElement.classList.remove('dark');
            } else if (mode === 'system') {
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            if (window.Alpine && window.Alpine.store('theme')) {
                window.Alpine.store('theme', (mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) ? 'dark' : 'light');
            }
        },
        close() {}
    }"
    x-init="
        theme = localStorage.getItem('theme') || 'light';
        window.addEventListener('theme-changed', (e) => {
            theme = e.detail;
        });
    "
    class="px-4 py-3 border-t border-gray-200 dark:border-white/10"
>
    <div class="mb-1.5 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span class="font-medium">Pilihan Tampilan</span>
        <span class="text-[11px] font-semibold text-primary-600 dark:text-primary-400" x-text="theme === 'dark' ? 'Mode Hitam' : 'Mode Terang'"></span>
    </div>

    <div class="grid grid-cols-2 gap-1 rounded-lg border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
        <button
            type="button"
            @click="setTheme('light')"
            :class="theme === 'light' 
                ? 'bg-white text-blue-700 shadow-sm font-semibold dark:bg-gray-700 dark:text-blue-300' 
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
            class="flex items-center justify-center gap-1.5 rounded-md py-1.5 text-xs transition-all"
            title="Pilih Tampilan Terang"
        >
            <svg class="h-3.5 w-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.243-1.591 1.591M5.25 12H3m4.243-4.773L5.652 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <span>Terang</span>
        </button>

        <button
            type="button"
            @click="setTheme('dark')"
            :class="theme === 'dark' 
                ? 'bg-gray-900 text-white shadow-sm font-semibold dark:bg-blue-600 dark:text-white' 
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
            class="flex items-center justify-center gap-1.5 rounded-md py-1.5 text-xs transition-all"
            title="Pilih Tampilan Hitam"
        >
            <svg class="h-3.5 w-3.5 text-blue-400 dark:text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            <span>Hitam</span>
        </button>
    </div>
</div>
