<script>
    (function () {
        localStorage.setItem('theme', 'light');
        document.documentElement.classList.remove('dark');
    })();
</script>

<style>
    /* Header kolom aksi & kolom numerik agar posisinya tepat di tengah (center) */
    th.fi-ta-header-cell.fi-align-end {
        text-align: center !important;
    }
    th.fi-ta-header-cell.fi-align-end .fi-ta-header-cell-sort-btn {
        justify-content: center !important;
    }
    th.fi-ta-header-cell.fi-align-center {
        text-align: center !important;
    }
    th.fi-ta-header-cell.fi-align-center .fi-ta-header-cell-sort-btn {
        justify-content: center !important;
    }
</style>
