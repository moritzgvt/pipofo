<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Employee Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="dashboard-stats grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <a href="{{ route('employee.submitted') }}" class="stat-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="stat-card-title text-lg font-medium text-gray-900 dark:text-gray-100">Submitted</h3>
                    <p class="stat-card-value mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $submitted }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Awaiting review</p>
                </a>
                <a href="{{ route('employee.corrections') }}" class="stat-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="stat-card-title text-lg font-medium text-gray-900 dark:text-gray-100">Corrections</h3>
                    <p class="stat-card-value mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $correctionsCount }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Waiting for requester</p>
                </a>
                <a href="{{ route('employee.completed') }}" class="stat-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-500 transition">
                    <h3 class="stat-card-title text-lg font-medium text-gray-900 dark:text-gray-100">Completed</h3>
                    <p class="stat-card-value mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $completedCount }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Accepted or declined</p>
                </a>
            </div>

            @if($recentForms->isNotEmpty())
                <div class="recent-forms bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Recent Activity</h3>
                    <div class="form-list space-y-3">
                        @foreach($recentForms as $form)
                            <x-form-list-item :form="$form" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
