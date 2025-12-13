<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Notification;
use App\Models\Skpd;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send notification to all Operators when new content is submitted.
     * Requirements: 7.1
     */
    public function sendContentSubmittedNotification(Content $content): void
    {
        $operators = User::where('role', 'Operator')->where('is_active', true)->get();
        
        foreach ($operators as $operator) {
            Notification::create([
                'user_id' => $operator->id,
                'type' => 'content_submitted',
                'message' => "Konten baru '{$content->judul}' dari SKPD {$content->skpd->nama_skpd} menunggu verifikasi.",
                'is_read' => false,
                'related_content_id' => $content->id,
            ]);
        }
    }

    /**
     * Send notification to Publisher when content is verified.
     * Requirements: 7.2
     */
    public function sendContentVerifiedNotification(Content $content, Verification $verification): void
    {
        $statusText = $verification->status === 'Approved' ? 'disetujui' : 'ditolak';
        
        Notification::create([
            'user_id' => $content->publisher_id,
            'type' => 'content_verified',
            'message' => "Konten '{$content->judul}' telah {$statusText} oleh Operator.",
            'is_read' => false,
            'related_content_id' => $content->id,
        ]);
    }

    /**
     * Send quota reminder notification to Publishers of an SKPD.
     * Requirements: 7.3
     */
    public function sendQuotaReminderNotification(Skpd $skpd, int $remaining): void
    {
        $publishers = User::where('skpd_id', $skpd->id)
            ->where('role', 'Publisher')
            ->where('is_active', true)
            ->get();
        
        foreach ($publishers as $publisher) {
            Notification::create([
                'user_id' => $publisher->id,
                'type' => 'quota_reminder',
                'message' => "SKPD {$skpd->nama_skpd} masih membutuhkan {$remaining} konten untuk memenuhi kuota bulan ini.",
                'is_read' => false,
            ]);
        }
    }

    /**
     * Send quota warning notification to Admin.
     * Requirements: 7.4
     */
    public function sendQuotaWarningNotification(Skpd $skpd): void
    {
        $admins = User::where('role', 'Admin')->where('is_active', true)->get();
        
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'quota_warning',
                'message' => "SKPD {$skpd->nama_skpd} tidak memenuhi kuota selama 2 bulan berturut-turut.",
                'is_read' => false,
            ]);
        }
    }

    /**
     * Get unread notifications for a user.
     * Requirements: 7.5
     */
    public function getUnreadNotifications(User $user): Collection
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $notificationId): void
    {
        Notification::where('id', $notificationId)->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Get all notifications for a user.
     */
    public function getAllNotifications(User $user, int $limit = 50): Collection
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
