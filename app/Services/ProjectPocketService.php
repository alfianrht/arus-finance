<?php

namespace App\Services;

use App\Models\ProjectPocketModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

class ProjectPocketService
{
    private BaseConnection $db;

    private ProjectPocketModel $pockets;

    public function __construct(?BaseConnection $db = null, ?ProjectPocketModel $pockets = null)
    {
        $this->db = $db ?? Database::connect();
        $this->pockets = $pockets ?? new ProjectPocketModel();
    }

    public function getByActivity(int $institutionId, int $activityId, bool $activeOnly = false): array
    {
        $builder = $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('deleted_at', null);

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $this->sortPockets($builder->findAll());
    }

    public function hasProjectMode(int $institutionId, int $activityId): bool
    {
        return $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function getMainPocket(int $institutionId, int $activityId): ?array
    {
        $pocket = $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('pocket_type', 'main')
            ->where('deleted_at', null)
            ->first();

        return is_array($pocket) ? $pocket : null;
    }

    public function getExecutionPockets(int $institutionId, int $activityId, bool $activeOnly = false): array
    {
        $builder = $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('pocket_type', 'execution')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC');

        if ($activeOnly) {
            $builder->where('is_active', 1);
        }

        return $this->sortPockets($builder->findAll());
    }

    public function activityRequiresExplicitPocket(int $institutionId, int $activityId): bool
    {
        return $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('pocket_type', 'execution')
            ->where('is_active', 1)
            ->where('deleted_at', null)
            ->countAllResults() > 0;
    }

    public function ensureMainPocket(int $institutionId, array $activity): array
    {
        $existing = $this->getMainPocket($institutionId, (int) $activity['id']);
        if (is_array($existing)) {
            return $existing;
        }

        $payload = [
            'institution_id' => $institutionId,
            'unit_id' => (int) $activity['unit_id'],
            'activity_id' => (int) $activity['id'],
            'name' => 'Kantong Utama',
            'slug' => $this->uniqueSlug($institutionId, (int) $activity['id'], 'kantong-utama'),
            'pocket_type' => 'main',
            'is_active' => 1,
            'notes' => 'Rumah utama kontrak, termin, dan transaksi bawaan kegiatan.',
            'contract_value' => null,
            'contract_terms_count' => null,
        ];

        $this->db->transStart();
        $pocketId = $this->pockets->insert($payload, true);
        if ($pocketId === false) {
            $this->db->transRollback();
            throw new RuntimeException('Kantong utama belum berhasil dibuat.');
        }

        $this->mapLegacyTransactionsToMainPocket($institutionId, (int) $activity['id'], (int) $pocketId);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Kantong utama belum berhasil dibuat.');
        }

        return $this->pockets->find((int) $pocketId);
    }

    public function createExecutionPocket(int $institutionId, array $activity, array $payload): array
    {
        $mainPocket = $this->ensureMainPocket($institutionId, $activity);
        if (! is_array($mainPocket)) {
            throw new RuntimeException('Kantong utama belum tersedia.');
        }

        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Nama kantong pelaksanaan wajib diisi.');
        }

        $insert = [
            'institution_id' => $institutionId,
            'unit_id' => (int) $activity['unit_id'],
            'activity_id' => (int) $activity['id'],
            'name' => $name,
            'slug' => $this->uniqueSlug($institutionId, (int) $activity['id'], url_title($name, '-', true)),
            'pocket_type' => 'execution',
            'is_active' => (int) ($payload['is_active'] ?? 1) === 1 ? 1 : 0,
            'notes' => trim((string) ($payload['notes'] ?? '')) ?: null,
            'contract_value' => null,
            'contract_terms_count' => null,
        ];

        $pocketId = $this->pockets->insert($insert, true);
        if ($pocketId === false) {
            throw new RuntimeException('Kantong pelaksanaan belum berhasil ditambahkan.');
        }

        return $this->pockets->find((int) $pocketId);
    }

    public function updatePocket(array $pocket, array $payload): array
    {
        $name = trim((string) ($payload['name'] ?? $pocket['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Nama kantong wajib diisi.');
        }

        $update = [
            'name' => $name,
            'notes' => trim((string) ($payload['notes'] ?? '')) ?: null,
            'is_active' => (int) ($payload['is_active'] ?? ($pocket['is_active'] ?? 1)) === 1 ? 1 : 0,
        ];

        if (($pocket['pocket_type'] ?? '') === 'main') {
            $update['name'] = 'Kantong Utama';
            $update['contract_value'] = $this->normalizeMoney((string) ($payload['contract_value'] ?? '0')) ?: null;
            $terms = (int) ($payload['contract_terms_count'] ?? 0);
            $update['contract_terms_count'] = $terms > 0 ? $terms : null;
            $update['is_active'] = 1;
        } else {
            $update['slug'] = $this->uniqueSlug((int) $pocket['institution_id'], (int) $pocket['activity_id'], url_title($name, '-', true), (int) $pocket['id']);
        }

        $this->pockets->update((int) $pocket['id'], $update);

        return $this->pockets->find((int) $pocket['id']);
    }

    public function deactivatePocket(array $pocket): void
    {
        if (($pocket['pocket_type'] ?? '') === 'main') {
            throw new RuntimeException('Kantong utama tidak bisa dinonaktifkan.');
        }

        $transactionCount = $this->db->table('transactions')
            ->groupStart()
                ->where('project_pocket_id', (int) $pocket['id'])
                ->orWhere('counter_project_pocket_id', (int) $pocket['id'])
            ->groupEnd()
            ->where('deleted_at', null)
            ->countAllResults();

        if ($transactionCount > 0) {
            throw new RuntimeException('Kantong yang sudah dipakai transaksi tidak bisa dinonaktifkan.');
        }

        $this->pockets->update((int) $pocket['id'], ['is_active' => 0]);
    }

    public function findPocket(int $institutionId, int $activityId, string $slug): ?array
    {
        $pocket = $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('slug', $slug)
            ->where('deleted_at', null)
            ->first();

        return is_array($pocket) ? $pocket : null;
    }

    public function resolvePocketIdForTransaction(int $institutionId, int $activityId, ?int $requestedPocketId): ?int
    {
        if (! $this->hasProjectMode($institutionId, $activityId)) {
            return null;
        }

        $mainPocket = $this->getMainPocket($institutionId, $activityId);
        if (! is_array($mainPocket)) {
            throw new RuntimeException('Kantong utama kegiatan belum tersedia.');
        }

        if ($requestedPocketId !== null && $requestedPocketId > 0) {
            $pocket = $this->pockets
                ->where('institution_id', $institutionId)
                ->where('activity_id', $activityId)
                ->where('id', $requestedPocketId)
                ->where('deleted_at', null)
                ->first();

            if (! is_array($pocket) || (int) ($pocket['is_active'] ?? 0) !== 1) {
                throw new RuntimeException('Kantong proyek yang dipilih tidak valid.');
            }

            return (int) $pocket['id'];
        }

        if (! $this->activityRequiresExplicitPocket($institutionId, $activityId)) {
            return (int) $mainPocket['id'];
        }

        throw new RuntimeException('Pilih kantong proyek terlebih dahulu.');
    }

    public function resolveCounterPocketIdForTransfer(int $institutionId, int $activityId, int $sourcePocketId, ?int $requestedPocketId): ?int
    {
        if ($requestedPocketId === null || $requestedPocketId < 1) {
            return null;
        }

        $pocket = $this->pockets
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('id', $requestedPocketId)
            ->where('deleted_at', null)
            ->first();

        if (! is_array($pocket) || (int) ($pocket['is_active'] ?? 0) !== 1) {
            throw new RuntimeException('Kantong tujuan tidak valid.');
        }

        if ((int) $pocket['id'] === $sourcePocketId) {
            throw new RuntimeException('Kantong asal dan tujuan tidak boleh sama.');
        }

        return (int) $pocket['id'];
    }

    public function buildActivityPocketGroups(int $institutionId, array $activities): array
    {
        if ($activities === []) {
            return [];
        }

        $activityIds = array_values(array_unique(array_map(static fn(array $activity): int => (int) ($activity['id'] ?? 0), $activities)));
        $rows = $this->pockets
            ->where('institution_id', $institutionId)
            ->whereIn('activity_id', $activityIds)
            ->where('deleted_at', null)
            ->findAll();
        $rows = $this->sortPockets($rows);

        $groups = [];
        foreach ($rows as $row) {
            $activityId = (int) $row['activity_id'];
            $groups[$activityId] = $groups[$activityId] ?? [
                'activity_id' => $activityId,
                'is_project_mode' => true,
                'requires_explicit' => false,
                'default_pocket_id' => null,
                'options' => [],
            ];

            if (($row['pocket_type'] ?? '') === 'main' && $groups[$activityId]['default_pocket_id'] === null) {
                $groups[$activityId]['default_pocket_id'] = (int) $row['id'];
            }

            if (($row['pocket_type'] ?? '') === 'execution' && (int) ($row['is_active'] ?? 0) === 1) {
                $groups[$activityId]['requires_explicit'] = true;
            }

            if ((int) ($row['is_active'] ?? 0) !== 1) {
                continue;
            }

            $groups[$activityId]['options'][] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'slug' => (string) $row['slug'],
                'pocket_type' => (string) $row['pocket_type'],
                'type_label' => ($row['pocket_type'] ?? '') === 'main' ? 'Utama' : 'Pelaksanaan',
            ];
        }

        return $groups;
    }

    private function mapLegacyTransactionsToMainPocket(int $institutionId, int $activityId, int $mainPocketId): void
    {
        $this->db->table('transactions')
            ->where('institution_id', $institutionId)
            ->where('activity_id', $activityId)
            ->where('deleted_at', null)
            ->where('project_pocket_id', null)
            ->update(['project_pocket_id' => $mainPocketId]);
    }

    private function uniqueSlug(int $institutionId, int $activityId, string $baseSlug, ?int $ignoreId = null): string
    {
        $baseSlug = trim($baseSlug) !== '' ? trim($baseSlug) : 'kantong';
        $slug = $baseSlug;
        $suffix = 2;

        while (true) {
            $builder = $this->pockets
                ->where('institution_id', $institutionId)
                ->where('activity_id', $activityId)
                ->where('slug', $slug)
                ->where('deleted_at', null);

            if ($ignoreId !== null) {
                $builder->where('id !=', $ignoreId);
            }

            if ($builder->first() === null) {
                return $slug;
            }

            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }
    }

    private function sortPockets(array $rows): array
    {
        usort($rows, static function (array $left, array $right): int {
            $leftWeight = ($left['pocket_type'] ?? '') === 'main' ? 0 : 1;
            $rightWeight = ($right['pocket_type'] ?? '') === 'main' ? 0 : 1;

            if ($leftWeight !== $rightWeight) {
                return $leftWeight <=> $rightWeight;
            }

            return ((int) ($left['id'] ?? 0)) <=> ((int) ($right['id'] ?? 0));
        });

        return $rows;
    }

    private function normalizeMoney(string $raw): float
    {
        $normalized = preg_replace('/[^0-9,.-]/', '', $raw) ?? '0';
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return (float) $normalized;
    }
}
