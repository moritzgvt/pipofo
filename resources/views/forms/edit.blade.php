<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit') }}: {{ $form->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <form method="POST" action="{{ route('forms.update', $form) }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @csrf
                @method('PUT')

                @foreach($form->fields->sortBy(fn($f) => $f->inputFieldTemplate->order) as $field)
                    @php
                        $template = $field->inputFieldTemplate;
                        $fieldComments = $form->comments->where('input_field_template_id', $template->id);
                        $hasNewComments = $lastViewedAt && $fieldComments->contains(fn($c) => $c->created_at->gt($lastViewedAt));
                    @endphp
                    <div class="mb-6 {{ $hasNewComments ? 'ring-2 ring-yellow-400 dark:ring-yellow-500 rounded-lg p-3 bg-yellow-50/30 dark:bg-yellow-900/10' : '' }}">
                        <label for="field_{{ $template->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $template->label }}
                            @if($template->required) <span class="text-red-500">*</span> @endif
                            @if($hasNewComments) <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200">New</span> @endif
                        </label>
                        @if($template->help_text)
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $template->help_text }}</p>
                        @endif

                        @switch($template->type)
                            @case('textarea')
                                <textarea name="fields[{{ $template->id }}]" id="field_{{ $template->id }}" rows="4" {{ $template->required ? 'required' : '' }} placeholder="{{ $template->placeholder }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('fields.' . $template->id, $field->value) }}</textarea>
                                @break
                            @case('select')
                                <select name="fields[{{ $template->id }}]" id="field_{{ $template->id }}" {{ $template->required ? 'required' : '' }} class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Select --</option>
                                    @foreach($template->options ?? [] as $option)
                                        <option value="{{ $option }}" {{ old('fields.' . $template->id, $field->value) == $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                                @break
                            @case('checkbox')
                                <div class="mt-2">
                                    <input type="hidden" name="fields[{{ $template->id }}]" value="0">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="fields[{{ $template->id }}]" value="1" {{ old('fields.' . $template->id, $field->value) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $template->label }}</span>
                                    </label>
                                </div>
                                @break
                            @case('radio')
                                <div class="mt-2 space-y-2">
                                    @foreach($template->options ?? [] as $option)
                                        <label class="inline-flex items-center mr-4">
                                            <input type="radio" name="fields[{{ $template->id }}]" value="{{ $option }}" {{ old('fields.' . $template->id, $field->value) == $option ? 'checked' : '' }} class="border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @break
                            @default
                                <input type="{{ $template->type === 'number' ? 'number' : ($template->type === 'date' ? 'date' : 'text') }}" name="fields[{{ $template->id }}]" id="field_{{ $template->id }}" value="{{ old('fields.' . $template->id, $field->value) }}" {{ $template->required ? 'required' : '' }} placeholder="{{ $template->placeholder }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @endswitch

                        {{-- Field-specific comments (collapsible, visually distinct) --}}
                        @if($fieldComments->isNotEmpty() || !$form->isCompleted())
                            <div x-data="{ expanded: {{ $fieldComments->isNotEmpty() ? 'true' : 'false' }}, showForm: false, body: '', submitting: false, error: '' }" class="mt-2 bg-white dark:bg-gray-900/20 border border-gray-200 dark:border-white rounded-lg p-3">
                                <button @click="expanded = !expanded" type="button" class="flex items-center gap-1 text-xs font-medium text-gray-800 dark:text-gray-200 hover:underline">
                                    <svg :class="expanded ? 'rotate-90' : ''" class="w-3 h-3 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    Comments ({{ $fieldComments->count() }})
                                </button>
                                <div x-show="expanded" x-cloak class="mt-2">
                                    @if($fieldComments->isNotEmpty())
                                        <div class="space-y-2">
                                            @foreach($fieldComments as $comment)
                                                @php $isNew = $lastViewedAt && $comment->created_at->gt($lastViewedAt); @endphp
                                                <div class="p-2 rounded-md text-sm {{ $isNew ? 'bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-400 dark:border-yellow-600' : ($comment->is_employee_comment ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' : 'bg-white dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600') }}">
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="text-xs font-medium {{ $comment->is_employee_comment ? 'text-blue-800 dark:text-blue-200' : 'text-gray-800 dark:text-gray-200' }}">
                                                            {{ $comment->user->name }}
                                                            @if($comment->is_employee_comment) <span class="text-xs">(Employee)</span> @endif
                                                        </span>
                                                        <span class="flex items-center gap-1">
                                                            @if($isNew) <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200">New</span> @endif
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Inline comment form for this field (uses fetch to avoid nested forms) --}}
                                    @if(!$form->isCompleted())
                                        <div class="mt-2">
                                            <button @click="showForm = !showForm" type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                                <span x-show="!showForm">Add comment</span>
                                                <span x-show="showForm" x-cloak>Cancel</span>
                                            </button>
                                            <div x-show="showForm" x-cloak class="mt-2">
                                                <textarea x-model="body" rows="2" placeholder="Add a comment for this field..." class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                                <p x-show="error" x-text="error" class="mt-1 text-xs text-red-600 dark:text-red-400"></p>
                                                <button type="button" :disabled="submitting"
                                                    @click="
                                                        if (!body.trim()) { error = 'Please enter a comment.'; return; }
                                                        submitting = true;
                                                        error = '';
                                                        fetch('{{ route('forms.comments.store', $form) }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'Content-Type': 'application/json',
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Accept': 'application/json'
                                                            },
                                                            body: JSON.stringify({
                                                                body: body,
                                                                input_field_template_id: '{{ $template->id }}'
                                                            })
                                                        })
                                                        .then(r => {
                                                            if (r.ok) { window.location.reload(); }
                                                            else { error = 'Failed to add comment.'; submitting = false; }
                                                        })
                                                        .catch(() => { error = 'Failed to add comment.'; submitting = false; });
                                                    "
                                                    class="mt-1 px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-xs disabled:opacity-50">
                                                    <span x-show="!submitting">Add Comment</span>
                                                    <span x-show="submitting">Saving...</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                <div class="flex justify-between items-center border-t border-gray-200 dark:border-gray-700 pt-4">
                    <a href="{{ route('forms.show', $form) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
