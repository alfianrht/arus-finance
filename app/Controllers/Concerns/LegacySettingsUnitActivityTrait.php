<?php

namespace App\Controllers\Concerns;

use App\Models\ActivityModel;
use App\Models\UnitModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Database;

trait LegacySettingsUnitActivityTrait
{
    public function masterUnitProgram(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Unit / Program';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['units'] = $this->loadUnitProgramRows();

        return view('pages/master/units', $data);
    }

    public function tambahUnitProgram(): string
    {
        return view('pages/master/form', $this->buildUnitFormData());
    }

    public function editUnitProgram(string $slug): string
    {
        $unit = (new UnitModel())->where('slug', $slug)->where('deleted_at', null)->first();

        if (! is_array($unit)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildUnitFormData($unit, true));
    }

    public function simpanUnitProgram(): RedirectResponse
    {
        return $this->persistUnitProgram();
    }

    public function updateUnitProgram(string $slug): RedirectResponse
    {
        return $this->persistUnitProgram($slug);
    }

    public function hapusUnitProgram(string $slug): RedirectResponse
    {
        $unitModel = new UnitModel();
        $unit = $unitModel->where('slug', $slug)->first();

        if (! is_array($unit)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $activityCount = (new ActivityModel())
            ->where('unit_id', (int) $unit['id'])
            ->where('deleted_at', null)
            ->countAllResults();

        if ($activityCount > 0) {
            return redirect()->to(site_url('pengaturan/unit-program'))
                ->with('error', 'Unit <strong>' . esc($unit['name']) . '</strong> tidak bisa dihapus karena masih memiliki ' . $activityCount . ' kegiatan. Hapus kegiatan terlebih dahulu.');
        }

        $unitModel->delete((int) $unit['id']);

        return redirect()->to(site_url('pengaturan/unit-program'))
            ->with('success', 'Unit <strong>' . esc($unit['name']) . '</strong> berhasil dihapus.');
    }

    public function masterKegiatan(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Kegiatan';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['activitySummaries'] = $this->loadActivityRows();

        return view('pages/master/activities', $data);
    }

    public function tambahKegiatan(): string
    {
        return view('pages/master/form', $this->buildActivityFormData());
    }

    public function editKegiatan(string $slug): string
    {
        $activity = (new ActivityModel())->where('slug', $slug)->where('deleted_at', null)->first();

        if (! is_array($activity)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildActivityFormData($activity, true));
    }

    public function simpanKegiatan(): RedirectResponse
    {
        return $this->persistActivity();
    }

    public function updateKegiatan(string $slug): RedirectResponse
    {
        return $this->persistActivity($slug);
    }

    public function hapusKegiatan(string $slug): RedirectResponse
    {
        $model = new ActivityModel();
        $activity = $model->where('slug', $slug)->first();

        if (! is_array($activity)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->delete((int) $activity['id']);

        return redirect()->to(site_url('pengaturan/kegiatan'))
            ->with('success', 'Kegiatan <strong>' . esc($activity['name']) . '</strong> berhasil dihapus.');
    }

    private function loadUnitProgramRows(): array
    {
        $unitModel = new UnitModel();
        $activityModel = new ActivityModel();
        $db = Database::connect();
        $units = $unitModel
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($units as &$unit) {
            $unitActivities = $activityModel
                ->where('unit_id', $unit['id'])
                ->where('deleted_at', null)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();

            $income = (float) $db->table('transactions')
                ->where('institution_id', $this->currentInstitutionId())
                ->where('unit_id', (int) $unit['id'])
                ->where('deleted_at', null)
                ->where('type', 'masuk')
                ->selectSum('amount')
                ->get()
                ->getRow()
                ->amount ?? 0;

            $expenseMain = (float) $db->table('transactions')
                ->where('institution_id', $this->currentInstitutionId())
                ->where('unit_id', (int) $unit['id'])
                ->where('deleted_at', null)
                ->whereIn('type', ['keluar', 'honor'])
                ->select('SUM(amount + admin_fee) as total')
                ->get()
                ->getRow()
                ->total ?? 0;

            $transferOut = (float) $db->table('transactions')
                ->where('institution_id', $this->currentInstitutionId())
                ->where('unit_id', (int) $unit['id'])
                ->where('deleted_at', null)
                ->where('type', 'pindah')
                ->select('SUM(amount + admin_fee) as total')
                ->get()
                ->getRow()
                ->total ?? 0;

            $unit['activities'] = $unitActivities;
            $unit['income'] = $income;
            $unit['expense'] = $expenseMain + $transferOut;
            $unit['surplus'] = $unit['income'] - $unit['expense'];
            $unit['related_balance'] = $unit['surplus'];
            $unit['status_label'] = (int) ($unit['is_active'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif';
            $unit['note'] = trim((string) ($unit['note'] ?? '')) !== '' ? (string) $unit['note'] : 'Unit aktif siap dipakai untuk pencatatan.';
            $unit['quick_activity_name'] = $unitActivities[0]['name'] ?? 'Belum ada kegiatan';
            $unit['detail_url'] = site_url('pengaturan/unit-program/' . $unit['slug'] . '/edit');
        }
        unset($unit);

        return $units;
    }

    private function buildUnitFormData(?array $unit = null, bool $isEdit = false): array
    {
        $existingUnits = $this->loadUnitProgramRows();
        $sortOrder = $isEdit
            ? (string) ($unit['sort_order'] ?? 0)
            : (string) (count($existingUnits) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($unit['name'] ?? 'Unit') : 'Tambah Unit / Program',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/unit-program'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Unit / Program',
            'formDescription' => 'Data ini sekarang tersimpan ke database dan akan dipakai sebagai fondasi konteks aktif serta relasi kegiatan.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Unit',
            'formAction' => $isEdit && isset($unit['slug'])
                ? site_url('pengaturan/unit-program/' . $unit['slug'])
                : site_url('pengaturan/unit-program'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Unit / Program', 'value' => old('name', $unit['name'] ?? '')],
                ['type' => 'text', 'name' => 'short_name', 'label' => 'Singkatan Unit', 'value' => old('short_name', $unit['short_name'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($unit['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
                ['type' => 'textarea', 'name' => 'note', 'label' => 'Catatan Singkat', 'value' => old('note', $unit['note'] ?? '')],
            ],
        ];
    }

    private function persistUnitProgram(?string $slug = null): RedirectResponse
    {
        $unitModel = new UnitModel();
        $isEdit = $slug !== null;
        $current = null;

        if ($isEdit) {
            $current = $unitModel->where('slug', $slug)->where('deleted_at', null)->first();

            if (! is_array($current)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $name = trim((string) $this->request->getPost('name'));
        $shortName = strtoupper(trim((string) $this->request->getPost('short_name')));
        $sortOrder = (int) $this->request->getPost('sort_order');
        $status = (string) $this->request->getPost('status');

        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama unit / program wajib diisi.');
        }

        if ($shortName === '') {
            return redirect()->back()->withInput()->with('error', 'Singkatan unit wajib diisi.');
        }

        $baseSlug = url_title($name, '-', true);
        $finalSlug = $baseSlug;
        $suffix = 2;

        while (true) {
            $conflict = $unitModel->where('slug', $finalSlug)->where('deleted_at', null)->first();

            if (! is_array($conflict) || ($isEdit && (int) $conflict['id'] === (int) $current['id'])) {
                break;
            }

            $finalSlug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $institutionId = (int) ($this->session->get('auth_institution_id') ?? 1);

        $payload = [
            'institution_id' => $institutionId,
            'name' => $name,
            'slug' => $finalSlug,
            'short_name' => $shortName,
            'is_active' => $status === 'Aktif' ? 1 : 0,
            'sort_order' => $sortOrder > 0 ? $sortOrder : 1,
        ];

        if ($isEdit) {
            $unitModel->update((int) $current['id'], $payload);

            return redirect()->to(site_url('pengaturan/unit-program/' . $finalSlug . '/edit'))
                ->with('success', 'Unit / Program berhasil diperbarui.');
        }

        $unitModel->insert($payload);

        return redirect()->to(site_url('pengaturan/unit-program/' . $payload['slug'] . '/edit'))
            ->with('success', 'Unit / Program berhasil ditambahkan.');
    }

    private function loadActivityRows(): array
    {
        $activityModel = new ActivityModel();
        $unitMap = [];
        foreach ($this->loadUnitProgramRows() as $unit) {
            $unitMap[(int) $unit['id']] = $unit;
        }

        $unitIds = array_keys($unitMap) ?: [0];
        $activities = $activityModel
            ->whereIn('unit_id', $unitIds)
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($activities as &$activity) {
            $unit = $unitMap[(int) $activity['unit_id']] ?? null;
            $activity['slug'] = $activity['slug'] ?? (string) $activity['id'];
            $activity['unit_name'] = $unit['name'] ?? 'Tanpa Unit';
            $activity['income'] = 0;
            $activity['expense'] = 0;
            $activity['related_balance'] = 0;
            $activity['related_accounts'] = [];
        }
        unset($activity);

        return $activities;
    }

    private function buildActivityFormData(?array $activity = null, bool $isEdit = false): array
    {
        $units = $this->loadUnitProgramRows();
        $selectedUnitId = (string) old('unit_id', $activity['unit_id'] ?? ($units[0]['id'] ?? ''));
        $sortOrder = $isEdit ? (string) ($activity['sort_order'] ?? 0) : (string) (count($this->loadActivityRows()) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($activity['name'] ?? 'Kegiatan') : 'Tambah Kegiatan',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/kegiatan'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Kegiatan',
            'formDescription' => 'Kegiatan tersimpan ke database dan menjadi konteks aktif level 2 saat pencatatan.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Kegiatan',
            'formAction' => $isEdit ? site_url('pengaturan/kegiatan/' . $activity['slug']) : site_url('pengaturan/kegiatan'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'select', 'name' => 'unit_id', 'label' => 'Unit Induk', 'value' => $selectedUnitId, 'options' => $this->buildSelectOptions($units)],
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Kegiatan', 'value' => old('name', $activity['name'] ?? '')],
                ['type' => 'text', 'name' => 'short_name', 'label' => 'Singkatan Kegiatan', 'value' => old('short_name', $activity['short_name'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($activity['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
            ],
        ];
    }

    private function persistActivity(?string $slug = null): RedirectResponse
    {
        $model = new ActivityModel();
        $current = null;

        if ($slug !== null) {
            $current = $model->where('slug', $slug)->where('deleted_at', null)->first();
            if (! is_array($current)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $unitId = (int) $this->request->getPost('unit_id');
        $name = trim((string) $this->request->getPost('name'));
        $shortName = strtoupper(trim((string) $this->request->getPost('short_name')));

        if ($unitId <= 0 || $name === '' || $shortName === '') {
            return redirect()->back()->withInput()->with('error', 'Unit induk, nama kegiatan, dan singkatan wajib diisi.');
        }

        $payload = [
            'unit_id' => $unitId,
            'name' => $name,
            'slug' => $this->uniqueSlug(new ActivityModel(), $name, $current['id'] ?? null),
            'short_name' => $shortName,
            'is_active' => $this->request->getPost('status') === 'Aktif' ? 1 : 0,
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/kegiatan/' . $payload['slug'] . '/edit'))->with('success', 'Kegiatan berhasil diperbarui.');
        }

        $model->insert($payload);
        return redirect()->to(site_url('pengaturan/kegiatan/' . $payload['slug'] . '/edit'))->with('success', 'Kegiatan berhasil ditambahkan.');
    }
}
