<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Deleted Forms') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="mb-6">
                <a href="{{ route('employee.forms') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:underline">
                    &larr; {{ __('Back to Forms') }}
                </a>
            </div>

            <form method="GET" action="{{ route('employee.deleted') }}" class="mb-6">
                <div class="flex gap-4 items-end flex-wrap">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" aria-label="Search forms">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Filter</button>
                </div>
            </form>

            @if($forms->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('No deleted forms found.') }}</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($forms as $form)
                        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('forms.show', $form) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline truncate">
                                    {{ $form->title }}
                                </a>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $form->formTemplate->name ?? 'Unknown Template' }}
                                    &middot; {{ $form->created_at->format('M d, Y') }}
                                    @if($form->user)
                                        &middot; by {{ $form->user->name }}
                                    @endif
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex items-center gap-2">
                                <x-status-badge :status="$form->status" />
                                <form method="POST" action="{{ route('forms.restore', $form) }}" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Restore this form?')" class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-xs">Restore</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $forms->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
