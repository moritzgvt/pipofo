<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Form $form)
    {
        $request->validate([
            'body' => 'required|string',
            'input_field_template_id' => 'nullable|exists:input_field_templates,id',
        ]);

        $user = Auth::user();

        if ($user->isRequester()) {
            abort_unless($form->user_id === $user->id, 403);
        } elseif ($user->isEmployeeBase()) {
            abort_unless(
                !$form->isDraft() && $form->assignedEmployees->contains('id', $user->id),
                403
            );
        }

        Comment::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'input_field_template_id' => $request->input_field_template_id,
            'body' => $request->body,
            'is_employee_comment' => $user->isEmployee(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Comment added.']);
        }

        return back()->with('success', 'Comment added.');
    }
}
