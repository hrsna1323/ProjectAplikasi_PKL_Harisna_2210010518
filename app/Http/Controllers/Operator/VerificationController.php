<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveContentRequest;
use App\Http\Requests\RejectContentRequest;
use App\Models\Content;
use App\Models\KategoriKonten;
use App\Models\Skpd;
use App\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for content verification operations by Operator.
 * 
 * Requirements: 4.1, 4.2, 4.3, 4.4
 */
class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService
    ) {}

    /**
     * Display list of pending contents for verification.
     * 
     * Requirements: 4.1
     */
    public function index(Request $request): View
    {
        $filters = [
            'skpd_id' => $request->input('skpd_id'),
            'kategori_id' => $request->input('kategori_id'),
            'search' => $request->input('search'),
        ];

        $contents = $this->verificationService->getPendingContents($filters);
        $skpds = Skpd::orderBy('nama_skpd')->get();
        $kategoris = KategoriKonten::active()->orderBy('nama_kategori')->get();

        return view('operator.verification.index', [
            'contents' => $contents,
            'skpds' => $skpds,
            'kategoris' => $kategoris,
            'filters' => $filters,
        ]);
    }

    /**
     * Display content detail for review.
     * 
     * Requirements: 4.2
     */
    public function show(int $contentId): View
    {
        $content = $this->verificationService->getContentForReview($contentId);

        if (!$content) {
            abort(404, 'Konten tidak ditemukan.');
        }

        $verificationHistory = $this->verificationService->getVerificationHistory($content);

        return view('operator.verification.show', [
            'content' => $content,
            'verificationHistory' => $verificationHistory,
        ]);
    }

    /**
     * Approve content.
     * 
     * Requirements: 4.3
     */
    public function approve(ApproveContentRequest $request, int $contentId): RedirectResponse
    {
        $content = Content::find($contentId);

        if (!$content) {
            return back()->with('error', 'Konten tidak ditemukan.');
        }

        if (!$content->isPending()) {
            return back()->with('error', 'Hanya konten dengan status Pending yang dapat diverifikasi.');
        }

        try {
            $this->verificationService->approveContent(
                $content,
                auth()->user(),
                $request->input('alasan')
            );

            return redirect()
                ->route('operator.verification.index')
                ->with('success', 'Konten berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui konten: ' . $e->getMessage());
        }
    }

    /**
     * Reject content.
     * 
     * Requirements: 4.4
     */
    public function reject(RejectContentRequest $request, int $contentId): RedirectResponse
    {
        $content = Content::find($contentId);

        if (!$content) {
            return back()->with('error', 'Konten tidak ditemukan.');
        }

        if (!$content->isPending()) {
            return back()->with('error', 'Hanya konten dengan status Pending yang dapat diverifikasi.');
        }

        try {
            $this->verificationService->rejectContent(
                $content,
                auth()->user(),
                $request->input('alasan')
            );

            return redirect()
                ->route('operator.verification.index')
                ->with('success', 'Konten berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak konten: ' . $e->getMessage());
        }
    }

    /**
     * Display verification history/timeline for a content.
     * 
     * Requirements: 4.2
     */
    public function history(int $contentId): View
    {
        $content = $this->verificationService->getContentForReview($contentId);

        if (!$content) {
            abort(404, 'Konten tidak ditemukan.');
        }

        $verificationHistory = $this->verificationService->getVerificationHistory($content);

        return view('operator.verification.history', [
            'content' => $content,
            'verificationHistory' => $verificationHistory,
        ]);
    }

    /**
     * Display general verification history page.
     * 
     * Requirements: 1.1, 1.4
     */
    public function historyIndex(Request $request): View
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'skpd_id' => $request->input('skpd_id'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $verifications = $this->verificationService->getAllVerificationHistory($filters);
        $skpds = Skpd::orderBy('nama_skpd')->get();

        return view('operator.verification.history-index', [
            'verifications' => $verifications,
            'skpds' => $skpds,
            'filters' => $filters,
        ]);
    }
}
