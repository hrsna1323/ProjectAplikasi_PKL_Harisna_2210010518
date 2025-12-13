<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'konten';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'skpd_id',
        'publisher_id',
        'judul',
        'deskripsi',
        'kategori_id',
        'url_publikasi',
        'tanggal_publikasi',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_publikasi' => 'date',
        ];
    }

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'Pending',
    ];

    /**
     * Status enum values.
     */
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_PUBLISHED = 'Published';

    /**
     * Get all valid status values.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_PUBLISHED,
        ];
    }

    /**
     * Get the SKPD that owns this content.
     */
    public function skpd(): BelongsTo
    {
        return $this->belongsTo(Skpd::class);
    }

    /**
     * Get the publisher (user) who created this content.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    /**
     * Get the category of this content.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriKonten::class, 'kategori_id');
    }

    /**
     * Get the verifications for this content.
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'content_id');
    }

    /**
     * Scope a query to only include pending content.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include approved content.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope a query to only include content from a specific SKPD.
     */
    public function scopeForSkpd($query, int $skpdId)
    {
        return $query->where('skpd_id', $skpdId);
    }

    /**
     * Check if content is pending verification.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if content is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if content can be edited (only Draft or Rejected).
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }
}
