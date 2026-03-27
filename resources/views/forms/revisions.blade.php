<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Revision History') }}: {{ $form->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('forms.show', $form) }}" class="mb-6 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to form</a>

            @if($revisions->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No revisions yet.</p>
                </div>
            @else
                <div class="revision-list space-y-4">
                    @foreach($revisions as $revision)
                        <div class="revision-item bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $revision->user->name }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $revision->created_at->format('M d, Y H:i') }}</span>
                            </div>
                            @if($revision->message)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 italic">{{ $revision->message }}</p>
                            @endif
                            <div class="revision-diff space-y-2">
                                @foreach($revision->diff as $fieldId => $changes)
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $fieldTemplates[$fieldId]->label ?? "Field #$fieldId" }}:</span>
                                        <div class="ml-4 flex gap-4">
                                            <span class="text-red-600 dark:text-red-400 line-through">{{ $changes['old'] ?: '(empty)' }}</span>
                                            <span class="text-gray-400">&rarr;</span>
                                            <span class="text-green-600 dark:text-green-400">{{ $changes['new'] ?: '(empty)' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">{{ $revisions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
