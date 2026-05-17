<?php

namespace App\Controllers;

use App\Models\ActivityModel;
use App\Models\UnitModel;
use CodeIgniter\HTTP\RedirectResponse;

class ActiveContextController extends BaseController
{
    public function update(): RedirectResponse
    {
        $units = $this->loadUnitsWithActivities();
        $unitSlug = trim((string) $this->request->getPost('unit'));
        $activitySlug = trim((string) $this->request->getPost('kegiatan'));

        foreach ($units as $unit) {
            if (($unit['slug'] ?? '') !== $unitSlug) {
                continue;
            }

            foreach (($unit['activities'] ?? []) as $activity) {
                if (($activity['slug'] ?? '') !== $activitySlug) {
                    continue;
                }

                $this->storeActiveContextSelection([
                    'unit_id' => (int) ($unit['id'] ?? 0),
                    'activity_id' => (int) ($activity['id'] ?? 0),
                    'unit_slug' => (string) ($unit['slug'] ?? ''),
                    'activity_slug' => (string) ($activity['slug'] ?? ''),
                    'unit_name' => (string) ($unit['name'] ?? 'Tanpa Unit'),
                    'activity_name' => (string) ($activity['name'] ?? 'Tanpa Kegiatan'),
                ]);

                return redirect()->to($this->resolveRedirectTarget())->with('success', 'Konteks aktif diperbarui.');
            }
        }

        return redirect()->to($this->resolveRedirectTarget())->with('error', 'Konteks yang dipilih tidak valid.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadUnitsWithActivities(): array
    {
        $units = (new UnitModel())
            ->where('institution_id', (int) ($this->session->get('auth_institution_id') ?? 1))
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $activityModel = new ActivityModel();

        foreach ($units as &$unit) {
            $unit['activities'] = $activityModel
                ->where('unit_id', (int) $unit['id'])
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();
        }
        unset($unit);

        return $units;
    }

    private function resolveRedirectTarget(): string
    {
        $target = trim((string) $this->request->getPost('redirect_to'));

        if ($target === '') {
            return site_url('beranda');
        }

        $path = parse_url($target, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return site_url('beranda');
        }

        return site_url(ltrim($path, '/'));
    }
}
