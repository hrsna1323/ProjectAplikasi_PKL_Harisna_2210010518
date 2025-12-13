<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action_type',
        'detail',
        'old_value',
        'new_value',
    ];

    /**
     * Action type constants.
     */
    public const ACTION_CONTENT_CREATED = 'content_created';
    public const ACTION_CONTENT_UPDATED = 'content_updated';
    public const ACTION_CONTENT_VERIFIED = 'content_verified';
    public const ACTION_SKPD_CREATED = 'skpd_created';
    public const ACTION_SKPD_UPDATED = 'skpd_updated';
    public const ACTION_SKPD_DELETED = 'skpd_deleted';
    public const ACTION_USER_CREATED = 'user_created';
    public const ACTION_USER_UPDATED = 'user_updated';
    public const ACTION_USER_LOGIN = 'user_login';
    public const ACTION_USER_LOGOUT = 'user_logout';
    public const ACTION_REPORT_EXPORTED = 'report_exported';
    public const ACTION_KATEGORI_CREATED = 'kategori_created';
    public const ACTION_KATEGORI_UPDATED = 'kategori_updated';
    public const ACTION_KATEGORI_TOGGLED = 'kategori_toggled';

    /**
     * Get the user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by action type.
     */
    public function scopeOfType(Builder $query, string $actionType): Builder
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope a query to filter by period (date range).
     */
    public function scopeInPeriod(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by month and year.
     */
    public function scopeInMonth(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('created_at', $month)
                     ->whereYear('created_at', $year);
    }
}
