<?php

namespace App\Services;

use App\Models\Skpd;
use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SkpdService
{
    protected ActivityLogService $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Create a new SKPD with default kuota_bulanan = 3.
     *
     * @param array $data
     * @return Skpd
     */
    public function createSkpd(array $data): Skpd
    {
        // Ensure default quota if not provided
        if (!isset($data['kuota_bulanan'])) {
            $data['kuota_bulanan'] = 3;
        }

        return DB::transaction(function () use ($data) {
            $skpd = Skpd::create($data);
            
            // Log the creation if user is authenticated
            if (auth()->check()) {
                $this->activityLogService->logUserAction(
                    'SKPD_CREATED',
                    "SKPD '{$skpd->nama_skpd}' telah dibuat",
                    auth()->user()
                );
            }

            return $skpd;
        });
    }

    /**
     * Update SKPD with tracking changes for activity log.
     *
     * @param Skpd $skpd
     * @param array $data
     * @return Skpd
     */
    public function updateSkpd(Skpd $skpd, array $data): Skpd
    {
        return DB::transaction(function () use ($skpd, $data) {
            // Track changes for activity log
            $changes = $this->getChanges($skpd, $data);
            
            $skpd->update($data);

            // Log the update with old and new values if user is authenticated
            if (auth()->check() && !empty($changes)) {
                $this->activityLogService->logSkpdUpdated($skpd, $changes, auth()->user());
            }

            return $skpd->fresh();
        });
    }


    /**
     * Delete SKPD with validation - cannot delete if has active content.
     *
     * @param Skpd $skpd
     * @return bool
     * @throws \Exception
     */
    public function deleteSkpd(Skpd $skpd): bool
    {
        // Check if SKPD has active content (Pending, Approved, or Published)
        if ($skpd->hasActiveContent()) {
            throw new \Exception('SKPD tidak dapat dihapus karena masih memiliki konten aktif.');
        }

        return DB::transaction(function () use ($skpd) {
            $namaSkpd = $skpd->nama_skpd;
            
            // Delete the SKPD
            $result = $skpd->delete();

            // Log the deletion if user is authenticated
            if (auth()->check()) {
                $this->activityLogService->logUserAction(
                    'SKPD_DELETED',
                    "SKPD '{$namaSkpd}' telah dihapus",
                    auth()->user()
                );
            }

            return $result;
        });
    }

    /**
     * Get all SKPDs with their quota compliance status for a given month/year.
     *
     * @param int $month
     * @param int $year
     * @return Collection
     */
    public function getSkpdWithQuotaStatus(int $month, int $year): Collection
    {
        $skpds = Skpd::active()->with(['contents' => function ($query) use ($month, $year) {
            $query->whereMonth('tanggal_publikasi', $month)
                  ->whereYear('tanggal_publikasi', $year)
                  ->where('status', Content::STATUS_APPROVED);
        }])->get();

        return $skpds->map(function ($skpd) use ($month, $year) {
            $approvedCount = $skpd->contents->count();
            $complianceStatus = $this->calculateComplianceStatus($skpd, $month, $year);
            
            return [
                'skpd' => $skpd,
                'approved_count' => $approvedCount,
                'quota' => $skpd->kuota_bulanan,
                'compliance_status' => $complianceStatus,
                'compliance_percentage' => $skpd->kuota_bulanan > 0 
                    ? round(($approvedCount / $skpd->kuota_bulanan) * 100, 2) 
                    : 0,
            ];
        });
    }

    /**
     * Calculate compliance status for a SKPD based on approved content vs quota.
     *
     * @param Skpd $skpd
     * @param int $month
     * @param int $year
     * @return string
     */
    public function calculateComplianceStatus(Skpd $skpd, int $month, int $year): string
    {
        $approvedCount = $skpd->contents()
            ->whereMonth('tanggal_publikasi', $month)
            ->whereYear('tanggal_publikasi', $year)
            ->where('status', Content::STATUS_APPROVED)
            ->count();

        $quota = $skpd->kuota_bulanan;

        if ($quota <= 0) {
            return 'Tidak Ada Kuota';
        }

        $percentage = ($approvedCount / $quota) * 100;

        if ($percentage >= 100) {
            return 'Memenuhi';
        } elseif ($percentage >= 50) {
            return 'Sebagian';
        } else {
            return 'Belum Memenuhi';
        }
    }


    /**
     * Get changes between current SKPD data and new data.
     *
     * @param Skpd $skpd
     * @param array $newData
     * @return array
     */
    protected function getChanges(Skpd $skpd, array $newData): array
    {
        $changes = [];
        $trackableFields = ['nama_skpd', 'website_url', 'email', 'kuota_bulanan', 'status'];

        foreach ($trackableFields as $field) {
            if (isset($newData[$field]) && $skpd->{$field} != $newData[$field]) {
                $changes[$field] = [
                    'old' => $skpd->{$field},
                    'new' => $newData[$field],
                ];
            }
        }

        return $changes;
    }

    /**
     * Get all active SKPDs.
     *
     * @return Collection
     */
    public function getAllActiveSkpd(): Collection
    {
        return Skpd::active()->orderBy('nama_skpd')->get();
    }

    /**
     * Get SKPD by ID with relationships.
     *
     * @param int $id
     * @return Skpd|null
     */
    public function getSkpdById(int $id): ?Skpd
    {
        return Skpd::with(['contents', 'publishers'])->find($id);
    }

    /**
     * Get SKPDs that are not meeting their quota for a given month/year.
     *
     * @param int $month
     * @param int $year
     * @return Collection
     */
    public function getNonCompliantSkpd(int $month, int $year): Collection
    {
        return $this->getSkpdWithQuotaStatus($month, $year)
            ->filter(function ($item) {
                return $item['compliance_status'] === 'Belum Memenuhi';
            });
    }
}
