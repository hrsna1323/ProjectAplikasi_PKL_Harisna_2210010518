<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Content;
use App\Models\Skpd;
use App\Models\Verification;
use App\Models\User;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /**
     * Log a user action.
     *
     * @param string $action
     * @param string $detail
     * @param User $user
     * @param string|null $oldValue
     * @param string|null $newValue
     * @return ActivityLog
     */
    public function logUserAction(
        string $action,
        string $detail,
        User $user,
        ?string $oldValue = null,
        ?string $newValue = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $user->id,
            'action_type' => $action,
            'detail' => $detail,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /**
     * Log content creation.
     *
     * @param Content $content
     * @param User $user
     * @return ActivityLog
     */
    public function logContentCreated(Content $content, User $user): ActivityLog
    {
        return $this->logUserAction(
            ActivityLog::ACTION_CONTENT_CREATED,
            "Konten '{$content->judul}' telah dibuat oleh {$user->name}",
            $user
        );
    }

    /**
     * Log content verification (approved or rejected).
     *
     * @param Content $content
     * @param Verification $verification
     * @param User $user
     * @return ActivityLog
     */
    public function logContentVerified(Content $content, Verification $verification, User $user): ActivityLog
    {
        $status = $verification->status === 'Approved' ? 'disetujui' : 'ditolak';
        
        return $this->logUserAction(
            ActivityLog::ACTION_CONTENT_VERIFIED,
            "Konten '{$content->judul}' telah {$status} oleh {$user->name}. Alasan: {$verification->alasan}",
            $user,
            $content->status,
            $verification->status
        );
    }

    /**
     * Log SKPD update with old and new values.
     *
     * @param Skpd $skpd
     * @param array $changes
     * @param User $user
     * @return ActivityLog
     */
    public function logSkpdUpdated(Skpd $skpd, array $changes, User $user): ActivityLog
    {
        $oldValues = [];
        $newValues = [];
        $changeDetails = [];

        foreach ($changes as $field => $values) {
            $oldValues[$field] = $values['old'];
            $newValues[$field] = $values['new'];
            $changeDetails[] = "{$field}: '{$values['old']}' → '{$values['new']}'";
        }

        return $this->logUserAction(
            ActivityLog::ACTION_SKPD_UPDATED,
            "SKPD '{$skpd->nama_skpd}' diperbarui. Perubahan: " . implode(', ', $changeDetails),
            $user,
            json_encode($oldValues),
            json_encode($newValues)
        );
    }

    /**
     * Get activity logs with filters.
     *
     * @param array $filters
     * @return Collection
     */
    public function getActivityLogs(array $filters = []): Collection
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        if (isset($filters['action_type'])) {
            $query->ofType($filters['action_type']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->inPeriod($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['month']) && isset($filters['year'])) {
            $query->inMonth($filters['month'], $filters['year']);
        }

        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }
}
