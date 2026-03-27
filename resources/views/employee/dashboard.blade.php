<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Employee Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('requester.available-forms') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Available Templates</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $templatesCount }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create new forms</p>
                </a>
                <a href="{{ route('requester.my-forms') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">My Forms</h3>
                    <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $myFormsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Forms you created</p>
                </a>
                <a href="{{ route('requester.pending-corrections') }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">My Corrections</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $myCorrections }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your forms needing corrections</p>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('employee.forms', ['status' => 'submitted']) }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Submitted</h3>
                    <p class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $submitted }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Awaiting review</p>
                </a>
                <a href="{{ route('employee.forms', ['status' => 'corrections']) }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Corrections</h3>
                    <p class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $correctionsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Waiting for requester</p>
                </a>
                <a href="{{ route('employee.forms', ['status' => 'accepted']) }}" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Completed</h3>
                    <p class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $completedCount }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Accepted or declined</p>
                </a>
            </div>

            @if($recentForms->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>
                    <div class="space-y-3">
                        @foreach($recentForms as $form)
                            <x-form-list-item :form="$form" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
