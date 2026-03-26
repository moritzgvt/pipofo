<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model
{
    use HasFactory;

    protected $fillable = ['form_id', 'user_id', 'diff', 'message'];
    protected $casts = ['diff' => 'array'];

    public function form(): BelongsTo { return $this->belongsTo(Form::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
