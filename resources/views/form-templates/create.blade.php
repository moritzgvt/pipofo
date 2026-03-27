<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Create Form Template') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <form method="POST" action="{{ route('form-templates.store') }}" x-data="formTemplateEditor()" class="space-y-6">
                @csrf

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Fields</h3>

                    <div class="space-y-4">
                        <template x-for="(field, index) in fields" :key="index">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden" x-data="{ open: true }">
                                <!-- Card Header -->
                                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/50 cursor-pointer select-none" role="button" tabindex="0" :aria-expanded="open" @click="open = !open" @keydown.enter.prevent="open = !open" @keydown.space.prevent="open = !open">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400 dark:text-gray-500 text-xs transition-transform" :class="open ? 'rotate-90' : ''" aria-hidden="true">&#9654;</span>
                                        <span class="font-medium text-sm text-gray-700 dark:text-gray-300" x-text="field.label || 'Field ' + (index + 1)"></span>
                                        <span class="text-xs text-gray-400 dark:text-gray-500" x-text="'(' + field.type + ')'"></span>
                                    </div>
                                    <div class="flex items-center gap-1" @click.stop>
                                        <button type="button" @click="moveUp(index)" x-show="index > 0" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition text-sm" aria-label="Move field up">&uarr;</button>
                                        <button type="button" @click="moveDown(index)" x-show="index < fields.length - 1" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition text-sm" aria-label="Move field down">&darr;</button>
                                        <button type="button" @click="removeField(index)" x-show="fields.length > 1" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-red-300 dark:border-red-500 bg-white dark:bg-gray-700 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition text-sm" aria-label="Remove field">&times;</button>
                                    </div>
                                </div>

                                <!-- Collapsible Body -->
                                <div x-show="open" x-transition class="p-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label :for="'field_label_' + index" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
                                            <input type="text" :name="'fields[' + index + '][label]'" :id="'field_label_' + index" x-model="field.label" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        </div>
                                        <div>
                                            <label :for="'field_type_' + index" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                            <select :name="'fields[' + index + '][type]'" :id="'field_type_' + index" x-model="field.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                                <option value="text">Text</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="number">Number</option>
                                                <option value="date">Date</option>
                                                <option value="select">Select</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="radio">Radio</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label :for="'field_placeholder_' + index" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Placeholder</label>
                                            <input type="text" :name="'fields[' + index + '][placeholder]'" :id="'field_placeholder_' + index" x-model="field.placeholder" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        </div>
                                        <div>
                                            <label :for="'field_help_' + index" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Help Text</label>
                                            <input type="text" :name="'fields[' + index + '][help_text]'" :id="'field_help_' + index" x-model="field.help_text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center gap-4">
                                        <label class="inline-flex items-center">
                                            <input type="hidden" :name="'fields[' + index + '][required]'" value="0">
                                            <input type="checkbox" :name="'fields[' + index + '][required]'" x-model="field.required" value="1" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Required</span>
                                        </label>
                                    </div>

                                    <div x-show="['select', 'radio'].includes(field.type)" class="mt-3">
                                        <label :for="'field_options_' + index" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Options (comma-separated)</label>
                                        <input type="text" :name="'fields[' + index + '][options]'" :id="'field_options_' + index" x-model="field.options" placeholder="Option 1, Option 2, Option 3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4">
                        <button type="button" @click="addField()" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition">+ Add Field</button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Create Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formTemplateEditor() {
            return {
                fields: [{ label: '', type: 'text', required: false, placeholder: '', help_text: '', options: '' }],
                addField() {
                    this.fields.push({ label: '', type: 'text', required: false, placeholder: '', help_text: '', options: '' });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                },
                moveUp(index) {
                    if (index > 0) {
                        const item = this.fields.splice(index, 1)[0];
                        this.fields.splice(index - 1, 0, item);
                    }
                },
                moveDown(index) {
                    if (index < this.fields.length - 1) {
                        const item = this.fields.splice(index, 1)[0];
                        this.fields.splice(index + 1, 0, item);
                    }
                }
            }
        }
    </script>
</x-app-layout>
