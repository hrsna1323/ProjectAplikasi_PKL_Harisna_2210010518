<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'skpd_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the SKPD that the user belongs to.
     */
    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class);
    }

    /**
     * Get the contents created by this user (as publisher).
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'publisher_id');
    }

    /**
     * Get the verifications made by this user (as operator).
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'verifikator_id');
    }

    /**
     * Get the notifications for this user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the activity logs for this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is a Publisher.
     */
    public function isPublisher(): bool
    {
        return $this->hasRole('Publisher');
    }

    /**
     * Check if user is an Operator.
     */
    public function isOperator(): bool
    {
        return $this->hasRole('Operator');
    }

    /**
     * Check if user is an Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }
}
