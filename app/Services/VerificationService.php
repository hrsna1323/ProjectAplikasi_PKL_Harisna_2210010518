<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Approve content and create verification record.
     * Changes status to "Approved" and creates a Verification record.
     *
     * @param Content $content The content to approve
     * @param User $operator The operator performing the verification
     * @param string|null $reason Optional reason for approval
     * @return Verification The created verification record
     * @throws \InvalidArgumentException If content is not in Pending status
     */
    public function approveContent(Content $content, User $operator, ?string $reason = null): Verification
    {
        if (!$content->isPending()) {
            throw new \InvalidArgumentException('Only pending content can be approved.');
        }

        return DB::transaction(function () use ($content, $operator, $reason) {
            // Update content status to Approved
            $content->update(['status' => Content::STATUS_APPROVED]);

            // Create verification record
            $verification = Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => Verification::STATUS_APPROVED,
                'alasan' => $reason,
                'verified_at' => now(),
            ]);

            // Send notification to Publisher (Requirements 7.2)
            $this->notificationService->sendContentVerifiedNotification($content, $verification);

            return $verification;
        });
    }

    /**
     * Reject content and create verification record.
     * Changes status to "Rejected" with mandatory reason.
     *
     * @param Content $content The content to reject
     * @param User $operator The operator performing the verification
     * @param string $reason Mandatory reason for rejection
     * @return Verification The created verification record
     * @throws \InvalidArgumentException If content is not in Pending status or reason is empty
     */
    public function rejectContent(Content $content, User $operator, string $reason): Verification
    {
        if (!$content->isPending()) {
            throw new \InvalidArgumentException('Only pending content can be rejected.');
        }

        if (empty(trim($reason))) {
            throw new \InvalidArgumentException('Reason is required for rejection.');
        }

        return DB::transaction(function () use ($content, $operator, $reason) {
            // Update content status to Rejected
            $content->update(['status' => Content::STATUS_REJECTED]);

            // Create verification record
            $verification = Verification::create([
                'content_id' => $content->id,
                'verifikator_id' => $operator->id,
                'status' => Verification::STATUS_REJECTED,
                'alasan' => $reason,
                'verified_at' => now(),
            ]);

            // Send notification to Publisher (Requirements 7.2)
            $this->notificationService->sendContentVerifiedNotification($content, $verification);

            return $verification;
        });
    }

    /**
     * Get list of pending contents for verification.
     *
     * @param array $filters Optional filters (skpd_id, kategori_id, search)
     * @return Collection Collection of pending contents
     */
    public function getPendingContents(array $filters = []): Collection
    {
        $query = Content::with(['skpd', 'publisher', 'kategori'])
            ->pending()
            ->orderBy('created_at', 'asc');

        if (!empty($filters['skpd_id'])) {
            $query->where('skpd_id', $filters['skpd_id']);
        }

        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get verification history/timeline for a content.
     *
     * @param Content $content The content to get history for
     * @return Collection Collection of verification records ordered by date
     */
    public function getVerificationHistory(Content $content): Collection
    {
        return $content->verifications()
            ->with('verifikator')
            ->orderBy('verified_at', 'desc')
            ->get();
    }

    /**
     * Get a single content by ID with all related data for review.
     *
     * @param int $contentId The content ID
     * @return Content|null The content with relationships loaded
     */
    public function getContentForReview(int $contentId): ?Content
    {
        return Content::with(['skpd', 'publisher', 'kategori', 'verifications.verifikator'])
            ->find($contentId);
    }

    /**
     * Get all verification history with filters and pagination.
     * 
     * Requirements: 1.2, 1.3, 1.4
     *
     * @param array $filters Optional filters (start_date, end_date, skpd_id, status, search)
     * @return LengthAwarePaginator Paginated verification records
     */
    public function getAllVerificationHistory(array $filters = []): LengthAwarePaginator
    {
        $query = Verification::with([
            'content',
            'content.skpd',
            'content.kategori',
            'verifikator'
        ]);

        // Apply date range filter
        if (!empty($filters['start_date'])) {
            $query->whereDate('verified_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('verified_at', '<=', $filters['end_date']);
        }

        // Apply SKPD filter
        if (!empty($filters['skpd_id'])) {
            $query->whereHas('content', function ($q) use ($filters) {
                $q->where('skpd_id', $filters['skpd_id']);
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply search filter on content title
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('content', function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%");
            });
        }

        // Order by most recent first
        $query->orderBy('verified_at', 'desc');

        // Return paginated results (20 per page)
        return $query->paginate(20);
    }
}
