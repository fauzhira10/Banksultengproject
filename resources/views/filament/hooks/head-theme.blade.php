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
