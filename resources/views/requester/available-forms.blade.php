<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Available Forms') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            @if($templates->isEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No form templates available yet.</p>
                </div>
            @else
                <div class="template-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($templates as $template)
                        <div class="template-card bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="template-card-title text-lg font-medium text-gray-900 dark:text-gray-100">{{ $template->name }}</h3>
                            @if($template->description)
                                <p class="template-card-description mt-2 text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($template->description, 100) }}</p>
                            @endif
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">{{ $template->input_field_templates_count }} fields</p>
                            <a href="{{ route('requester.forms.create', $template) }}" class="template-card-action mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                Start Form
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $templates->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
