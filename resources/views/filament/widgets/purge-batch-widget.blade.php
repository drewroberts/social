<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Batch Update Purges
        </x-slot>

        <x-slot name="description">
            Mark multiple purges as saved or unsaved by searching for text content
        </x-slot>

        <div>
            <div style="margin-bottom: 1.5rem;">
                <x-filament::input.wrapper>
                    <x-filament::input
                        type="text"
                        wire:model.live.debounce.500ms="searchText"
                        placeholder="Enter text to search for in purges..."
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="flex flex-wrap items-center gap-6" style="margin-bottom: 1.5rem;">
                <label class="flex items-center gap-2 cursor-pointer">
                    <x-filament::input.checkbox wire:model.live="caseSensitive" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Case Sensitive</span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                    <x-filament::input.checkbox wire:model.live="useRegex" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Use Regex</span>
                </label>
            </div>

            <div class="flex flex-wrap items-center gap-6" style="margin-bottom: 1.5rem;">
                <div style="width: 200px;">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="operation">
                            <option value="save">Mark as Saved</option>
                            <option value="unsave">Mark as Unsaved</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>

            @if($searchText)
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-400" style="margin-bottom: 1.5rem;">
                    Found <span class="font-semibold text-primary-600 dark:text-primary-400">{{ $this->getSearchCount() }}</span> 
                    {{ $operation === 'save' ? 'unsaved' : 'saved' }} purge(s) 
                    @if($useRegex)
                        matching pattern "{{ $searchText }}"
                    @else
                        containing "{{ $searchText }}"
                    @endif
                    @if($caseSensitive)
                        (case sensitive)
                    @endif
                </div>
            @endif

            <div class="flex gap-3">
                @if($operation === 'save')
                    <x-filament::button
                        wire:click="batchSave"
                        color="primary"
                        icon="heroicon-o-bookmark"
                        :disabled="!$searchText || $this->getSearchCount() === 0"
                    >
                        Mark as Saved
                    </x-filament::button>
                @else
                    <x-filament::button
                        wire:click="batchUnsave"
                        color="warning"
                        icon="heroicon-o-bookmark-slash"
                        :disabled="!$searchText || $this->getSearchCount() === 0"
                    >
                        Mark as Unsaved
                    </x-filament::button>
                @endif

                @if($searchText)
                    <x-filament::button
                        color="gray"
                        icon="heroicon-o-x-mark"
                        wire:click="$set('searchText', '')"
                    >
                        Clear
                    </x-filament::button>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
