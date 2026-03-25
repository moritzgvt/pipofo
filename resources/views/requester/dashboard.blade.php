<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Available Templates</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $templates }}</p>
                    <a href="{{ route('requester.available-forms') }}" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Browse templates &rarr;</a>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">My Forms</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $myForms->count() }}</p>
                    <a href="{{ route('requester.my-forms') }}" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View all &rarr;</a>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pending Corrections</h3>
                    <p class="mt-2 text-3xl font-bold {{ $corrections > 0 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400' }}">{{ $corrections }}</p>
                    @if($corrections > 0)
                        <a href="{{ route('requester.pending-corrections') }}" class="mt-2 inline-block text-sm text-yellow-600 dark:text-yellow-400 hover:underline">Review now &rarr;</a>
                    @endif
                </div>
            </div>

            @if($myForms->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Forms</h3>
                        <div class="space-y-3">
                            @foreach($myForms as $form)
                                <x-form-list-item :form="$form" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
