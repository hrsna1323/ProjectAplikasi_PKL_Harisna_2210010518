<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Models\Content;
use App\Models\KategoriKonten;
use App\Services\ContentService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentController extends Controller
{
    protected ContentService $contentService;
    protected NotificationService $notificationService;

    public function __construct(ContentService $contentService, NotificationService $notificationService)
    {
        $this->contentService = $contentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the content for the publisher's SKPD.
     * Requirements: 3.5
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filters = $request->only(['status', 'kategori_id', 'start_date', 'end_date', 'search']);
        
        $contents = $this->contentService->getContentByPublisher($user, $filters);
        $categories = KategoriKonten::active()->get();
        $statusOptions = Content::getStatusOptions();
        
        // Get quota progress for current month
        $quotaProgress = $this->contentService->checkQuotaProgress(
            $user->skpd,
            now()->month,
            now()->year
        );

        return view('publisher.content.index', compact(
            'contents',
            'categories',
            'statusOptions',
            'quotaProgress',
            'filters'
        ));
    }

    /**
     * Show the form for creating a new content.
     * Requirements: 3.1
     */
    public function create(): View
    {
        $categories = KategoriKonten::active()->get();
        
        return view('publisher.content.create', compact('categories'));
    }


    /**
     * Store a newly created content in storage.
     * Requirements: 3.1, 3.2
     */
    public function store(StoreContentRequest $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Validate URL format using ContentService
        if (!$this->contentService->validateUrl($request->url_publikasi)) {
            return back()
                ->withInput()
                ->withErrors(['url_publikasi' => 'URL publikasi tidak valid atau tidak dapat diakses.']);
        }

        $content = $this->contentService->createContent($request->validated(), $user);
        
        // Send notification to all operators
        $this->notificationService->sendContentSubmittedNotification($content);

        return redirect()
            ->route('publisher.content.show', $content->id)
            ->with('success', 'Konten berhasil disimpan dan menunggu verifikasi.');
    }

    /**
     * Display the specified content.
     * Requirements: 3.5
     */
    public function show(int $id): View
    {
        $user = auth()->user();
        $content = $this->contentService->getContentById($id);

        if (!$content) {
            abort(404, 'Konten tidak ditemukan.');
        }

        // Ensure publisher can only view content from their own SKPD
        if ($content->skpd_id !== $user->skpd_id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat konten ini.');
        }

        // Get verification history
        $verificationHistory = $content->verifications()
            ->with('verifikator')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('publisher.content.show', compact('content', 'verificationHistory'));
    }

    /**
     * Show the form for editing the specified content.
     * Requirements: 3.4
     */
    public function edit(int $id): View
    {
        $user = auth()->user();
        $content = $this->contentService->getContentById($id);

        if (!$content) {
            abort(404, 'Konten tidak ditemukan.');
        }

        // Ensure publisher can only edit content from their own SKPD
        if ($content->skpd_id !== $user->skpd_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit konten ini.');
        }

        // Ensure publisher is the owner of the content
        if ($content->publisher_id !== $user->id) {
            abort(403, 'Anda hanya dapat mengedit konten yang Anda buat sendiri.');
        }

        // Check if content can be edited (only Draft or Rejected status)
        if (!$content->canBeEdited()) {
            return redirect()
                ->route('publisher.content.show', $content->id)
                ->with('error', 'Konten dengan status "' . $content->status . '" tidak dapat diedit.');
        }

        $categories = KategoriKonten::active()->get();

        return view('publisher.content.edit', compact('content', 'categories'));
    }

    /**
     * Update the specified content in storage.
     * Requirements: 3.4
     */
    public function update(UpdateContentRequest $request, int $id): RedirectResponse
    {
        $user = auth()->user();
        $content = $this->contentService->getContentById($id);

        if (!$content) {
            abort(404, 'Konten tidak ditemukan.');
        }

        // Ensure publisher can only update content from their own SKPD
        if ($content->skpd_id !== $user->skpd_id) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate konten ini.');
        }

        // Ensure publisher is the owner of the content
        if ($content->publisher_id !== $user->id) {
            abort(403, 'Anda hanya dapat mengupdate konten yang Anda buat sendiri.');
        }

        // Check if content can be edited
        if (!$content->canBeEdited()) {
            return redirect()
                ->route('publisher.content.show', $content->id)
                ->with('error', 'Konten dengan status "' . $content->status . '" tidak dapat diedit.');
        }

        // Validate URL format using ContentService
        if (!$this->contentService->validateUrl($request->url_publikasi)) {
            return back()
                ->withInput()
                ->withErrors(['url_publikasi' => 'URL publikasi tidak valid atau tidak dapat diakses.']);
        }

        $this->contentService->updateContent($content, $request->validated());

        // If content was rejected and now resubmitted, notify operators
        if ($content->status === Content::STATUS_PENDING) {
            $this->notificationService->sendContentSubmittedNotification($content->fresh());
        }

        return redirect()
            ->route('publisher.content.show', $content->id)
            ->with('success', 'Konten berhasil diperbarui.');
    }
}
