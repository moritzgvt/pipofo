<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Pending Corrections') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            @if($forms->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-green-600 dark:text-green-400 font-medium">No pending corrections. You're all caught up!</p>
                </div>
            @else
                <div class="correction-list space-y-4">
                    @foreach($forms as $form)
                        <div class="correction-item bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between mb-3">
                                <a href="{{ route('forms.show', $form) }}" class="text-lg font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $form->title }}</a>
                                <x-status-badge :status="$form->status" />
                            </div>
                            @if($form->comments->isNotEmpty())
                                <div class="correction-feedback mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                        <strong>Latest feedback:</strong> {{ $form->comments->first()->body }}
                                    </p>
                                </div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('forms.edit', $form) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium rounded-md transition">
                                    Make Corrections
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $forms->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
