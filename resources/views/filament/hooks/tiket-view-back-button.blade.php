{{--
    Tombol "Kembali" di kiri atas halaman Rincian Tiket (posisi breadcrumb).
    Jika pengguna datang dari daftar tiket, tombol memakai riwayat browser agar tab, filter,
    halaman, dan posisi scroll tabel tetap seperti semula. Selain itu, menuju daftar tiket.
--}}
@php
    use App\Filament\Resources\Tikets\TiketResource;

    $listUrl = TiketResource::getUrl('index');
@endphp

<div class="bs-back-button-ctn">
    <x-filament::button
        tag="a"
        :href="$listUrl"
        color="gray"
        icon="heroicon-m-arrow-left"
        size="sm"
        class="bs-back-button"
        x-data
        x-on:click="
            const listUrl = new URL($el.href);
            const referrerUrl = document.referrer ? new URL(document.referrer) : null;
            const isComingFromList = referrerUrl
                && referrerUrl.origin === listUrl.origin
                && referrerUrl.pathname === listUrl.pathname;

            if (isComingFromList && window.history.length > 1 && ! $event.ctrlKey && ! $event.metaKey) {
                $event.preventDefault();
                window.history.back();
            }
        "
    >
        Kembali ke Daftar Tiket
    </x-filament::button>
</div>
