<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function forms(Request $request)
    {
        $user = Auth::user();
        $excludedStatuses = $user->isManagerOrAdmin() ? ['deleted'] : ['draft', 'deleted'];

        $query = Form::forEmployee($user)
            ->whereNotIn('status', $excludedStatuses)
            ->with('formTemplate', 'user', 'assignedEmployees');

        $allowedStatuses = $user->isManagerOrAdmin()
            ? ['draft', 'submitted', 'corrections', 'accepted', 'declined']
            : ['submitted', 'corrections', 'accepted', 'declined'];

        if ($request->filled('status') && in_array($request->status, $allowedStatuses)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('template')) {
            $query->where('form_template_id', $request->template);
        }

        if ($request->filled('creator')) {
            $query->where('user_id', $request->creator);
        }

        if ($request->filled('assigned')) {
            $query->whereHas('assignedEmployees', fn($q) => $q->where('employee_id', $request->assigned));
        }

        if ($request->filled('submitted_from')) {
            $query->whereDate('submitted_at', '>=', $request->submitted_from);
        }
        if ($request->filled('submitted_to')) {
            $query->whereDate('submitted_at', '<=', $request->submitted_to);
        }

        if ($request->filled('updated_from')) {
            $query->whereDate('updated_at', '>=', $request->updated_from);
        }
        if ($request->filled('updated_to')) {
            $query->whereDate('updated_at', '<=', $request->updated_to);
        }

        $sortField = 'updated_at';
        $sortDir = 'desc';
        if ($request->filled('sort') && in_array($request->sort, ['submitted_at', 'updated_at'])) {
            $sortField = $request->sort;
        }
        if ($request->filled('direction') && in_array($request->direction, ['asc', 'desc'])) {
            $sortDir = $request->direction;
        }
        $query->orderBy($sortField, $sortDir);

        $forms = $query->paginate(15)->withQueryString();

        $templates = FormTemplate::orderBy('name')->get();
        $creatorsQuery = $user->isManagerOrAdmin()
            ? fn($q) => $q->where('status', '!=', 'deleted')
            : fn($q) => $q->whereNotIn('status', ['draft', 'deleted']);
        $creators = User::whereHas('forms', $creatorsQuery)->orderBy('name')->get();
        $employees = User::whereIn('role', ['employee_base', 'employee_manager', 'employee_admin'])->orderBy('name')->get();

        return view('employee.forms', compact('forms', 'templates', 'creators', 'employees'));
    }

    public function submittedForms(Request $request)
    {
        $query = Form::forEmployee(Auth::user())
            ->where('status', 'submitted')
            ->with('formTemplate', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        $forms = $query->latest()->paginate(15);
        return view('employee.submitted-forms', compact('forms'));
    }

    public function correctionsRequested(Request $request)
    {
        $query = Form::forEmployee(Auth::user())
            ->where('status', 'corrections')
            ->with('formTemplate', 'user');

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $forms = $query->latest()->paginate(15);
        return view('employee.corrections-requested', compact('forms'));
    }

    public function completedForms(Request $request)
    {
        $query = Form::forEmployee(Auth::user())
            ->whereIn('status', ['accepted', 'declined'])
            ->with('formTemplate', 'user');

        if ($request->filled('status') && in_array($request->status, ['accepted', 'declined'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $forms = $query->latest()->paginate(15);
        return view('employee.completed-forms', compact('forms'));
    }

    public function deletedForms(Request $request)
    {
        abort_unless(Auth::user()->isManagerOrAdmin(), 403);

        $query = Form::where('status', 'deleted')
            ->with('formTemplate', 'user');

        if ($request->filled('previous_status') && in_array($request->previous_status, ['draft', 'submitted', 'accepted', 'declined', 'corrections'])) {
            $query->where('previous_status', $request->previous_status);
        }

        if ($request->filled('template')) {
            $query->where('form_template_id', $request->template);
        }

        if ($request->filled('creator')) {
            $query->where('user_id', $request->creator);
        }

        if ($request->filled('updated_from')) {
            $query->whereDate('updated_at', '>=', $request->updated_from);
        }
        if ($request->filled('updated_to')) {
            $query->whereDate('updated_at', '<=', $request->updated_to);
        }

        $sortField = 'updated_at';
        $sortDir = 'desc';
        if ($request->filled('sort') && in_array($request->sort, ['updated_at', 'created_at'])) {
            $sortField = $request->sort;
        }
        if ($request->filled('direction') && in_array($request->direction, ['asc', 'desc'])) {
            $sortDir = $request->direction;
        }
        $query->orderBy($sortField, $sortDir);

        $forms = $query->paginate(15)->withQueryString();

        $templates = FormTemplate::orderBy('name')->get();
        $creators = User::whereHas('forms', fn($q) => $q->where('status', 'deleted'))->orderBy('name')->get();

        return view('employee.deleted-forms', compact('forms', 'templates', 'creators'));
    }
}
