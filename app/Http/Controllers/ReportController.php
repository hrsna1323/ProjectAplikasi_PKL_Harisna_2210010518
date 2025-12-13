<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected ReportService $reportService;
    protected ActivityLogService $activityLogService;

    public function __construct(ReportService $reportService, ActivityLogService $activityLogService)
    {
        $this->reportService = $reportService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display content history with filters.
     * Requirements: 6.1
     */
    public function contentHistory(Request $request): View
    {
        $filters = $request->only(['skpd_id', 'status', 'kategori_id', 'start_date', 'end_date', 'search']);
        $contents = $this->reportService->getContentHistory($filters);
        
        // Get filter options
        $skpds = $this->reportService->getActiveSkpds();
        $categories = $this->reportService->getCategories();
        $statusOptions = $this->reportService->getStatusOptions();

        return view('reports.content-history', compact(
            'contents',
            'filters',
            'skpds',
            'categories',
            'statusOptions'
        ));
    }

    /**
     * Display SKPD performance report.
     * Requirements: 6.3
     */
    public function skpdPerformance(Request $request): View
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        
        $reportData = $this->reportService->generateSkpdReport($month, $year, 'view');

        return view('reports.skpd-performance', [
            'performance' => $reportData['performance'],
            'summary' => $reportData['summary'],
            'month' => $month,
            'year' => $year,
        ]);
    }


    /**
     * Export content report to CSV.
     * Requirements: 6.4, 6.5
     */
    public function exportContentReport(Request $request): StreamedResponse
    {
        $filters = $request->only(['skpd_id', 'status', 'kategori_id', 'start_date', 'end_date']);
        $reportData = $this->reportService->generateContentReport($filters, 'csv');
        
        // Log export activity
        if (auth()->check()) {
            $this->activityLogService->logUserAction(
                'REPORT_EXPORTED',
                'Export laporan konten',
                auth()->user()
            );
        }

        $filename = 'laporan-konten-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($reportData) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($handle, [
                'No',
                'Judul',
                'SKPD',
                'Kategori',
                'Publisher',
                'URL Publikasi',
                'Tanggal Publikasi',
                'Status',
                'Dibuat',
            ]);

            // Data rows
            $no = 1;
            foreach ($reportData['contents'] as $content) {
                fputcsv($handle, [
                    $no++,
                    $content->judul,
                    $content->skpd->nama_skpd ?? '-',
                    $content->kategori->nama_kategori ?? '-',
                    $content->publisher->name ?? '-',
                    $content->url_publikasi,
                    $content->tanggal_publikasi?->format('Y-m-d') ?? '-',
                    $content->status,
                    $content->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export SKPD performance report to CSV.
     * Requirements: 6.4, 6.5
     */
    public function exportSkpdReport(Request $request): StreamedResponse
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        
        $reportData = $this->reportService->generateSkpdReport($month, $year, 'csv');
        
        // Log export activity
        if (auth()->check()) {
            $this->activityLogService->logUserAction(
                'REPORT_EXPORTED',
                'Export laporan performa SKPD',
                auth()->user()
            );
        }

        $monthName = \DateTime::createFromFormat('!m', $month)->format('F');
        $filename = 'laporan-skpd-' . $monthName . '-' . $year . '.csv';

        return response()->streamDownload(function () use ($reportData, $monthName, $year) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Report title
            fputcsv($handle, ['Laporan Performa SKPD - ' . $monthName . ' ' . $year]);
            fputcsv($handle, ['Dibuat: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []); // Empty row
            
            // Summary
            fputcsv($handle, ['RINGKASAN']);
            fputcsv($handle, ['Total SKPD', $reportData['summary']['total_skpd']]);
            fputcsv($handle, ['Memenuhi Kuota', $reportData['summary']['compliant_skpd']]);
            fputcsv($handle, ['Belum Memenuhi', $reportData['summary']['non_compliant_skpd']]);
            fputcsv($handle, ['Rata-rata Kepatuhan', $reportData['summary']['average_compliance'] . '%']);
            fputcsv($handle, []); // Empty row
            
            // Header row
            fputcsv($handle, [
                'No',
                'Nama SKPD',
                'Website',
                'Kuota',
                'Approved',
                'Pending',
                'Rejected',
                'Persentase',
                'Status',
            ]);

            // Data rows
            $no = 1;
            foreach ($reportData['performance'] as $skpd) {
                fputcsv($handle, [
                    $no++,
                    $skpd->nama_skpd,
                    $skpd->website_url ?? '-',
                    $skpd->kuota_bulanan,
                    $skpd->approved_count,
                    $skpd->pending_count,
                    $skpd->rejected_count,
                    $skpd->compliance_percentage . '%',
                    $skpd->compliance_status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
