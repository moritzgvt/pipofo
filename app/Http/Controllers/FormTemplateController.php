<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormTemplateController extends Controller
{
    public function index()
    {
        $templates = FormTemplate::with('creator')->withCount('forms')->latest()->paginate(15);
        return view('form-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('form-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,textarea,number,date,select,checkbox,radio',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.help_text' => 'nullable|string',
            'fields.*.options' => 'nullable|string',
        ]);

        $template = FormTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => Auth::id(),
        ]);

        foreach ($request->fields as $index => $fieldData) {
            $options = null;
            if (!empty($fieldData['options'])) {
                $options = array_map('trim', explode(',', $fieldData['options']));
            }

            InputFieldTemplate::create([
                'form_template_id' => $template->id,
                'label' => $fieldData['label'],
                'type' => $fieldData['type'],
                'required' => !empty($fieldData['required']),
                'order' => $index,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'help_text' => $fieldData['help_text'] ?? null,
                'options' => $options,
            ]);
        }

        return redirect()->route('form-templates.index')->with('success', 'Form template created.');
    }

    public function edit(FormTemplate $formTemplate)
    {
        $formTemplate->load('inputFieldTemplates');
        return view('form-templates.edit', compact('formTemplate'));
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'fields' => 'required|array|min:1',
            'fields.*.id' => 'nullable|exists:input_field_templates,id',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,textarea,number,date,select,checkbox,radio',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.help_text' => 'nullable|string',
            'fields.*.options' => 'nullable|string',
        ]);

        $formTemplate->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $existingIds = [];
        foreach ($request->fields as $index => $fieldData) {
            $options = null;
            if (!empty($fieldData['options'])) {
                $options = array_map('trim', explode(',', $fieldData['options']));
            }

            $data = [
                'form_template_id' => $formTemplate->id,
                'label' => $fieldData['label'],
                'type' => $fieldData['type'],
                'required' => !empty($fieldData['required']),
                'order' => $index,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'help_text' => $fieldData['help_text'] ?? null,
                'options' => $options,
            ];

            if (!empty($fieldData['id'])) {
                $field = InputFieldTemplate::findOrFail($fieldData['id']);
                $field->update($data);
                $existingIds[] = $field->id;
            } else {
                $field = InputFieldTemplate::create($data);
                $existingIds[] = $field->id;
            }
        }

        $formTemplate->inputFieldTemplates()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('form-templates.index')->with('success', 'Template updated.');
    }

    public function destroy(FormTemplate $formTemplate)
    {
        $formTemplate->delete();
        return redirect()->route('form-templates.index')->with('success', 'Template deleted.');
    }
}
