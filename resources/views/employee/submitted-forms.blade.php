<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Submitted Forms') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <form method="GET" action="{{ route('employee.submitted') }}" class="mb-6">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title or requester..." class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" aria-label="Search forms">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Search</button>
                </div>
            </form>

            @if($forms->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No submitted forms to review.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($forms as $form)
                        <x-form-list-item :form="$form" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $forms->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
