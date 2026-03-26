<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Form extends Model
{
    use HasFactory;

    protected $fillable = ['form_template_id', 'user_id', 'title', 'status', 'submitted_at', 'completed_at'];
    protected $casts = ['submitted_at' => 'datetime', 'completed_at' => 'datetime'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function formTemplate(): BelongsTo { return $this->belongsTo(FormTemplate::class); }
    public function fields(): HasMany { return $this->hasMany(FormField::class); }
    public function comments(): HasMany { return $this->hasMany(Comment::class)->latest(); }
    public function revisions(): HasMany { return $this->hasMany(Revision::class)->latest(); }
    public function assignedEmployees(): BelongsToMany { return $this->belongsToMany(User::class, 'form_employees', 'form_id', 'employee_id'); }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isAccepted(): bool { return $this->status === 'accepted'; }
    public function isDeclined(): bool { return $this->status === 'declined'; }
    public function isCorrections(): bool { return $this->status === 'corrections'; }
    public function isCompleted(): bool { return in_array($this->status, ['accepted', 'declined']); }
    public function isEditable(): bool { return in_array($this->status, ['draft', 'corrections']); }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            'submitted' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'accepted' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'declined' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'corrections' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'corrections' => 'Corrections Requested',
            default => ucfirst($this->status),
        };
    }

    public function scopeForEmployee($query, User $user)
    {
        if ($user->isManagerOrAdmin()) {
            return $query;
        }
        return $query->whereHas('assignedEmployees', fn($q) => $q->where('employee_id', $user->id));
    }
}
