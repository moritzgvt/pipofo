<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InputFieldTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['form_template_id', 'label', 'type', 'required', 'order', 'options', 'placeholder', 'help_text'];
    protected $casts = ['required' => 'boolean', 'options' => 'array'];

    public function formTemplate(): BelongsTo { return $this->belongsTo(FormTemplate::class); }
    public function formFields(): HasMany { return $this->hasMany(FormField::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
}
