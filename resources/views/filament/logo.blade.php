<div class="flex items-center justify-between w-full" style="min-width: 150px;">
    <div style="display: flex; align-items: center; margin-left: -0.75rem;">
        <img
            src="{{ asset('assets/images/logo_orig_svg.svg') }}"
            alt="{{ config('app.name') }}"
            class="custom-brand-logo"
            style="height: 100%; width: auto; object-fit: contain;"
        />
    </div>
    
    <button x-on:click.prevent="$store.sidebar.close()" type="button" class="lg:hidden text-white hover:text-gray-200 transition p-2" aria-label="Cerrar menú">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>
