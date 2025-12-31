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
    public function exportContentReport(Request $request): Response
    {
        $filters = $request->only(['skpd_id', 'status', 'kategori_id', 'start_date', 'end_date']);
        $format = $request->get('format', 'csv');
        $reportData = $this->reportService->generateContentReport($filters, $format);
        
        // Log export activity
        if (auth()->check()) {
            $this->activityLogService->logUserAction(
                'REPORT_EXPORTED',
                "Export laporan konten format {$format}",
                auth()->user()
            );
        }

        return match($format) {
            'pdf' => $this->exportContentPdf($reportData),
            'word' => $this->exportContentWord($reportData),
            default => $this->exportContentCsv($reportData),
        };
    }

    /**
     * Export SKPD performance report.
     * Requirements: 6.4, 6.5
     */
    public function exportSkpdReport(Request $request): Response
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $format = $request->get('format', 'csv');
        
        $reportData = $this->reportService->generateSkpdReport($month, $year, $format);
        
        // Log export activity
        if (auth()->check()) {
            $this->activityLogService->logUserAction(
                'REPORT_EXPORTED',
                "Export laporan performa SKPD format {$format}",
                auth()->user()
            );
        }

        return match($format) {
            'pdf' => $this->exportSkpdPdf($reportData, $month, $year),
            'word' => $this->exportSkpdWord($reportData, $month, $year),
            default => $this->exportSkpdCsv($reportData, $month, $year),
        };
    }

    /**
     * Export dashboard report.
     */
    public function exportDashboardReport(Request $request): Response
    {
        $format = $request->get('format', 'csv');
        $month = now()->month;
        $year = now()->year;
        
        $reportData = $this->reportService->generateSkpdReport($month, $year, $format);
        
        // Log export activity
        if (auth()->check()) {
            $this->activityLogService->logUserAction(
                'REPORT_EXPORTED',
                "Export laporan dashboard format {$format}",
                auth()->user()
            );
        }

        return match($format) {
            'pdf' => $this->exportSkpdPdf($reportData, $month, $year),
            'word' => $this->exportSkpdWord($reportData, $month, $year),
            default => $this->exportSkpdCsv($reportData, $month, $year),
        };
    }

    // CSV Export Methods
    private function exportContentCsv($reportData): StreamedResponse
    {
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

    private function exportSkpdCsv($reportData, $month, $year): StreamedResponse
    {
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

    // PDF Export Methods
    private function exportContentPdf($reportData): Response
    {
        $filename = 'laporan-konten-' . now()->format('Y-m-d-His') . '.pdf';
        
        $html = $this->generateContentPdfHtml($reportData);
        
        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportSkpdPdf($reportData, $month, $year): Response
    {
        $monthName = \DateTime::createFromFormat('!m', $month)->format('F');
        $filename = 'laporan-skpd-' . $monthName . '-' . $year . '.pdf';
        
        $html = $this->generateSkpdPdfHtml($reportData, $monthName, $year);
        
        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // Word Export Methods
    private function exportContentWord($reportData): Response
    {
        $filename = 'laporan-konten-' . now()->format('Y-m-d-His') . '.doc';
        
        $html = $this->generateContentWordHtml($reportData);
        
        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportSkpdWord($reportData, $month, $year): Response
    {
        $monthName = \DateTime::createFromFormat('!m', $month)->format('F');
        $filename = 'laporan-skpd-' . $monthName . '-' . $year . '.doc';
        
        $html = $this->generateSkpdWordHtml($reportData, $monthName, $year);
        
        return response($html, 200, [
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // HTML Generation Methods
    private function generateContentPdfHtml($reportData): string
    {
        $html = '<html><head><meta charset="UTF-8"><style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
            h1 { color: #333; }
        </style></head><body>';
        
        $html .= '<h1>Laporan Riwayat Konten</h1>';
        $html .= '<p>Dibuat: ' . now()->format('d/m/Y H:i:s') . '</p>';
        $html .= '<table><thead><tr>';
        $html .= '<th>No</th><th>Judul</th><th>SKPD</th><th>Kategori</th><th>Publisher</th><th>Tanggal</th><th>Status</th>';
        $html .= '</tr></thead><tbody>';
        
        $no = 1;
        foreach ($reportData['contents'] as $content) {
            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            $html .= '<td>' . htmlspecialchars($content->judul) . '</td>';
            $html .= '<td>' . htmlspecialchars($content->skpd->nama_skpd ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($content->kategori->nama_kategori ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($content->publisher->name ?? '-') . '</td>';
            $html .= '<td>' . ($content->tanggal_publikasi?->format('d/m/Y') ?? '-') . '</td>';
            $html .= '<td>' . $content->status . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></body></html>';
        return $html;
    }

    private function generateSkpdPdfHtml($reportData, $monthName, $year): string
    {
        $html = '<html><head><meta charset="UTF-8"><style>
            body { font-family: Arial, sans-serif; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #4CAF50; color: white; }
            h1 { color: #333; }
            .summary { background-color: #f0f0f0; padding: 15px; margin: 20px 0; }
        </style></head><body>';
        
        $html .= '<h1>Laporan Performa SKPD</h1>';
        $html .= '<p>Periode: ' . $monthName . ' ' . $year . '</p>';
        $html .= '<p>Dibuat: ' . now()->format('d/m/Y H:i:s') . '</p>';
        
        $html .= '<div class="summary">';
        $html .= '<h2>Ringkasan</h2>';
        $html .= '<p>Total SKPD: ' . $reportData['summary']['total_skpd'] . '</p>';
        $html .= '<p>Memenuhi Kuota: ' . $reportData['summary']['compliant_skpd'] . '</p>';
        $html .= '<p>Belum Memenuhi: ' . $reportData['summary']['non_compliant_skpd'] . '</p>';
        $html .= '<p>Rata-rata Kepatuhan: ' . $reportData['summary']['average_compliance'] . '%</p>';
        $html .= '</div>';
        
        $html .= '<table><thead><tr>';
        $html .= '<th>No</th><th>SKPD</th><th>Kuota</th><th>Approved</th><th>Pending</th><th>Rejected</th><th>%</th><th>Status</th>';
        $html .= '</tr></thead><tbody>';
        
        $no = 1;
        foreach ($reportData['performance'] as $skpd) {
            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            $html .= '<td>' . htmlspecialchars($skpd->nama_skpd) . '</td>';
            $html .= '<td>' . $skpd->kuota_bulanan . '</td>';
            $html .= '<td>' . $skpd->approved_count . '</td>';
            $html .= '<td>' . $skpd->pending_count . '</td>';
            $html .= '<td>' . $skpd->rejected_count . '</td>';
            $html .= '<td>' . $skpd->compliance_percentage . '%</td>';
            $html .= '<td>' . $skpd->compliance_status . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></body></html>';
        return $html;
    }

    private function generateContentWordHtml($reportData): string
    {
        return $this->generateContentPdfHtml($reportData);
    }

    private function generateSkpdWordHtml($reportData, $monthName, $year): string
    {
        return $this->generateSkpdPdfHtml($reportData, $monthName, $year);
    }
}
