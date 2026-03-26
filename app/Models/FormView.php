<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormView extends Model
{
    protected $fillable = ['form_id', 'user_id', 'last_viewed_at'];
    protected $casts = ['last_viewed_at' => 'datetime'];

    public function form(): BelongsTo { return $this->belongsTo(Form::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
