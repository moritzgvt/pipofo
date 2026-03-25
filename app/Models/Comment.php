<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'user_id', 'input_field_template_id', 'body', 'is_employee_comment'];
    protected $casts = ['is_employee_comment' => 'boolean'];

    public function form(): BelongsTo { return $this->belongsTo(Form::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function inputFieldTemplate(): BelongsTo { return $this->belongsTo(InputFieldTemplate::class); }
}
