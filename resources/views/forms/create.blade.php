<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Form') }}: {{ $formTemplate->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            @if($formTemplate->description)
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-blue-800 dark:text-blue-200">{{ $formTemplate->description }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('requester.forms.store', $formTemplate) }}" class="form-create bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @csrf
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Form Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $formTemplate->name . ' - ' . now()->format('M d, Y')) }}" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">This form has {{ $formTemplate->inputFieldTemplates->count() }} fields. You can fill them after creating the form.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Create Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
