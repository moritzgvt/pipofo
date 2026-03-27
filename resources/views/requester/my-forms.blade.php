<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('My Forms') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="status-filters mb-6 flex gap-2 flex-wrap">
                <a href="{{ route('requester.my-forms') }}" class="px-3 py-1 rounded-full text-sm {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">All</a>
                @foreach(['draft', 'submitted', 'accepted', 'declined', 'corrections'] as $s)
                    <a href="{{ route('requester.my-forms', ['status' => $s]) }}" class="px-3 py-1 rounded-full text-sm {{ request('status') === $s ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">{{ ucfirst($s) }}</a>
                @endforeach
            </div>

            @if($forms->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No forms found.</p>
                </div>
            @else
                <div class="form-list space-y-3">
                    @foreach($forms as $form)
                        <x-form-list-item :form="$form" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $forms->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
