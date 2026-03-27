<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isRequester()) {
            $myForms = Form::where('user_id', $user->id)->where('status', '!=', 'deleted')->latest()->take(5)->get();
            $corrections = Form::where('user_id', $user->id)->where('status', 'corrections')->count();
            $templates = \App\Models\FormTemplate::active()->count();
            return view('requester.dashboard', compact('myForms', 'corrections', 'templates'));
        }

        $query = Form::forEmployee($user)->whereNotIn('status', ['draft', 'deleted']);
        $submitted = (clone $query)->where('status', 'submitted')->count();
        $correctionsCount = (clone $query)->where('status', 'corrections')->count();
        $completedCount = (clone $query)->whereIn('status', ['accepted', 'declined'])->count();
        $recentForms = (clone $query)->latest()->take(5)->get();

        return view('employee.dashboard', compact('submitted', 'correctionsCount', 'completedCount', 'recentForms'));
    }
}
