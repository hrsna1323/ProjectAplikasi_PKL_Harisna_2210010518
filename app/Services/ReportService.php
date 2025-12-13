<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Skpd;
use App\Models\KategoriKonten;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get dashboard statistics for a given month and year.
     * Requirements: 5.1
     */
    public function getDashboardStats(int $month, int $year): array
    {
        $totalSkpd = Skpd::where('status', 'Aktif')
            ->orWhere('status', 'Active')
            ->count();
        
        $totalContentThisMonth = Content::whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->count();
        
        $pendingContent = Content::where('status', Content::STATUS_PENDING)->count();
        
        $approvedContentThisMonth = Content::whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->where('status', Content::STATUS_APPROVED)
            ->count();
        
        // Calculate non-compliant SKPD (those not meeting quota)
        $nonCompliantSkpd = $this->getNonCompliantSkpdCount($month, $year);

        // Additional stats for comprehensive dashboard
        $totalContentAllTime = Content::count();
        $rejectedContentThisMonth = Content::whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->where('status', Content::STATUS_REJECTED)
            ->count();

        // Content by category this month
        $contentByCategory = Content::whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->select('kategori_id', DB::raw('count(*) as total'))
            ->groupBy('kategori_id')
            ->with('kategori')
            ->get()
            ->mapWithKeys(function ($item) {
                $kategoriName = $item->kategori ? $item->kategori->nama_kategori : 'Tidak Berkategori';
                return [$kategoriName => $item->total];
            });

        return [
            'total_skpd' => $totalSkpd,
            'total_content_this_month' => $totalContentThisMonth,
            'pending_content' => $pendingContent,
            'approved_content_this_month' => $approvedContentThisMonth,
            'rejected_content_this_month' => $rejectedContentThisMonth,
            'non_compliant_skpd' => $nonCompliantSkpd,
            'total_content_all_time' => $totalContentAllTime,
            'content_by_category' => $contentByCategory,
        ];
    }


    /**
     * Get content history with filters.
     * Requirements: 6.1
     */
    public function getContentHistory(array $filters): Collection
    {
        $query = Content::with(['skpd', 'publisher', 'kategori', 'verifications.verifikator'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['skpd_id']) && $filters['skpd_id']) {
            $query->where('skpd_id', $filters['skpd_id']);
        }

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
            $query->whereMonth('tanggal_publikasi', $filters['month'])
                  ->whereYear('tanggal_publikasi', $filters['year']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $query->where('judul', 'like', '%' . $filters['search'] . '%');
        }

        return $query->get();
    }

    /**
     * Get SKPD performance for a given month and year.
     * Requirements: 6.3
     */
    public function getSkpdPerformance(int $month, int $year): Collection
    {
        return Skpd::where(function ($q) {
                $q->where('status', 'Aktif')->orWhere('status', 'Active');
            })
            ->withCount(['contents as approved_count' => function ($query) use ($month, $year) {
                $query->whereMonth('tanggal_publikasi', $month)
                    ->whereYear('tanggal_publikasi', $year)
                    ->where('status', Content::STATUS_APPROVED);
            }])
            ->withCount(['contents as pending_count' => function ($query) use ($month, $year) {
                $query->whereMonth('tanggal_publikasi', $month)
                    ->whereYear('tanggal_publikasi', $year)
                    ->where('status', Content::STATUS_PENDING);
            }])
            ->withCount(['contents as rejected_count' => function ($query) use ($month, $year) {
                $query->whereMonth('tanggal_publikasi', $month)
                    ->whereYear('tanggal_publikasi', $year)
                    ->where('status', Content::STATUS_REJECTED);
            }])
            ->withCount(['contents as total_count' => function ($query) use ($month, $year) {
                $query->whereMonth('tanggal_publikasi', $month)
                    ->whereYear('tanggal_publikasi', $year);
            }])
            ->get()
            ->map(function ($skpd) {
                $skpd->compliance_percentage = $skpd->kuota_bulanan > 0 
                    ? round(($skpd->approved_count / $skpd->kuota_bulanan) * 100, 2) 
                    : 0;
                $skpd->is_compliant = $skpd->approved_count >= $skpd->kuota_bulanan;
                $skpd->compliance_status = $this->getComplianceStatusLabel($skpd->compliance_percentage);
                return $skpd;
            });
    }

    /**
     * Get SKPD performance trend for the last N months.
     * Requirements: 5.5
     */
    public function getSkpdPerformanceTrend(Skpd $skpd, int $months = 6): array
    {
        $trend = [];
        $currentDate = now();

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $currentDate->copy()->subMonths($i);
            $month = $date->month;
            $year = $date->year;

            $approvedCount = $skpd->contents()
                ->whereMonth('tanggal_publikasi', $month)
                ->whereYear('tanggal_publikasi', $year)
                ->where('status', Content::STATUS_APPROVED)
                ->count();

            $trend[] = [
                'month' => $date->format('M Y'),
                'month_num' => $month,
                'year' => $year,
                'approved_count' => $approvedCount,
                'quota' => $skpd->kuota_bulanan,
                'compliance_percentage' => $skpd->kuota_bulanan > 0 
                    ? round(($approvedCount / $skpd->kuota_bulanan) * 100, 2) 
                    : 0,
            ];
        }

        return $trend;
    }


    /**
     * Generate content report data.
     * Requirements: 6.4
     */
    public function generateContentReport(array $filters, string $format): array
    {
        $contents = $this->getContentHistory($filters);
        
        // Calculate summary statistics
        $summary = [
            'total_content' => $contents->count(),
            'by_status' => $contents->groupBy('status')->map->count(),
            'by_category' => $contents->groupBy(function ($item) {
                return $item->kategori ? $item->kategori->nama_kategori : 'Tidak Berkategori';
            })->map->count(),
            'by_skpd' => $contents->groupBy(function ($item) {
                return $item->skpd ? $item->skpd->nama_skpd : 'Tidak Ada SKPD';
            })->map->count(),
        ];

        return [
            'contents' => $contents,
            'summary' => $summary,
            'filters' => $filters,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate SKPD report data.
     * Requirements: 6.4
     */
    public function generateSkpdReport(int $month, int $year, string $format): array
    {
        $performance = $this->getSkpdPerformance($month, $year);
        
        // Calculate summary statistics
        $totalSkpd = $performance->count();
        $compliantSkpd = $performance->where('is_compliant', true)->count();
        $nonCompliantSkpd = $totalSkpd - $compliantSkpd;
        $averageCompliance = $performance->avg('compliance_percentage');
        $totalApproved = $performance->sum('approved_count');
        $totalPending = $performance->sum('pending_count');
        $totalRejected = $performance->sum('rejected_count');

        $summary = [
            'total_skpd' => $totalSkpd,
            'compliant_skpd' => $compliantSkpd,
            'non_compliant_skpd' => $nonCompliantSkpd,
            'compliance_rate' => $totalSkpd > 0 ? round(($compliantSkpd / $totalSkpd) * 100, 2) : 0,
            'average_compliance' => round($averageCompliance, 2),
            'total_approved' => $totalApproved,
            'total_pending' => $totalPending,
            'total_rejected' => $totalRejected,
        ];

        return [
            'performance' => $performance,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get count of non-compliant SKPD.
     */
    protected function getNonCompliantSkpdCount(int $month, int $year): int
    {
        $skpds = Skpd::where(function ($q) {
            $q->where('status', 'Aktif')->orWhere('status', 'Active');
        })->get();
        
        $nonCompliant = 0;

        foreach ($skpds as $skpd) {
            $approvedCount = $skpd->contents()
                ->whereMonth('tanggal_publikasi', $month)
                ->whereYear('tanggal_publikasi', $year)
                ->where('status', Content::STATUS_APPROVED)
                ->count();

            if ($approvedCount < $skpd->kuota_bulanan) {
                $nonCompliant++;
            }
        }

        return $nonCompliant;
    }

    /**
     * Get compliance status label based on percentage.
     */
    protected function getComplianceStatusLabel(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'Memenuhi';
        } elseif ($percentage >= 50) {
            return 'Sebagian';
        } else {
            return 'Belum Memenuhi';
        }
    }

    /**
     * Get all categories for filter dropdown.
     */
    public function getCategories(): Collection
    {
        return KategoriKonten::where('is_active', true)->orderBy('nama_kategori')->get();
    }

    /**
     * Get all active SKPDs for filter dropdown.
     */
    public function getActiveSkpds(): Collection
    {
        return Skpd::where(function ($q) {
            $q->where('status', 'Aktif')->orWhere('status', 'Active');
        })->orderBy('nama_skpd')->get();
    }

    /**
     * Get content status options for filter dropdown.
     */
    public function getStatusOptions(): array
    {
        return Content::getStatusOptions();
    }
}
