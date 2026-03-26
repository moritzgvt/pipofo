<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'created_by', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function inputFieldTemplates(): HasMany { return $this->hasMany(InputFieldTemplate::class)->orderBy('order'); }
    public function forms(): HasMany { return $this->hasMany(Form::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
}
