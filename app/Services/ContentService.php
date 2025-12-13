<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Skpd;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ContentService
{
    protected ActivityLogService $activityLogService;
    protected NotificationService $notificationService;

    public function __construct(ActivityLogService $activityLogService, NotificationService $notificationService)
    {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
    }

    public function createContent(array $data, User $publisher): Content
    {
        return DB::transaction(function () use ($data, $publisher) {
            $data['publisher_id'] = $publisher->id;
            $data['skpd_id'] = $publisher->skpd_id;
            $data['status'] = Content::STATUS_PENDING;
            $content = Content::create($data);
            $this->activityLogService->logContentCreated($content, $publisher);
            
            // Send notification to all Operators (Requirements 7.1)
            $this->notificationService->sendContentSubmittedNotification($content);
            
            return $content;
        });
    }

    public function updateContent(Content $content, array $data): Content
    {
        return DB::transaction(function () use ($content, $data) {
            if ($content->status === Content::STATUS_REJECTED) {
                $data['status'] = Content::STATUS_PENDING;
            }
            $content->update($data);
            if (auth()->check()) {
                $this->activityLogService->logUserAction('CONTENT_UPDATED', "Konten diperbarui", auth()->user());
            }
            return $content->fresh();
        });
    }

    public function getContentByPublisher(User $publisher, array $filters = []): Collection
    {
        $query = Content::with(['kategori', 'verifications.verifikator'])
            ->where('skpd_id', $publisher->skpd_id)
            ->orderBy('created_at', 'desc');
        $this->applyFilters($query, $filters);
        return $query->get();
    }

    public function validateUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return false;
        }
        return isset($parsedUrl['host']) && !empty($parsedUrl['host']);
    }

    public function checkQuotaProgress(Skpd $skpd, int $month, int $year): array
    {
        $approvedCount = $skpd->contents()
            ->whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->where('status', Content::STATUS_APPROVED)
            ->count();
        $pendingCount = $skpd->contents()
            ->whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->where('status', Content::STATUS_PENDING)
            ->count();
        $quota = $skpd->kuota_bulanan;
        $remaining = max(0, $quota - $approvedCount);
        $percentage = $quota > 0 ? round(($approvedCount / $quota) * 100, 2) : 0;
        return [
            'quota' => $quota,
            'approved' => $approvedCount,
            'pending' => $pendingCount,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'is_fulfilled' => $approvedCount >= $quota,
        ];
    }

    public function getContentById(int $id): ?Content
    {
        return Content::with(['skpd', 'publisher', 'kategori', 'verifications.verifikator'])->find($id);
    }

    public function getAllContent(array $filters = []): Collection
    {
        $query = Content::with(['skpd', 'publisher', 'kategori'])->orderBy('created_at', 'desc');
        if (isset($filters['skpd_id']) && $filters['skpd_id']) {
            $query->where('skpd_id', $filters['skpd_id']);
        }
        $this->applyFilters($query, $filters);
        return $query->get();
    }

    public function getContentStatsBySkpd(Skpd $skpd): array
    {
        return [
            'total' => $skpd->contents()->count(),
            'draft' => $skpd->contents()->where('status', Content::STATUS_DRAFT)->count(),
            'pending' => $skpd->contents()->where('status', Content::STATUS_PENDING)->count(),
            'approved' => $skpd->contents()->where('status', Content::STATUS_APPROVED)->count(),
            'rejected' => $skpd->contents()->where('status', Content::STATUS_REJECTED)->count(),
            'published' => $skpd->contents()->where('status', Content::STATUS_PUBLISHED)->count(),
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['kategori_id']) && $filters['kategori_id']) {
            $query->where('kategori_id', $filters['kategori_id']);
        }
        if (isset($filters['start_date']) && $filters['start_date']) {
            $query->whereDate('tanggal_publikasi', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date']) && $filters['end_date']) {
            $query->whereDate('tanggal_publikasi', '<=', $filters['end_date']);
        }
        if (isset($filters['month']) && isset($filters['year'])) {
            $query->whereMonth('tanggal_publikasi', $filters['month'])->whereYear('tanggal_publikasi', $filters['year']);
        }
        if (isset($filters['search']) && $filters['search']) {
            $query->where('judul', 'like', '%' . $filters['search'] . '%');
        }
    }
}
