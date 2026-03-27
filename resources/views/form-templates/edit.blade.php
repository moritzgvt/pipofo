<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit Template') }}: {{ $formTemplate->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <form method="POST" action="{{ route('form-templates.update', $formTemplate) }}" x-data="formTemplateEditor()" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Template Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $formTemplate->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $formTemplate->description) }}</textarea>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ $formTemplate->is_active ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Active</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Fields</h3>

                    <div class="space-y-4">
                        <template x-for="(field, index) in fields" :key="index">
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 relative">
                                <div class="absolute top-2 right-2 flex items-center gap-1">
                                    <button type="button" @click="moveUp(index)" x-show="index > 0" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1" aria-label="Move field up">&uarr;</button>
                                    <button type="button" @click="moveDown(index)" x-show="index < fields.length - 1" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1" aria-label="Move field down">&darr;</button>
                                    <button type="button" @click="removeField(index)" x-show="fields.length > 1" class="text-red-500 hover:text-red-700 p-1" aria-label="Remove field">&times;</button>
                                </div>
                                <input type="hidden" :name="'fields[' + index + '][id]'" :value="field.id || ''">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
                                        <input type="text" :name="'fields[' + index + '][label]'" x-model="field.label" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                        <select :name="'fields[' + index + '][type]'" x-model="field.type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Placeholder</label>
                                        <input type="text" :name="'fields[' + index + '][placeholder]'" x-model="field.placeholder" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Help Text</label>
                                        <input type="text" :name="'fields[' + index + '][help_text]'" x-model="field.help_text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Options (comma-separated)</label>
                                    <input type="text" :name="'fields[' + index + '][options]'" x-model="field.options" placeholder="Option 1, Option 2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4">
                        <button type="button" @click="addField()" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition">+ Add Field</button>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('form-templates.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        Update Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    @php
        $fieldsJson = $formTemplate->inputFieldTemplates->map(fn($f) => [
            'id' => $f->id,
            'label' => $f->label,
            'type' => $f->type,
            'required' => $f->required,
            'placeholder' => $f->placeholder ?? '',
            'help_text' => $f->help_text ?? '',
            'options' => $f->options ? implode(', ', $f->options) : '',
        ])->values();
    @endphp
    <script>
        function formTemplateEditor() {
            return {
                fields: @json($fieldsJson),
                addField() {
                    this.fields.push({ id: null, label: '', type: 'text', required: false, placeholder: '', help_text: '', options: '' });
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
