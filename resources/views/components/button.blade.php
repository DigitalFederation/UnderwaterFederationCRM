<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2 bg-linear-to-r from-primary to-primary-light border border-transparent rounded-lg font-medium text-sm text-white tracking-wide hover:from-primary/90 hover:to-primary-light/90 active:from-primary/90 active:to-primary focus:outline-hidden focus:border-primary focus:ring-3 focus:ring-primary-light/30 disabled:opacity-50 transition-colors duration-150 shadow-xs']) }}>
    {{ $slot }}
</button>
