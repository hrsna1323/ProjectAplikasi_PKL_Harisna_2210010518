<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skpd;
use App\Models\Content;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\KategoriKonten;
use App\Services\ReportService;
use App\Services\SkpdService;
use App\Services\VerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    protected ReportService $reportService;
    protected SkpdService $skpdService;
    protected VerificationService $verificationService;

    public function __construct(
        ReportService $reportService,
        SkpdService $skpdService,
        VerificationService $verificationService
    ) {
        $this->reportService = $reportService;
        $this->skpdService = $skpdService;
        $this->verificationService = $verificationService;
    }

    /**
     * Get admin dashboard stats
     */
    public function adminStats(): JsonResponse
    {
        $month = now()->month;
        $year = now()->year;

        $stats = [
            'totalSKPD' => Skpd::count(),
            'totalContent' => Content::whereMonth('created_at', $month)->whereYear('created_at', $year)->count(),
            'pendingVerification' => Content::where('status', 'Pending')->count(),
            'nonCompliantSKPD' => $this->getNonCompliantCount($month, $year),
        ];

        return response()->json($stats);
    }

    /**
     * Get SKPD list with compliance status
     */
    public function skpdList(): JsonResponse
    {
        $month = now()->month;
        $year = now()->year;

        $skpds = Skpd::active()->get()->map(function ($skpd) use ($month, $year) {
            $published = $skpd->contents()
                ->whereMonth('tanggal_publikasi', $month)
                ->whereYear('tanggal_publikasi', $year)
                ->where('status', 'Approved')
                ->count();

            $percentage = $skpd->kuota_bulanan > 0 ? ($published / $skpd->kuota_bulanan) * 100 : 0;

            return [
                'id' => $skpd->id,
                'name' => $skpd->nama_skpd,
                'quota' => $skpd->kuota_bulanan,
                'published' => $published,
                'status' => $percentage >= 100 ? 'compliant' : ($percentage >= 50 ? 'warning' : 'critical'),
            ];
        });

        return response()->json($skpds);
    }


    /**
     * Get recent activities
     */
    public function recentActivities(): JsonResponse
    {
        $activities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user' => $activity->user->name ?? 'System',
                    'action' => $activity->action_type,
                    'detail' => $activity->detail,
                    'time' => $activity->created_at->diffForHumans(),
                ];
            });

        return response()->json($activities);
    }

    /**
     * Get operator dashboard stats
     */
    public function operatorStats(): JsonResponse
    {
        $today = now()->toDateString();

        $stats = [
            'pendingContent' => Content::where('status', 'Pending')->count(),
            'approvedToday' => Content::where('status', 'Approved')->whereDate('updated_at', $today)->count(),
            'rejectedToday' => Content::where('status', 'Rejected')->whereDate('updated_at', $today)->count(),
            'totalVerified' => Content::whereIn('status', ['Approved', 'Rejected'])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get pending contents for verification
     */
    public function pendingContents(): JsonResponse
    {
        $contents = Content::with(['skpd', 'publisher', 'kategori'])
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($content) {
                return [
                    'id' => $content->id,
                    'title' => $content->judul,
                    'skpd' => $content->skpd->nama_skpd ?? '-',
                    'publisher' => $content->publisher->name ?? '-',
                    'category' => $content->kategori->nama_kategori ?? '-',
                    'date' => $content->tanggal_publikasi?->format('Y-m-d') ?? '-',
                    'url' => $content->url_publikasi,
                ];
            });

        return response()->json($contents);
    }

    /**
     * Get publisher dashboard stats
     */
    public function publisherStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $skpd = $user->skpd;
        $month = now()->month;
        $year = now()->year;

        if (!$skpd) {
            return response()->json(['error' => 'No SKPD assigned'], 400);
        }

        $approved = $skpd->contents()
            ->whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->where('status', 'Approved')
            ->count();

        $stats = [
            'quotaProgress' => [
                'current' => $approved,
                'total' => $skpd->kuota_bulanan,
            ],
            'approved' => $user->contents()->where('status', 'Approved')->count(),
            'rejected' => $user->contents()->where('status', 'Rejected')->count(),
            'pending' => $user->contents()->where('status', 'Pending')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get publisher's contents
     */
    public function myContents(Request $request): JsonResponse
    {
        $user = $request->user();

        $contents = Content::with(['kategori', 'verifications.verifikator'])
            ->where('publisher_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($content) {
                $lastVerification = $content->verifications->last();
                return [
                    'id' => $content->id,
                    'title' => $content->judul,
                    'category' => $content->kategori->nama_kategori ?? '-',
                    'status' => strtolower($content->status),
                    'date' => $content->tanggal_publikasi?->format('Y-m-d') ?? '-',
                    'verifier' => $lastVerification?->verifikator?->name ?? '-',
                    'reason' => $lastVerification?->alasan ?? '-',
                ];
            });

        return response()->json($contents);
    }

    /**
     * Get categories
     */
    public function categories(): JsonResponse
    {
        $categories = KategoriKonten::active()->get(['id', 'nama_kategori as name']);
        return response()->json($categories);
    }

    /**
     * Get notifications
     */
    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->type,
                    'message' => $notif->message,
                    'unread' => !$notif->is_read,
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Get current user info
     */
    public function currentUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'role' => strtolower($user->role),
            'skpd' => $user->skpd?->nama_skpd,
        ]);
    }

    protected function getNonCompliantCount(int $month, int $year): int
    {
        return Skpd::active()->get()->filter(function ($skpd) use ($month, $year) {
            $published = $skpd->contents()
                ->whereMonth('tanggal_publikasi', $month)
                ->whereYear('tanggal_publikasi', $year)
                ->where('status', 'Approved')
                ->count();
            return $published < $skpd->kuota_bulanan;
        })->count();
    }
}
