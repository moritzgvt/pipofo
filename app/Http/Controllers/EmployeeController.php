<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
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
}
