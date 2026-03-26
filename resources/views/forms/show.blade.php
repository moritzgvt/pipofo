<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $form->title }}</h2>
            <x-status-badge :status="$form->status" />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            {{-- Form Meta --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Template</span>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $form->formTemplate->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Requester</span>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $form->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Created</span>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $form->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Last Updated</span>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $form->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-2 mb-6">
                @if(auth()->user()->isRequester() && $form->isEditable() && $form->user_id === auth()->id())
                    <a href="{{ route('forms.edit', $form) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Edit</a>
                    <form method="POST" action="{{ route('forms.submit', $form) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Submit this form?')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm">Submit</button>
                    </form>
                @endif

                @if(auth()->user()->isEmployee() && $form->isSubmitted())
                    <a href="{{ route('forms.edit', $form) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Edit Fields</a>
                    <form method="POST" action="{{ route('employee.forms.accept', $form) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Accept this form?')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-sm">Accept</button>
                    </form>
                    <form method="POST" action="{{ route('employee.forms.decline', $form) }}" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Decline this form?')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition text-sm">Decline</button>
                    </form>
                @endif

                <a href="{{ route('forms.revisions', $form) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm">History ({{ $form->revisions->count() }})</a>
            </div>

            {{-- Request Corrections (Employee) --}}
            @if(auth()->user()->isEmployee() && $form->isSubmitted())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6" x-data="{ open: false }">
                    <button @click="open = !open" class="text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:underline">Request Corrections &darr;</button>
                    <form method="POST" action="{{ route('employee.forms.request-corrections', $form) }}" x-show="open" x-cloak class="mt-4">
                        @csrf
                        <textarea name="comment" required rows="3" placeholder="Describe the corrections needed..." class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        <button type="submit" class="mt-2 px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition text-sm">Send Correction Request</button>
                    </form>
                </div>
            @endif

            {{-- Assign Employee (Manager/Admin) --}}
            @if(auth()->user()->isManagerOrAdmin() && $baseEmployees->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Assigned Employees</h3>
                    @if($form->assignedEmployees->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach($form->assignedEmployees as $emp)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    {{ $emp->name }}
                                    <form method="POST" action="{{ route('forms.remove-employee', $form) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="employee_id" value="{{ $emp->id }}">
                                        <button type="submit" class="text-red-500 hover:text-red-700 ml-1" aria-label="Remove {{ $emp->name }}">&times;</button>
                                    </form>
                                </span>
                            @endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('forms.assign-employee', $form) }}" class="flex gap-2">
                        @csrf
                        <select name="employee_id" required class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm text-sm" aria-label="Select employee">
                            <option value="">Select employee...</option>
                            @foreach($baseEmployees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Assign</button>
                    </form>
                </div>
            @endif

            {{-- Form Fields --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Form Data</h3>
                @foreach($form->fields->sortBy(fn($f) => $f->inputFieldTemplate->order) as $field)
                    @php
                        $fieldComments = $form->comments->where('input_field_template_id', $field->inputFieldTemplate->id);
                        $hasNewComments = $lastViewedAt && $fieldComments->contains(fn($c) => $c->created_at->gt($lastViewedAt));
                    @endphp
                    <div class="mb-4 pb-4 border-b border-gray-100 dark:border-gray-700 last:border-0 {{ $hasNewComments ? 'ring-2 ring-yellow-400 dark:ring-yellow-500 rounded-lg p-3 bg-yellow-50/30 dark:bg-yellow-900/10' : '' }}">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ $field->inputFieldTemplate->label }}
                            @if($field->inputFieldTemplate->required) <span class="text-red-500">*</span> @endif
                            @if($hasNewComments) <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200">New</span> @endif
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $field->value ?: '—' }}
                        </dd>

                        {{-- Field-specific comments (collapsible, visually distinct) --}}
                        @if($fieldComments->isNotEmpty() || !$form->isCompleted())
                            <div x-data="{ expanded: {{ $fieldComments->isNotEmpty() ? 'true' : 'false' }} }" class="mt-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                                <button @click="expanded = !expanded" type="button" class="flex items-center gap-1 text-xs font-medium text-amber-800 dark:text-amber-200 hover:underline">
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

                                    {{-- Inline comment form for this field --}}
                                    @if(!$form->isCompleted())
                                        <div x-data="{ showForm: false }" class="mt-2">
                                            <button @click="showForm = !showForm" type="button" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                                <span x-show="!showForm">Add comment</span>
                                                <span x-show="showForm" x-cloak>Cancel</span>
                                            </button>
                                            <form method="POST" action="{{ route('forms.comments.store', $form) }}" x-show="showForm" x-cloak class="mt-2">
                                                @csrf
                                                <input type="hidden" name="input_field_template_id" value="{{ $field->inputFieldTemplate->id }}">
                                                <textarea name="body" required rows="2" placeholder="Add a comment for this field..." class="w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                                <button type="submit" class="mt-1 px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-xs">Add Comment</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- General Comments --}}
            @php $generalComments = $form->comments->whereNull('input_field_template_id'); @endphp
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">General Comments ({{ $generalComments->count() }})</h3>

                @foreach($generalComments as $comment)
                    @php $isNew = $lastViewedAt && $comment->created_at->gt($lastViewedAt); @endphp
                    <div class="mb-4 p-3 rounded-lg {{ $isNew ? 'bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-400 dark:border-yellow-600' : ($comment->is_employee_comment ? 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' : 'bg-gray-50 dark:bg-gray-700/50') }}">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium {{ $comment->is_employee_comment ? 'text-blue-800 dark:text-blue-200' : 'text-gray-800 dark:text-gray-200' }}">
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

                @if(!$form->isCompleted())
                    <form method="POST" action="{{ route('forms.comments.store', $form) }}" class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        @csrf
                        <textarea name="body" required rows="3" placeholder="Add a general comment..." class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        @error('body') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        <button type="submit" class="mt-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition text-sm">Add Comment</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
