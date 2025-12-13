<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skpd extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skpd';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_skpd',
        'website_url',
        'email',
        'kuota_bulanan',
        'status',
        'server_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kuota_bulanan' => 'integer',
        ];
    }

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'kuota_bulanan' => 3,
        'status' => 'Active',
    ];

    /**
     * Get the contents for this SKPD.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * Get the publishers (users) for this SKPD.
     */
    public function publishers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'Publisher');
    }

    /**
     * Get all users associated with this SKPD.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the server location for this SKPD.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(LokasiServer::class, 'server_id');
    }

    /**
     * Scope a query to only include active SKPDs.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Check if SKPD has active content.
     */
    public function hasActiveContent(): bool
    {
        return $this->contents()
            ->whereIn('status', ['Pending', 'Approved', 'Published'])
            ->exists();
    }
}
