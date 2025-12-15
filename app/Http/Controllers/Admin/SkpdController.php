<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSkpdRequest;
use App\Http\Requests\UpdateSkpdRequest;
use App\Models\Skpd;
use App\Services\SkpdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkpdController extends Controller
{
    protected SkpdService $skpdService;

    public function __construct(SkpdService $skpdService)
    {
        $this->skpdService = $skpdService;
    }

    /**
     * Display a listing of SKPDs.
     */
    public function index(Request $request): View
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $skpds = $this->skpdService->getSkpdWithQuotaStatus($month, $year);

        return view('admin.skpd.index', [
            'skpds' => $skpds,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Show the form for creating a new SKPD.
     */
    public function create(): View
    {
        return view('admin.skpd.create');
    }


    /**
     * Store a newly created SKPD in storage.
     */
    public function store(StoreSkpdRequest $request): RedirectResponse
    {
        try {
            $skpd = $this->skpdService->createSkpd($request->validated());

            return redirect()
                ->route('admin.skpd.index')
                ->with('success', "SKPD '{$skpd->nama_skpd}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan SKPD: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified SKPD.
     */
    public function show(int $id, Request $request): View
    {
        $skpd = $this->skpdService->getSkpdById($id);

        if (!$skpd) {
            abort(404, 'SKPD tidak ditemukan.');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $complianceStatus = $this->skpdService->calculateComplianceStatus($skpd, $month, $year);

        // Get content statistics for this SKPD
        $contentStats = [
            'total' => $skpd->contents()->count(),
            'pending' => $skpd->contents()->where('status', 'Pending')->count(),
            'approved' => $skpd->contents()->where('status', 'Approved')->count(),
            'rejected' => $skpd->contents()->where('status', 'Rejected')->count(),
            'published' => $skpd->contents()->where('status', 'Published')->count(),
        ];

        return view('admin.skpd.show', [
            'skpd' => $skpd,
            'complianceStatus' => $complianceStatus,
            'contentStats' => $contentStats,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Show the form for editing the specified SKPD.
     */
    public function edit(int $id): View
    {
        $skpd = Skpd::findOrFail($id);

        return view('admin.skpd.edit', [
            'skpd' => $skpd,
        ]);
    }

    /**
     * Update the specified SKPD in storage.
     */
    public function update(UpdateSkpdRequest $request, int $id): RedirectResponse
    {
        $skpd = Skpd::findOrFail($id);

        try {
            $this->skpdService->updateSkpd($skpd, $request->validated());

            return redirect()
                ->route('admin.skpd.index')
                ->with('success', "SKPD '{$skpd->nama_skpd}' berhasil diperbarui.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui SKPD: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified SKPD from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $skpd = Skpd::findOrFail($id);

        try {
            $namaSkpd = $skpd->nama_skpd;
            $this->skpdService->deleteSkpd($skpd);

            return redirect()
                ->route('admin.skpd.index')
                ->with('success', "SKPD '{$namaSkpd}' berhasil dihapus.");
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }
}
