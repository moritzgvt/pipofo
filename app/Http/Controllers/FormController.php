<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\FormField;
use App\Models\FormView;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    public function create(FormTemplate $formTemplate)
    {
        abort_if(!$formTemplate->is_active, 403);
        $formTemplate->load('inputFieldTemplates');
        return view('forms.create', compact('formTemplate'));
    }

    public function store(Request $request, FormTemplate $formTemplate)
    {
        abort_if(!$formTemplate->is_active, 403);
        $request->validate(['title' => 'required|string|max:255']);

        $form = Form::create([
            'form_template_id' => $formTemplate->id,
            'user_id' => Auth::id(),
            'title' => $request->title,
            'status' => 'draft',
        ]);

        foreach ($formTemplate->inputFieldTemplates as $fieldTemplate) {
            FormField::create([
                'form_id' => $form->id,
                'input_field_template_id' => $fieldTemplate->id,
                'value' => null,
            ]);
        }

        return redirect()->route('forms.edit', $form)->with('success', 'Form created successfully.');
    }

    public function show(Form $form)
    {
        $this->authorizeView($form);
        $form->load('fields.inputFieldTemplate', 'comments.user', 'comments.inputFieldTemplate', 'revisions.user', 'formTemplate', 'user', 'assignedEmployees');
        $baseEmployees = Auth::user()->isManagerOrAdmin()
            ? User::where('role', 'employee_base')->get()
            : collect();

        $lastViewedAt = $this->recordFormView($form);

        return view('forms.show', compact('form', 'baseEmployees', 'lastViewedAt'));
    }

    public function edit(Form $form)
    {
        $this->authorizeEdit($form);
        $form->load('fields.inputFieldTemplate', 'formTemplate', 'comments.user', 'comments.inputFieldTemplate');

        $lastViewedAt = $this->recordFormView($form);

        return view('forms.edit', compact('form', 'lastViewedAt'));
    }

    public function update(Request $request, Form $form)
    {
        $this->authorizeEdit($form);
        $form->load('fields');

        $diff = [];
        foreach ($form->fields as $field) {
            $newValue = $request->input('fields.' . $field->input_field_template_id, '');
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
                'message' => $request->input('revision_message'),
            ]);
        }

        return redirect()->route('forms.show', $form)->with('success', 'Form updated.');
    }

    public function submit(Form $form)
    {
        abort_unless(
            $form->user_id === Auth::id() && ($form->isDraft() || $form->isCorrections()),
            403
        );

        $form->load('fields.inputFieldTemplate');
        $missingFieldEntries = $form->fields
            ->filter(fn($field) => $field->inputFieldTemplate->required && ($field->value === null || trim($field->value) === ''));
        $missingFields = $missingFieldEntries->map(fn($field) => $field->inputFieldTemplate->label);

        if ($missingFields->isNotEmpty()) {
            $missingFieldIds = $missingFieldEntries->pluck('input_field_template_id')->toArray();

            return back()
                ->withErrors(['fields' => 'Please fill in all required fields: ' . $missingFields->implode(', ')])
                ->with('missing_fields', $missingFieldIds);
        }

        $form->update(['status' => 'submitted', 'submitted_at' => now()]);
        return redirect()->route('forms.show', $form)->with('success', 'Form submitted successfully.');
    }

    public function accept(Form $form)
    {
        abort_unless(Auth::user()->isEmployee() && $form->isSubmitted(), 403);
        $this->authorizeEmployeeAccess($form);
        $form->update(['status' => 'accepted', 'completed_at' => now()]);
        return redirect()->route('forms.show', $form)->with('success', 'Form accepted.');
    }

    public function decline(Form $form)
    {
        abort_unless(Auth::user()->isEmployee() && $form->isSubmitted(), 403);
        $this->authorizeEmployeeAccess($form);
        $form->update(['status' => 'declined', 'completed_at' => now()]);
        return redirect()->route('forms.show', $form)->with('success', 'Form declined.');
    }

    public function requestCorrections(Request $request, Form $form)
    {
        abort_unless(Auth::user()->isEmployee() && $form->isSubmitted(), 403);
        $this->authorizeEmployeeAccess($form);
        $request->validate(['comment' => 'required|string']);

        $form->update(['status' => 'corrections']);
        $form->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->comment,
            'is_employee_comment' => true,
        ]);

        return redirect()->route('forms.show', $form)->with('success', 'Corrections requested.');
    }

    public function assignEmployee(Request $request, Form $form)
    {
        abort_unless(Auth::user()->isManagerOrAdmin(), 403);
        $request->validate(['employee_id' => 'required|exists:users,id']);
        $employee = User::findOrFail($request->employee_id);
        abort_unless($employee->isEmployeeBase(), 403);
        $form->assignedEmployees()->syncWithoutDetaching([$request->employee_id]);
        return back()->with('success', 'Employee assigned.');
    }

    public function removeEmployee(Request $request, Form $form)
    {
        abort_unless(Auth::user()->isManagerOrAdmin(), 403);
        $form->assignedEmployees()->detach($request->employee_id);
        return back()->with('success', 'Employee removed.');
    }

    public function destroy(Form $form)
    {
        abort_unless(Auth::user()->isManagerOrAdmin(), 403);
        $form->delete();
        return redirect()->route('dashboard')->with('success', 'Form deleted.');
    }

    public function revisions(Form $form)
    {
        $this->authorizeView($form);
        $revisions = $form->revisions()->with('user')->paginate(20);
        $fieldTemplates = $form->formTemplate->inputFieldTemplates->keyBy('id');
        return view('forms.revisions', compact('form', 'revisions', 'fieldTemplates'));
    }

    private function authorizeView(Form $form): void
    {
        $user = Auth::user();
        if ($form->user_id === $user->id) {
            return; // Form owners can always view their own forms
        }
        if ($user->isEmployeeBase()) {
            abort_unless(
                !$form->isDraft() && $form->assignedEmployees->contains('id', $user->id),
                403
            );
        } elseif (!$user->isManagerOrAdmin()) {
            abort(403);
        }
    }

    private function authorizeEdit(Form $form): void
    {
        $user = Auth::user();
        if ($form->user_id === $user->id && $form->isEditable()) {
            return; // Form owners can edit their own draft/corrections forms
        }
        if ($user->isEmployee()) {
            abort_unless($form->isSubmitted(), 403);
            $this->authorizeEmployeeAccess($form);
        } else {
            abort(403);
        }
    }

    private function authorizeEmployeeAccess(Form $form): void
    {
        $user = Auth::user();
        if ($user->isEmployeeBase()) {
            abort_unless($form->assignedEmployees->contains('id', $user->id), 403);
        }
    }

    private function recordFormView(Form $form): ?\Carbon\Carbon
    {
        $view = FormView::where('form_id', $form->id)
            ->where('user_id', Auth::id())
            ->first();

        $lastViewedAt = $view?->last_viewed_at;

        FormView::updateOrCreate(
            ['form_id' => $form->id, 'user_id' => Auth::id()],
            ['last_viewed_at' => now()]
        );

        return $lastViewedAt;
    }
}
