<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit') }}: {{ $form->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            @if($form->comments->where('is_employee_comment', true)->isNotEmpty())
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <h3 class="font-medium text-yellow-800 dark:text-yellow-200 mb-2">Employee Feedback</h3>
                    @foreach($form->comments->where('is_employee_comment', true)->take(3) as $comment)
                        <div class="mb-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <strong>{{ $comment->user->name }}:</strong>
                            {{ $comment->body }}
                            @if($comment->inputFieldTemplate)
                                <span class="text-xs">(re: {{ $comment->inputFieldTemplate->label }})</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('forms.update', $form) }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                @csrf
                @method('PUT')

                @foreach($form->fields->sortBy(fn($f) => $f->inputFieldTemplate->order) as $field)
                    @php $template = $field->inputFieldTemplate; @endphp
                    <div class="mb-6">
                        <label for="field_{{ $template->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $template->label }}
                            @if($template->required) <span class="text-red-500">*</span> @endif
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
