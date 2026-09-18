{{-- Logo Resmi Bank Sulteng di Tengah Navbar --}}
<div class="bs-topbar-center-logo pointer-events-auto">
    <a 
        href="{{ filament()->getHomeUrl() ?? url('/admin') }}" 
        class="group inline-flex items-center justify-center transition-transform duration-200 hover:scale-[1.02] focus:outline-none"
        title="PT Bank Sulteng"
    >
        <img 
            src="{{ asset('images/logo-bank-sulteng.png') }}" 
            alt="Bank Sulteng" 
            class="h-7 sm:h-8 md:h-9 w-auto max-w-[170px] sm:max-w-[210px] md:max-w-[250px] object-contain drop-shadow-xs transition-opacity duration-200 group-hover:opacity-90"
        />
    </a>
</div>
