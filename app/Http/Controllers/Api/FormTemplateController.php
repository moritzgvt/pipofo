<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FormTemplate;
use App\Models\InputFieldTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormTemplateController extends Controller
{
    /**
     * List the active form templates available to a requester.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $templates = FormTemplate::active()
            ->withCount('inputFieldTemplates')
            ->orderBy('name')
            ->paginate($perPage);

        $templates->getCollection()->transform(fn ($template) => $this->serializeTemplate($template));

        return response()->json($templates);
    }

    /**
     * Show a single active form template, including its input fields.
     */
    public function show(FormTemplate $formTemplate): JsonResponse
    {
        abort_if(!$formTemplate->is_active, 404);
        $formTemplate->load('inputFieldTemplates');

        return response()->json([
            'data' => $this->serializeTemplate($formTemplate, includeFields: true),
        ]);
    }

    private function serializeTemplate(FormTemplate $template, bool $includeFields = false): array
    {
        $payload = [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'is_active' => (bool) $template->is_active,
            'input_field_templates_count' => $template->input_field_templates_count
                ?? $template->inputFieldTemplates()->count(),
        ];

        if ($includeFields) {
            $payload['input_field_templates'] = $template->inputFieldTemplates
                ->map(fn (InputFieldTemplate $field) => [
                    'id' => $field->id,
                    'label' => $field->label,
                    'type' => $field->type,
                    'required' => (bool) $field->required,
                    'order' => $field->order,
                    'options' => $field->options,
                    'placeholder' => $field->placeholder,
                    'help_text' => $field->help_text,
                ])
                ->values();
        }

        return $payload;
    }
}
