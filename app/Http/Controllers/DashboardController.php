<?php

namespace App\Http\Controllers;

use App\Services\ContentService;
use App\Services\NotificationService;
use App\Services\ReportService;
use App\Services\SkpdService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected ContentService $contentService;
    protected NotificationService $notificationService;
    protected ReportService $reportService;
    protected SkpdService $skpdService;

    public function __construct(
        ContentService $contentService,
        NotificationService $notificationService,
        ReportService $reportService,
        SkpdService $skpdService
    ) {
        $this->contentService = $contentService;
        $this->notificationService = $notificationService;
        $this->reportService = $reportService;
        $this->skpdService = $skpdService;
    }

    /**
     * Publisher Dashboard
     * Requirements: 3.5, 5.1, 7.5
     */
    public function publisher(): View
    {
        $user = auth()->user();
        
        // Get content statistics for publisher's SKPD
        $contentStats = $this->contentService->getContentStatsBySkpd($user->skpd);
        
        // Get quota progress for current month
        $quotaProgress = $this->contentService->checkQuotaProgress(
            $user->skpd,
            now()->month,
            now()->year
        );
        
        // Get recent contents (last 5)
        $recentContents = $this->contentService->getContentByPublisher($user, [])->take(5);
        
        // Get unread notifications
        $notifications = $this->notificationService->getUnreadNotifications($user);

        return view('publisher.dashboard', compact(
            'contentStats',
            'quotaProgress',
            'recentContents',
            'notifications'
        ));
    }


    /**
     * Operator Dashboard
     * Requirements: 4.1, 5.1, 5.2, 7.5
     */
    public function operator(): View
    {
        $user = auth()->user();
        
        // Get dashboard statistics for current month
        $stats = $this->reportService->getDashboardStats(now()->month, now()->year);
        
        // Get pending contents for verification (limit to 10 for dashboard)
        $pendingContents = $this->contentService->getAllContent(['status' => 'Pending'])->take(10);
        
        // Get SKPD performance summary
        $skpdPerformance = $this->reportService->getSkpdPerformance(now()->month, now()->year);
        
        // Get SKPDs not meeting quota
        $nonCompliantSkpds = $skpdPerformance->where('is_compliant', false)->take(5);
        
        // Get unread notifications
        $notifications = $this->notificationService->getUnreadNotifications($user);

        return view('operator.dashboard', compact(
            'stats',
            'pendingContents',
            'skpdPerformance',
            'nonCompliantSkpds',
            'notifications'
        ));
    }

    /**
     * Admin Dashboard
     * Requirements: 5.1, 7.5, 8.4
     */
    public function admin(): View
    {
        $user = auth()->user();
        
        // Get dashboard statistics for current month
        $stats = $this->reportService->getDashboardStats(now()->month, now()->year);
        
        // Get SKPD performance summary
        $skpdPerformance = $this->reportService->getSkpdPerformance(now()->month, now()->year);
        
        // Get SKPDs not meeting quota
        $nonCompliantSkpds = $skpdPerformance->where('is_compliant', false);
        
        // Get unread notifications
        $notifications = $this->notificationService->getUnreadNotifications($user);

        return view('admin.dashboard', compact(
            'stats',
            'skpdPerformance',
            'nonCompliantSkpds',
            'notifications'
        ));
    }
}
