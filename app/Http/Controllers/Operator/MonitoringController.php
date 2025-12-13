<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Skpd;
use App\Services\ReportService;
use App\Services\SkpdService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    protected ReportService $reportService;
    protected SkpdService $skpdService;

    public function __construct(ReportService $reportService, SkpdService $skpdService)
    {
        $this->reportService = $reportService;
        $this->skpdService = $skpdService;
    }

    /**
     * Display monitoring dashboard for all SKPDs.
     * Requirements: 5.2, 5.4
     */
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get SKPD performance with quota status
        $skpds = $this->reportService->getSkpdPerformance($month, $year);

        // Get summary statistics
        $stats = [
            'total_skpd' => $skpds->count(),
            'compliant' => $skpds->where('is_compliant', true)->count(),
            'partial' => $skpds->where('compliance_status', 'Sebagian')->count(),
            'non_compliant' => $skpds->where('compliance_status', 'Belum Memenuhi')->count(),
        ];

        return view('operator.monitoring.index', compact('skpds', 'stats', 'month', 'year'));
    }

    /**
     * Display detailed monitoring for a specific SKPD with 6-month trend.
     * Requirements: 5.5
     */
    public function show(int $skpdId): View
    {
        $skpd = Skpd::findOrFail($skpdId);

        // Get current month performance
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $currentPerformance = [
            'approved' => $skpd->contents()
                ->whereMonth('tanggal_publikasi', $currentMonth)
                ->whereYear('tanggal_publikasi', $currentYear)
                ->where('status', 'Approved')
                ->count(),
            'pending' => $skpd->contents()
                ->whereMonth('tanggal_publikasi', $currentMonth)
                ->whereYear('tanggal_publikasi', $currentYear)
                ->where('status', 'Pending')
                ->count(),
            'rejected' => $skpd->contents()
                ->whereMonth('tanggal_publikasi', $currentMonth)
                ->whereYear('tanggal_publikasi', $currentYear)
                ->where('status', 'Rejected')
                ->count(),
            'quota' => $skpd->kuota_bulanan,
        ];

        $currentPerformance['compliance_percentage'] = $skpd->kuota_bulanan > 0
            ? round(($currentPerformance['approved'] / $skpd->kuota_bulanan) * 100, 2)
            : 0;

        // Get 6-month trend
        $trend = $this->reportService->getSkpdPerformanceTrend($skpd, 6);

        // Get recent contents
        $recentContents = $skpd->contents()
            ->with(['kategori', 'publisher'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('operator.monitoring.show', compact(
            'skpd',
            'currentPerformance',
            'trend',
            'recentContents'
        ));
    }
}
