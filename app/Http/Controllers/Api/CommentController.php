<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Add a comment to a form owned by the authenticated user.
     */
    public function store(Request $request, Form $form): JsonResponse
    {
        abort_unless($form->user_id === Auth::id(), 403);
        abort_if($form->isDeleted(), 404);

        $data = $request->validate([
            'body' => ['required', 'string'],
            'input_field_template_id' => ['nullable', 'exists:input_field_templates,id'],
        ]);

        $comment = Comment::create([
            'form_id' => $form->id,
            'user_id' => Auth::id(),
            'input_field_template_id' => $data['input_field_template_id'] ?? null,
            'body' => $data['body'],
            'is_employee_comment' => false,
        ]);

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'input_field_template_id' => $comment->input_field_template_id,
                'is_employee_comment' => false,
                'created_at' => optional($comment->created_at)->toIso8601String(),
            ],
        ], 201);
    }
}
