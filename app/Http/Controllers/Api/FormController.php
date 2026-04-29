<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Revision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FormController extends Controller
{
    /**
     * List the authenticated user's own forms (excluding deleted ones).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $query = Form::where('user_id', Auth::id())
            ->where('status', '!=', 'deleted')
            ->with('formTemplate');

        if ($request->filled('status')) {
            $request->validate([
                'status' => ['in:draft,submitted,accepted,declined,corrections'],
            ]);
            $query->where('status', $request->string('status'));
        }

        $forms = $query->latest()->paginate($perPage);
        $forms->getCollection()->transform(fn (Form $form) => $this->serializeForm($form));

        return response()->json($forms);
    }

    /**
     * Show a single form belonging to the authenticated user.
     */
    public function show(Form $form): JsonResponse
    {
        $this->authorizeOwnership($form, allowDeleted: false);
        $form->load(['formTemplate.inputFieldTemplates', 'fields.inputFieldTemplate', 'comments.user']);

        return response()->json([
            'data' => $this->serializeForm($form, detailed: true),
        ]);
    }

    /**
     * Create a new form from an active template.
     */
    public function store(Request $request, FormTemplate $formTemplate): JsonResponse
    {
        abort_if(!$formTemplate->is_active, 403, 'Form template is not active.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'fields' => ['sometimes', 'array'],
            'fields.*' => ['nullable'],
        ]);

        $form = Form::create([
            'form_template_id' => $formTemplate->id,
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'status' => 'draft',
        ]);

        $formTemplate->load('inputFieldTemplates');
        $allowedFieldIds = $formTemplate->inputFieldTemplates->pluck('id')->all();

        foreach ($formTemplate->inputFieldTemplates as $fieldTemplate) {
            $value = null;
            if (isset($data['fields'][$fieldTemplate->id])) {
                $value = $this->normalizeFieldValue($data['fields'][$fieldTemplate->id]);
            }

            FormField::create([
                'form_id' => $form->id,
                'input_field_template_id' => $fieldTemplate->id,
                'value' => $value,
            ]);
        }

        // Reject unknown field ids in the input rather than silently ignoring them.
        if (!empty($data['fields'])) {
            $unknown = array_diff(array_keys($data['fields']), $allowedFieldIds);
            if (!empty($unknown)) {
                throw ValidationException::withMessages([
                    'fields' => ['Unknown field id(s): ' . implode(', ', $unknown)],
                ]);
            }
        }

        $form->load(['formTemplate.inputFieldTemplates', 'fields.inputFieldTemplate']);

        return response()->json([
            'data' => $this->serializeForm($form, detailed: true),
        ], 201);
    }

    /**
     * Update the field values of a form. Only allowed while editable
     * (status `draft` or `corrections`).
     */
    public function update(Request $request, Form $form): JsonResponse
    {
        $this->authorizeOwnership($form);
        abort_unless($form->isEditable(), 403, 'Form is not editable.');

        $data = $request->validate([
            'fields' => ['required', 'array'],
            'fields.*' => ['nullable'],
            'revision_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $form->load('fields');
        $allowedFieldIds = $form->fields->pluck('input_field_template_id')->all();
        $unknown = array_diff(array_keys($data['fields']), $allowedFieldIds);
        if (!empty($unknown)) {
            throw ValidationException::withMessages([
                'fields' => ['Unknown field id(s): ' . implode(', ', $unknown)],
            ]);
        }

        $diff = [];
        foreach ($form->fields as $field) {
            if (!array_key_exists($field->input_field_template_id, $data['fields'])) {
                continue;
            }
            $newValue = $this->normalizeFieldValue($data['fields'][$field->input_field_template_id]);
            if ($field->value !== $newValue) {
                $diff[$field->input_field_template_id] = ['old' => $field->value, 'new' => $newValue];
                $field->update(['value' => $newValue]);
            }
        }

        if (!empty($diff)) {
            Revision::create([
                'form_id' => $form->id,
                'user_id' => Auth::id(),
                'diff' => $diff,
                'message' => $data['revision_message'] ?? null,
            ]);
        }

        $form->load(['formTemplate.inputFieldTemplates', 'fields.inputFieldTemplate']);

        return response()->json([
            'data' => $this->serializeForm($form, detailed: true),
        ]);
    }

    /**
     * Submit a draft or corrections form for review.
     */
    public function submit(Form $form): JsonResponse
    {
        $this->authorizeOwnership($form);
        abort_unless($form->isDraft() || $form->isCorrections(), 403, 'Form cannot be submitted in its current state.');

        $form->load('fields.inputFieldTemplate');
        $missing = $form->fields
            ->filter(fn (FormField $field) => $field->inputFieldTemplate->required
                && ($field->value === null || trim((string) $field->value) === ''));

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'fields' => ['Please fill in all required fields: '
                    . $missing->map(fn (FormField $f) => $f->inputFieldTemplate->label)->implode(', ')],
            ]);
        }

        $form->update(['status' => 'submitted', 'submitted_at' => now()]);

        return response()->json([
            'data' => $this->serializeForm($form->fresh(['formTemplate'])),
            'message' => 'Form submitted successfully.',
        ]);
    }

    /**
     * Soft-delete a draft form belonging to the authenticated user.
     */
    public function destroy(Form $form): JsonResponse
    {
        $this->authorizeOwnership($form);
        abort_unless($form->isDraft(), 403, 'Only draft forms can be deleted.');

        $form->update(['previous_status' => $form->status, 'status' => 'deleted']);

        return response()->json(['message' => 'Form deleted.']);
    }

    /**
     * Authorize that the form belongs to the current user.
     */
    private function authorizeOwnership(Form $form, bool $allowDeleted = false): void
    {
        abort_unless($form->user_id === Auth::id(), 403);
        if (!$allowDeleted) {
            abort_if($form->isDeleted(), 404);
        }
    }

    private function normalizeFieldValue($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return json_encode($value);
        }
        return (string) $value;
    }

    private function serializeForm(Form $form, bool $detailed = false): array
    {
        $payload = [
            'id' => $form->id,
            'title' => $form->title,
            'status' => $form->status,
            'status_label' => $form->status_label,
            'form_template' => $form->relationLoaded('formTemplate') && $form->formTemplate ? [
                'id' => $form->formTemplate->id,
                'name' => $form->formTemplate->name,
            ] : null,
            'submitted_at' => optional($form->submitted_at)->toIso8601String(),
            'completed_at' => optional($form->completed_at)->toIso8601String(),
            'created_at' => optional($form->created_at)->toIso8601String(),
            'updated_at' => optional($form->updated_at)->toIso8601String(),
        ];

        if ($detailed) {
            $payload['fields'] = $form->fields
                ->map(fn (FormField $field) => [
                    'id' => $field->id,
                    'input_field_template_id' => $field->input_field_template_id,
                    'label' => $field->inputFieldTemplate?->label,
                    'type' => $field->inputFieldTemplate?->type,
                    'required' => (bool) ($field->inputFieldTemplate?->required),
                    'value' => $field->value,
                ])
                ->values();

            if ($form->relationLoaded('comments')) {
                $payload['comments'] = $form->comments->map(fn (Comment $comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'input_field_template_id' => $comment->input_field_template_id,
                    'is_employee_comment' => (bool) $comment->is_employee_comment,
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ] : null,
                    'created_at' => optional($comment->created_at)->toIso8601String(),
                ])->values();
            }
        }

        return $payload;
    }
}
