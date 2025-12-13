<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Skpd;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with('skpd');

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by SKPD
        if ($request->filled('skpd_id')) {
            $query->where('skpd_id', $request->skpd_id);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $skpds = Skpd::where('status', 'Active')->orderBy('nama_skpd')->get();

        return view('admin.user.index', compact('users', 'skpds'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $skpds = Skpd::where('status', 'Active')->orderBy('nama_skpd')->get();
        $roles = ['Admin', 'Operator', 'Publisher'];

        return view('admin.user.create', compact('skpds', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Set default is_active if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        // Clear skpd_id if not Publisher
        if ($validated['role'] !== 'Publisher') {
            $validated['skpd_id'] = null;
        }

        $user = User::create($validated);

        // Log activity
        $this->activityLogService->logUserAction(
            'user_created',
            "User baru dibuat: {$user->name} ({$user->role})",
            auth()->user()
        );

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    /**
     * Display the specified user.
     */
    public function show(int $id): View
    {
        $user = User::with(['skpd', 'contents', 'verifications'])->findOrFail($id);

        // Get user statistics
        $stats = [
            'total_contents' => $user->contents()->count(),
            'approved_contents' => $user->contents()->where('status', 'Approved')->count(),
            'pending_contents' => $user->contents()->where('status', 'Pending')->count(),
            'rejected_contents' => $user->contents()->where('status', 'Rejected')->count(),
            'total_verifications' => $user->verifications()->count(),
        ];

        return view('admin.user.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(int $id): View
    {
        $user = User::findOrFail($id);
        $skpds = Skpd::where('status', 'Active')->orderBy('nama_skpd')->get();
        $roles = ['Admin', 'Operator', 'Publisher'];

        return view('admin.user.edit', compact('user', 'skpds', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        // Hash password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Clear skpd_id if not Publisher
        if ($validated['role'] !== 'Publisher') {
            $validated['skpd_id'] = null;
        }

        // Track changes for activity log
        $changes = [];
        foreach (['name', 'email', 'role', 'skpd_id', 'is_active'] as $field) {
            if (isset($validated[$field]) && $user->$field != $validated[$field]) {
                $changes[$field] = [
                    'old' => $user->$field,
                    'new' => $validated[$field],
                ];
            }
        }

        $user->update($validated);

        // Log activity if there were changes
        if (!empty($changes)) {
            $this->activityLogService->logUserAction(
                'user_updated',
                "User diupdate: {$user->name}. Perubahan: " . json_encode($changes),
                auth()->user()
            );
        }

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil diupdate');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.user.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri');
        }

        // Check if user has related content
        if ($user->contents()->exists()) {
            return redirect()
                ->route('admin.user.index')
                ->with('error', 'User tidak dapat dihapus karena memiliki konten terkait');
        }

        $userName = $user->name;
        $user->delete();

        // Log activity
        $this->activityLogService->logUserAction(
            'user_deleted',
            "User dihapus: {$userName}",
            auth()->user()
        );

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil dihapus');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.user.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun sendiri');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        // Log activity
        $this->activityLogService->logUserAction(
            'user_status_changed',
            "Status user {$user->name} {$status}",
            auth()->user()
        );

        return redirect()
            ->route('admin.user.index')
            ->with('success', "User berhasil {$status}");
    }
}
