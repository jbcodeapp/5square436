<x-filament::widget class="filament-account-widget">
    <x-filament::card>
        @php
            $user = \Filament\Facades\Filament::auth()->user();
        @endphp

        <div class="h-12 flex items-center space-x-4 rtl:space-x-reverse">

            <div class="flex-1">
                <h2 class="text-lg flex-1 sm:text-xl font-bold tracking-tight">
                    Monthly Cron
                </h2>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Is Repeat ticket clone/copy every month, Please click run</p>
            </div>
            <form action="{{ route('monthly.cron.run') }}" method="post" class="text-sm">
                @csrf
                <x-filament::button
                    onclick="return confirm('Are you sure Run monthly Cron? ')"
                    type="submit"
                    @class([
                        'text-gray-600 hover:text-primary-500 focus:outline-none focus:underline',
                        'dark:text-gray-300 dark:hover:text-primary-500' => config('filament.dark_mode'),
                    ])
                > RUN
                </x-filament::button>
            </form>
        </div>
    </x-filament::card>
</x-filament::widget>
