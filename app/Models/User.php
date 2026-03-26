<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isRequester(): bool { return $this->role === 'requester'; }
    public function isEmployee(): bool { return in_array($this->role, ['employee_base', 'employee_manager', 'employee_admin']); }
    public function isEmployeeAdmin(): bool { return $this->role === 'employee_admin'; }
    public function isEmployeeManager(): bool { return $this->role === 'employee_manager'; }
    public function isEmployeeBase(): bool { return $this->role === 'employee_base'; }
    public function isManagerOrAdmin(): bool { return in_array($this->role, ['employee_manager', 'employee_admin']); }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'requester' => 'Requester',
            'employee_base' => 'Employee',
            'employee_manager' => 'Manager',
            'employee_admin' => 'Administrator',
            default => $this->role,
        };
    }

    public function forms(): HasMany { return $this->hasMany(Form::class); }
    public function assignedForms(): BelongsToMany { return $this->belongsToMany(Form::class, 'form_employees', 'employee_id', 'form_id'); }
    public function createdFormTemplates(): HasMany { return $this->hasMany(FormTemplate::class, 'created_by'); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
    public function revisions(): HasMany { return $this->hasMany(Revision::class); }
}
