@props(['form'])
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
            @if($form->relationLoaded('assignedEmployees') && $form->assignedEmployees->isNotEmpty())
                &middot; {{ __('assigned to') }} {{ $form->assignedEmployees->pluck('name')->join(', ') }}
            @endif
        </p>
    </div>
    <div class="ml-4 flex-shrink-0">
        <x-status-badge :status="$form->status" />
    </div>
</div>
