<?php
// In app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute; // 1. IMPORT Attribute class
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
    ];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_admin']; // 2. APPEND the new attribute

    // ... other properties

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The tasks that are assigned to the user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user');
    }

    /**
     * 3. NEW: Determine if the user is an administrator.
     *
     * This is an "Attribute Accessor". It creates a virtual `is_admin` property
     * on the User model.
     */
    protected function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->roles()->where('slug', 'admin')->exists(),
        );
    }
}
