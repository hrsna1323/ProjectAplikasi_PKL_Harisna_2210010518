<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriKonten;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Display a listing of all categories.
     */
    public function index(): View
    {
        $kategoris = KategoriKonten::withCount('contents')
            ->orderBy('nama_kategori')
            ->get();

        return view('admin.kategori.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('admin.kategori.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_konten,nama_kategori',
            'deskripsi' => 'nullable|string|max:1000',
        ]);

        $kategori = KategoriKonten::create([
            'nama_kategori' => $validated['nama_kategori'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => true,
        ]);


        // Log the action
        $this->activityLogService->logUserAction(
            ActivityLog::ACTION_KATEGORI_CREATED,
            "Kategori '{$kategori->nama_kategori}' telah dibuat",
            auth()->user()
        );

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified category.
     */
    public function show(int $id): View
    {
        $kategori = KategoriKonten::withCount('contents')->findOrFail($id);
        
        // Get recent contents in this category
        $recentContents = $kategori->contents()
            ->with(['skpd', 'publisher'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.kategori.show', compact('kategori', 'recentContents'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(int $id): View
    {
        $kategori = KategoriKonten::findOrFail($id);
        return view('admin.kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $kategori = KategoriKonten::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kategori_konten', 'nama_kategori')->ignore($kategori->id),
            ],
            'deskripsi' => 'nullable|string|max:1000',
        ]);

        $oldValues = [
            'nama_kategori' => $kategori->nama_kategori,
            'deskripsi' => $kategori->deskripsi,
        ];

        $kategori->update([
            'nama_kategori' => $validated['nama_kategori'],
            'deskripsi' => $validated['deskripsi'] ?? null,
        ]);

        // Log the action with changes
        $changes = [];
        if ($oldValues['nama_kategori'] !== $kategori->nama_kategori) {
            $changes[] = "nama: '{$oldValues['nama_kategori']}' → '{$kategori->nama_kategori}'";
        }
        if ($oldValues['deskripsi'] !== $kategori->deskripsi) {
            $changes[] = "deskripsi diperbarui";
        }

        if (!empty($changes)) {
            $this->activityLogService->logUserAction(
                ActivityLog::ACTION_KATEGORI_UPDATED,
                "Kategori '{$kategori->nama_kategori}' diperbarui. Perubahan: " . implode(', ', $changes),
                auth()->user(),
                json_encode($oldValues),
                json_encode([
                    'nama_kategori' => $kategori->nama_kategori,
                    'deskripsi' => $kategori->deskripsi,
                ])
            );
        }

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Toggle the active status of the specified category (soft disable).
     * This is used instead of hard delete to preserve data integrity.
     */
    public function destroy(int $id): RedirectResponse
    {
        $kategori = KategoriKonten::findOrFail($id);
        
        $oldStatus = $kategori->is_active;
        $kategori->is_active = !$kategori->is_active;
        $kategori->save();

        $statusText = $kategori->is_active ? 'diaktifkan' : 'dinonaktifkan';

        // Log the action
        $this->activityLogService->logUserAction(
            ActivityLog::ACTION_KATEGORI_TOGGLED,
            "Kategori '{$kategori->nama_kategori}' telah {$statusText}",
            auth()->user(),
            $oldStatus ? 'active' : 'inactive',
            $kategori->is_active ? 'active' : 'inactive'
        );

        return redirect()
            ->route('admin.kategori.index')
            ->with('success', "Kategori berhasil {$statusText}.");
    }
}
