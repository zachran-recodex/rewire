<x-layouts.main :title="__('Home')">
    <div class="flex flex-col items-center gap-6">
        {{-- Logo + Brand --}}
        <div class="flex items-center gap-3 animate-pulse">
            <img src="{{ asset('android-chrome-512x512.png') }}" alt="Rewire Logo" class="size-24 drop-shadow-lg">
            <span class="text-6xl font-extrabold tracking-wide bg-gradient-to-r from-blue-500 to-cyan-500 bg-clip-text text-transparent">
                Rewire
            </span>
        </div>

        {{-- Coming Soon Typing Effect --}}
        <span
            class="mt-4 text-xl font-medium text-gray-600 dark:text-gray-300 relative overflow-hidden whitespace-nowrap border-r-2 border-gray-600 dark:border-gray-300 animate-typing">
            Coming Soon...
        </span>

        {{-- Theme Switcher --}}
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="mt-6">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
        </flux:radio.group>
    </div>

    {{-- Custom Animation --}}
    <style>
        @keyframes typing {
            from { width: 0 }
            to { width: 100% }
        }
        @keyframes blink {
            50% { border-color: transparent }
        }
        .animate-typing {
            display: inline-block;
            width: 0;
            animation: typing 2.5s steps(12, end) forwards, blink .7s step-end infinite;
        }
    </style>
</x-layouts.main>
