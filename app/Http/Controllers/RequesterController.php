<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequesterController extends Controller
{
    public function availableForms()
    {
        $templates = FormTemplate::active()->withCount('inputFieldTemplates')->paginate(15);
        return view('requester.available-forms', compact('templates'));
    }

    public function myForms(Request $request)
    {
        $query = Form::where('user_id', Auth::id())
            ->where('status', '!=', 'deleted')
            ->with('formTemplate');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $forms = $query->latest()->paginate(15);
        return view('requester.my-forms', compact('forms'));
    }

    public function pendingCorrections()
    {
        $forms = Form::where('user_id', Auth::id())
            ->where('status', 'corrections')
            ->with('formTemplate', 'comments.user')
            ->latest()
            ->paginate(15);
        return view('requester.pending-corrections', compact('forms'));
    }
}
