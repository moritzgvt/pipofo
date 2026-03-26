<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $user->name }}</h2>
            <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Edit</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->role_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            @if($user->is_suspended)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Suspended</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Active</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            @if($user->isEmployeeBase() || $user->isRequester())
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    {{ $user->isEmployeeBase() ? 'Assigned Forms' : 'Created Forms' }}
                </h3>

                @if($forms->isEmpty())
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No forms found.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($forms as $form)
                            <x-form-list-item :form="$form" />
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
