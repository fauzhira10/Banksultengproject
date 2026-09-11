<div
    x-data="{
        theme: localStorage.getItem('theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light'),
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
        }
    }"
    x-init="
        theme = localStorage.getItem('theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light');
        window.addEventListener('theme-changed', (e) => {
            theme = e.detail;
        });
    "
    class="me-3 flex items-center"
>
    <div class="inline-flex items-center gap-0.5 rounded-full border border-gray-200/90 bg-gray-100/90 p-1 shadow-xs backdrop-blur-xs transition-colors dark:border-gray-700/80 dark:bg-gray-800/90">
        <!-- Mode Terang (Light) -->
        <button
            type="button"
            @click="setTheme('light')"
            :class="theme === 'light' 
                ? 'bg-white text-gray-900 shadow-xs font-semibold ring-1 ring-black/5 dark:bg-gray-700 dark:text-white' 
                : 'text-gray-500 hover:text-gray-900 font-medium dark:text-gray-400 dark:hover:text-white'"
            class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs cursor-pointer select-none transition-all duration-200 focus:outline-hidden"
            title="Mode Terang (Light Mode)"
        >
            <svg class="h-3.5 w-3.5 text-amber-500 shrink-0" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.243-1.591 1.591M5.25 12H3m4.243-4.773L5.652 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <span class="hidden sm:inline">Terang</span>
        </button>

        <!-- Mode Gelap (Dark) -->
        <button
            type="button"
            @click="setTheme('dark')"
            :class="theme === 'dark' 
                ? 'bg-gray-900 text-white shadow-xs font-semibold ring-1 ring-black/5 dark:bg-blue-600 dark:text-white' 
                : 'text-gray-500 hover:text-gray-900 font-medium dark:text-gray-400 dark:hover:text-white'"
            class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs cursor-pointer select-none transition-all duration-200 focus:outline-hidden"
            title="Mode Gelap (Dark Mode)"
        >
            <svg class="h-3.5 w-3.5 text-indigo-400 dark:text-blue-200 shrink-0" style="width: 14px; height: 14px; min-width: 14px; min-height: 14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
            <span class="hidden sm:inline">Gelap</span>
        </button>
    </div>
</div>
