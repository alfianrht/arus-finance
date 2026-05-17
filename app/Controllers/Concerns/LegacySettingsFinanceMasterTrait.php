<?php

namespace App\Controllers\Concerns;

use App\Models\AccountModel;
use App\Models\ReceiverModel;
use App\Models\TransactionCategoryModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

trait LegacySettingsFinanceMasterTrait
{
    public function masterRekeningDompet(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Rekening / Dompet';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['accountSummaries'] = $this->loadAccountRows();

        return view('pages/master/accounts', $data);
    }

    public function tambahRekeningDompet(): string
    {
        return view('pages/master/form', $this->buildAccountFormData());
    }

    public function editRekeningDompet(string $slug): string
    {
        $account = (new AccountModel())->where('slug', $slug)->where('deleted_at', null)->first();

        if (! is_array($account)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildAccountFormData($account, true));
    }

    public function simpanRekeningDompet(): RedirectResponse
    {
        return $this->persistAccount();
    }

    public function updateRekeningDompet(string $slug): RedirectResponse
    {
        return $this->persistAccount($slug);
    }

    public function hapusRekeningDompet(string $slug): RedirectResponse
    {
        $model = new AccountModel();
        $account = $model->where('slug', $slug)->first();

        if (! is_array($account)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->delete((int) $account['id']);

        return redirect()->to(site_url('pengaturan/rekening-dompet'))
            ->with('success', 'Rekening <strong>' . esc($account['name']) . '</strong> berhasil dihapus.');
    }

    public function masterKategoriBiaya(): string
    {
        $data = [];
        $data['pageTitle'] = 'Master Kategori Transaksi';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['transactionCategories'] = $this->loadTransactionCategoryRows();

        return view('pages/master/categories', $data);
    }

    public function tambahKategoriBiaya(): string
    {
        return view('pages/master/form', $this->buildTransactionCategoryFormData());
    }

    public function editKategoriBiaya(string $id): string
    {
        $category = (new TransactionCategoryModel())->find((int) $id);

        if (! is_array($category)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildTransactionCategoryFormData($category, true));
    }

    public function simpanKategoriBiaya(): RedirectResponse
    {
        return $this->persistTransactionCategory();
    }

    public function updateKategoriBiaya(string $id): RedirectResponse
    {
        return $this->persistTransactionCategory($id);
    }

    public function hapusKategoriBiaya(string $id): RedirectResponse
    {
        $model = new TransactionCategoryModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/kategori-biaya'))
            ->with('success', 'Kategori <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterPenerima(): string
    {
        $data = [];
        $data['pageTitle'] = 'Penerima';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['receivers'] = $this->loadReceiverRows();

        return view('pages/master/receivers', $data);
    }

    public function tambahPenerima(): string
    {
        return view('pages/master/form', $this->buildReceiverFormData());
    }

    public function editPenerima(string $id): string
    {
        $receiver = (new ReceiverModel())->find((int) $id);

        if (! is_array($receiver)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildReceiverFormData($receiver, true));
    }

    public function simpanPenerima(): RedirectResponse
    {
        return $this->persistReceiver();
    }

    public function updatePenerima(string $id): RedirectResponse
    {
        return $this->persistReceiver($id);
    }

    public function hapusPenerima(string $id): RedirectResponse
    {
        $model = new ReceiverModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/penerima'))
            ->with('success', 'Penerima <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    private function loadAccountRows(): array
    {
        $accountModel = new AccountModel();
        $positionMap = $this->reportPositionNameMap();
        $openingByPosition = $this->openingBalanceTotalByPosition();

        $accounts = $accountModel
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($accounts as &$account) {
            $positionId = (int) ($account['report_position_id'] ?? 0);
            $account['balance'] = (float) ($openingByPosition[$positionId] ?? 0);
            $account['report_position_name'] = $positionMap[$positionId] ?? 'Belum dipilih';
            $account['preview_activity'] = $account['report_position_name'];
            $account['movement_count'] = 0;
            $account['detail_url'] = site_url('pengaturan/rekening-dompet/' . $account['slug'] . '/edit');
        }
        unset($account);

        return $accounts;
    }

    private function buildAccountFormData(?array $account = null, bool $isEdit = false): array
    {
        $positions = $this->loadReportPositionRows('Neraca');
        $sortOrder = $isEdit ? (string) ($account['sort_order'] ?? 0) : (string) (count($this->loadAccountRows()) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($account['name'] ?? 'Rekening') : 'Tambah Rekening / Dompet',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/rekening-dompet'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Rekening / Dompet',
            'formDescription' => 'Master rekening sekarang langsung tersimpan ke database dan menyimpan pos laporan terkait.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Rekening',
            'formAction' => $isEdit ? site_url('pengaturan/rekening-dompet/' . $account['slug']) : site_url('pengaturan/rekening-dompet'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Rekening / Dompet', 'value' => old('name', $account['name'] ?? '')],
                ['type' => 'select', 'name' => 'kind', 'label' => 'Jenis Penyimpanan Dana', 'value' => old('kind', $account['kind'] ?? 'Rekening'), 'options' => ['Rekening', 'Dompet Digital', 'Kas Tunai']],
                ['type' => 'text', 'name' => 'mark', 'label' => 'Label / Singkatan', 'value' => old('mark', $account['mark'] ?? '')],
                ['type' => 'text', 'name' => 'account_number', 'label' => 'Nomor Rekening', 'value' => old('account_number', $account['account_number'] ?? '')],
                ['type' => 'select', 'name' => 'report_position_id', 'label' => 'Pos Laporan Terkait', 'value' => (string) old('report_position_id', $account['report_position_id'] ?? ($positions[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($positions)],
                ['type' => 'file', 'name' => 'logo_file', 'label' => 'Logo Rekening (Opsional)', 'value' => $account['logo_asset'] ?? ''],
                ['type' => 'textarea', 'name' => 'note', 'label' => 'Catatan Penggunaan', 'value' => old('note', $account['note'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($account['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
            ],
        ];
    }

    private function persistAccount(?string $slug = null): RedirectResponse
    {
        $model = new AccountModel();
        $current = null;

        if ($slug !== null) {
            $current = $model->where('slug', $slug)->where('deleted_at', null)->first();
            if (! is_array($current)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $name = trim((string) $this->request->getPost('name'));
        $mark = strtoupper(trim((string) $this->request->getPost('mark')));
        if ($name === '' || $mark === '') {
            return redirect()->back()->withInput()->with('error', 'Nama rekening dan label singkatan wajib diisi.');
        }

        $logoAsset = $current['logo_asset'] ?? '';
        $logoFile = $this->request->getFile('logo_file');
        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/accounts';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $newName = $logoFile->getRandomName();
            $logoFile->move($uploadDir, $newName);
            $logoAsset = 'uploads/accounts/' . $newName;

            $oldLogo = $current['logo_asset'] ?? '';
            if ($oldLogo !== '' && is_file(FCPATH . $oldLogo)) {
                @unlink(FCPATH . $oldLogo);
            }
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'slug' => $this->uniqueSlug(new AccountModel(), $name, $current['id'] ?? null),
            'kind' => (string) $this->request->getPost('kind'),
            'mark' => $mark,
            'account_number' => trim((string) $this->request->getPost('account_number')),
            'logo_asset' => $logoAsset,
            'note' => trim((string) $this->request->getPost('note')),
            'report_position_id' => (int) $this->request->getPost('report_position_id') ?: null,
            'is_active' => $this->request->getPost('status') === 'Aktif' ? 1 : 0,
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/rekening-dompet/' . $payload['slug'] . '/edit'))->with('success', 'Rekening / dompet berhasil diperbarui.');
        }

        $model->insert($payload);
        return redirect()->to(site_url('pengaturan/rekening-dompet/' . $payload['slug'] . '/edit'))->with('success', 'Rekening / dompet berhasil ditambahkan.');
    }

    private function loadTransactionCategoryRows(): array
    {
        $positionMap = $this->reportPositionNameMap();
        $rows = (new TransactionCategoryModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['type'] = $row['kind'];
            $row['order'] = (int) $row['sort_order'];
            $row['report_position_name'] = $positionMap[(int) ($row['report_position_id'] ?? 0)] ?? 'Belum dipilih';
            $row['note'] = $row['chip_label'] ?: 'Kategori transaksi untuk ' . strtolower($row['kind']) . '.';
        }
        unset($row);

        return $rows;
    }

    private function buildTransactionCategoryFormData(?array $category = null, bool $isEdit = false): array
    {
        $positions = $this->loadReportPositionRows('Laba Rugi');
        $sortOrder = $isEdit ? (string) ($category['sort_order'] ?? 0) : (string) (count($this->loadTransactionCategoryRows()) + 1);

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($category['name'] ?? 'Kategori') : 'Tambah Kategori Transaksi',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/kategori-biaya'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Kategori Transaksi',
            'formDescription' => 'Kategori transaksi langsung menentukan pilihan di form uang masuk atau uang keluar.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori',
            'formAction' => $isEdit ? site_url('pengaturan/kategori-biaya/' . $category['id']) : site_url('pengaturan/kategori-biaya'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Kategori', 'value' => old('name', $category['name'] ?? '')],
                ['type' => 'select', 'name' => 'kind', 'label' => 'Jenis Transaksi', 'value' => old('kind', $category['kind'] ?? 'Keluar'), 'options' => ['Masuk', 'Keluar']],
                ['type' => 'select', 'name' => 'report_position_id', 'label' => 'Pos Laporan Terkait', 'value' => (string) old('report_position_id', $category['report_position_id'] ?? ($positions[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($positions)],
                ['type' => 'text', 'name' => 'chip_label', 'label' => 'Label Chip Cepat', 'value' => old('chip_label', $category['chip_label'] ?? '')],
                ['type' => 'select', 'name' => 'is_quick', 'label' => 'Muncul sebagai kategori cepat', 'value' => old('is_quick', ((int) ($category['is_quick'] ?? 0) === 1 ? 'Ya' : 'Tidak')), 'options' => ['Ya', 'Tidak']],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($category['is_active'] ?? 1) === 1 ? 'Aktif' : 'Nonaktif')), 'options' => ['Aktif', 'Nonaktif']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $sortOrder)],
            ],
        ];
    }

    private function persistTransactionCategory(?string $id = null): RedirectResponse
    {
        $model = new TransactionCategoryModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama kategori wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'kind' => (string) $this->request->getPost('kind'),
            'report_position_id' => (int) $this->request->getPost('report_position_id') ?: null,
            'is_quick' => $this->request->getPost('is_quick') === 'Ya' ? 1 : 0,
            'chip_label' => trim((string) $this->request->getPost('chip_label')),
            'is_active' => $this->request->getPost('status') === 'Aktif' ? 1 : 0,
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/kategori-biaya/' . $current['id'] . '/edit'))->with('success', 'Kategori transaksi berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/kategori-biaya/' . $newId . '/edit'))->with('success', 'Kategori transaksi berhasil ditambahkan.');
    }

    private function loadReceiverRows(): array
    {
        $rows = (new ReceiverModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['note'] = $row['notes'] ?? '';
        }
        unset($row);

        return $rows;
    }

    private function buildReceiverFormData(?array $receiver = null, bool $isEdit = false): array
    {
        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($receiver['name'] ?? 'Penerima') : 'Tambah Penerima',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/penerima'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Penerima',
            'formDescription' => 'Penerima dipakai sebagai daftar kontak, vendor, dan pihak terkait saat transaksi keluar dicatat.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Penerima',
            'formAction' => $isEdit ? site_url('pengaturan/penerima/' . $receiver['id']) : site_url('pengaturan/penerima'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Penerima / Kontak', 'value' => old('name', $receiver['name'] ?? '')],
                ['type' => 'select', 'name' => 'type', 'label' => 'Jenis Kontak', 'value' => old('type', $receiver['type'] ?? 'Vendor'), 'options' => ['Tim Internal', 'Vendor', 'Klien', 'Lainnya']],
                ['type' => 'text', 'name' => 'nik', 'label' => 'NIK (Opsional)', 'value' => old('nik', $receiver['nik'] ?? '')],
                ['type' => 'text', 'name' => 'npwp', 'label' => 'NPWP (Opsional)', 'value' => old('npwp', $receiver['npwp'] ?? '')],
                ['type' => 'text', 'name' => 'bank_account', 'label' => 'Informasi Rekening (Opsional)', 'value' => old('bank_account', $receiver['bank_account'] ?? '')],
                ['type' => 'textarea', 'name' => 'notes', 'label' => 'Catatan', 'value' => old('notes', $receiver['notes'] ?? '')],
            ],
        ];
    }

    private function persistReceiver(?string $id = null): RedirectResponse
    {
        $model = new ReceiverModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama penerima wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'type' => (string) $this->request->getPost('type'),
            'nik' => trim((string) $this->request->getPost('nik')),
            'npwp' => trim((string) $this->request->getPost('npwp')),
            'bank_account' => trim((string) $this->request->getPost('bank_account')),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/penerima/' . $current['id'] . '/edit'))->with('success', 'Penerima berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/penerima/' . $newId . '/edit'))->with('success', 'Penerima berhasil ditambahkan.');
    }
}
