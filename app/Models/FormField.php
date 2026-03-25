<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'input_field_template_id', 'value'];

    public function form(): BelongsTo { return $this->belongsTo(Form::class); }
    public function inputFieldTemplate(): BelongsTo { return $this->belongsTo(InputFieldTemplate::class); }
}
