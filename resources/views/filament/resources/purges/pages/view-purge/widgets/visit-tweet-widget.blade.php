<x-filament-widgets::widget>
    <div class="flex justify-center p-6">
        <x-filament::button
            tag="a"
            href="https://x.com/{{ $record->account?->username ?? 'drewroberts' }}/status/{{ $record->post_id }}"
            target="_blank"
            rel="noopener noreferrer"
            color="gray"
            size="lg"
        >
            <x-slot name="icon">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </x-slot>
            Visit Tweet
        </x-filament::button>
    </div>
</x-filament-widgets::widget>
