<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Deleted Forms') }}</h2>
            <a href="{{ route('employee.forms') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:underline">
                &larr; {{ __('Back to Forms') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            @php
                $hasActiveFilters = request()->hasAny(['previous_status', 'template', 'creator', 'updated_from', 'updated_to']);
            @endphp
            <form method="GET" action="{{ route('employee.deleted') }}" class="mb-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg" x-data="{ open: {{ $hasActiveFilters ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Filters & Sorting') }}
                        @if($hasActiveFilters)
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">{{ __('Active') }}</span>
                        @endif
                    </span>
                    <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div x-show="open" x-cloak class="px-6 pb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Previous Status --}}
                        <div>
                            <label for="previous_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Previous Status') }}</label>
                            <select name="previous_status" id="previous_status" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('All') }}</option>
                                <option value="draft" {{ request('previous_status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="submitted" {{ request('previous_status') === 'submitted' ? 'selected' : '' }}>{{ __('Submitted') }}</option>
                                <option value="corrections" {{ request('previous_status') === 'corrections' ? 'selected' : '' }}>{{ __('Corrections Requested') }}</option>
                                <option value="accepted" {{ request('previous_status') === 'accepted' ? 'selected' : '' }}>{{ __('Accepted') }}</option>
                                <option value="declined" {{ request('previous_status') === 'declined' ? 'selected' : '' }}>{{ __('Declined') }}</option>
                            </select>
                        </div>

                        {{-- Form Template --}}
                        <div>
                            <label for="template" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Template') }}</label>
                            <select name="template" id="template" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('All') }}</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ request('template') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Created by (Requester) --}}
                        <div>
                            <label for="creator" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Created by') }}</label>
                            <select name="creator" id="creator" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">{{ __('All') }}</option>
                                @foreach($creators as $creator)
                                    <option value="{{ $creator->id }}" {{ request('creator') == $creator->id ? 'selected' : '' }}>{{ $creator->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Last updated date range --}}
                        <div>
                            <label for="updated_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Updated from') }}</label>
                            <input type="date" name="updated_from" id="updated_from" value="{{ request('updated_from') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="updated_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Updated to') }}</label>
                            <input type="date" name="updated_to" id="updated_to" value="{{ request('updated_to') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        {{-- Sort --}}
                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Sort by') }}</label>
                            <div class="flex gap-2">
                                <select name="sort" id="sort" class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="updated_at" {{ request('sort', 'updated_at') === 'updated_at' ? 'selected' : '' }}>{{ __('Last updated') }}</option>
                                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>{{ __('Date created') }}</option>
                                </select>
                                <select name="direction" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>{{ __('Desc') }}</option>
                                    <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>{{ __('Asc') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">{{ __('Filter') }}</button>
                        <a href="{{ route('employee.deleted') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">{{ __('Reset') }}</a>
                    </div>
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
                                    @if($form->previous_status)
                                        &middot; was: {{ ucfirst($form->previous_status === 'corrections' ? 'Corrections Requested' : $form->previous_status) }}
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
