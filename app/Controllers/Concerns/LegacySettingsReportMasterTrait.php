<?php

namespace App\Controllers\Concerns;

use App\Models\BookPeriodModel;
use App\Models\OpeningBalanceModel;
use App\Models\ReportPositionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

trait LegacySettingsReportMasterTrait
{
    public function masterPosLaporan(): string
    {
        $data = [];
        $data['pageTitle'] = 'Pos Laporan';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['reportPositions'] = $this->loadReportPositionRows();
        $data['reportGroups'] = [
            ['name' => 'Laba Rugi'],
            ['name' => 'Neraca'],
        ];

        return view('pages/master/report_positions', $data);
    }

    public function tambahPosLaporan(): string
    {
        return view('pages/master/form', $this->buildReportPositionFormData());
    }

    public function editPosLaporan(string $id): string
    {
        $reportPosition = (new ReportPositionModel())->find((int) $id);

        if (! is_array($reportPosition)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildReportPositionFormData($reportPosition, true));
    }

    public function simpanPosLaporan(): RedirectResponse
    {
        return $this->persistReportPosition();
    }

    public function updatePosLaporan(string $id): RedirectResponse
    {
        return $this->persistReportPosition($id);
    }

    public function hapusPosLaporan(string $id): RedirectResponse
    {
        $model = new ReportPositionModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/pos-laporan'))
            ->with('success', 'Pos Laporan <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterTahunBuku(): string
    {
        $data = [];
        $data['pageTitle'] = 'Tahun Buku';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['bookPeriods'] = $this->loadBookPeriodRows();

        return view('pages/master/book_periods', $data);
    }

    public function tambahTahunBuku(): string
    {
        return view('pages/master/form', $this->buildBookPeriodFormData());
    }

    public function editTahunBuku(string $slug): string
    {
        $bookPeriod = (new BookPeriodModel())->where('slug', $slug)->first();

        if (! is_array($bookPeriod)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildBookPeriodFormData($bookPeriod, true));
    }

    public function simpanTahunBuku(): RedirectResponse
    {
        return $this->persistBookPeriod();
    }

    public function updateTahunBuku(string $slug): RedirectResponse
    {
        return $this->persistBookPeriod($slug);
    }

    public function hapusTahunBuku(string $slug): RedirectResponse
    {
        $model = new BookPeriodModel();
        $item = $model->where('slug', $slug)->first();
        if (! is_array($item)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/tahun-buku'))
            ->with('success', 'Tahun Buku <strong>' . esc($item['name']) . '</strong> berhasil dihapus.');
    }

    public function masterSaldoAwal(): string
    {
        $data = [];
        $data['pageTitle'] = 'Saldo Awal';
        $data['activeNav'] = 'beranda';
        $data['backUrl'] = site_url('pengaturan');
        $data['openingBalances'] = $this->loadOpeningBalanceRows();
        $data['bookPeriods'] = $this->loadBookPeriodRows();

        return view('pages/master/opening_balances', $data);
    }

    public function tambahSaldoAwal(): string
    {
        return view('pages/master/form', $this->buildOpeningBalanceFormData());
    }

    public function editSaldoAwal(string $id): string
    {
        $openingBalance = (new OpeningBalanceModel())->find((int) $id);

        if (! is_array($openingBalance)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('pages/master/form', $this->buildOpeningBalanceFormData($openingBalance, true));
    }

    public function simpanSaldoAwal(): RedirectResponse
    {
        return $this->persistOpeningBalance();
    }

    public function updateSaldoAwal(string $id): RedirectResponse
    {
        return $this->persistOpeningBalance($id);
    }

    public function hapusSaldoAwal(string $id): RedirectResponse
    {
        $model = new OpeningBalanceModel();
        $item = $model->find((int) $id);
        if (! is_array($item)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $model->delete((int) $item['id']);
        return redirect()->to(site_url('pengaturan/saldo-awal'))
            ->with('success', 'Saldo Awal <strong>' . esc($item['label'] ?? 'item') . '</strong> berhasil dihapus.');
    }

    private function loadReportPositionRows(?string $group = null): array
    {
        $model = new ReportPositionModel();
        $builder = $model
            ->where('institution_id', $this->currentInstitutionId())
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC');
        if ($group !== null) {
            $builder = $builder->where('group', $group);
        }
        $rows = $builder->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['code'] = 'POS-' . str_pad((string) $row['id'], 3, '0', STR_PAD_LEFT);
            $row['note'] = 'Pos laporan untuk kelompok ' . strtolower($row['group']) . '.';
            $row['normal_balance'] = $this->normalBalanceForKind($row['kind']);
        }
        unset($row);

        return $rows;
    }

    private function buildReportPositionFormData(?array $position = null, bool $isEdit = false): array
    {
        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($position['name'] ?? 'Pos') : 'Tambah Pos Laporan',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/pos-laporan'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Pos Laporan',
            'formDescription' => 'Pos laporan menjadi fondasi akhir untuk laporan tahunan, tetapi tetap dikelola sederhana dari sekarang.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Pos',
            'formAction' => $isEdit ? site_url('pengaturan/pos-laporan/' . $position['id']) : site_url('pengaturan/pos-laporan'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Pos Laporan', 'value' => old('name', $position['name'] ?? '')],
                ['type' => 'select', 'name' => 'group', 'label' => 'Kelompok Laporan', 'value' => old('group', $position['group'] ?? 'Laba Rugi'), 'options' => ['Laba Rugi', 'Neraca']],
                ['type' => 'select', 'name' => 'kind', 'label' => 'Jenis Pos', 'value' => old('kind', $position['kind'] ?? 'Beban'), 'options' => ['Pendapatan', 'Beban', 'Aset', 'Kewajiban', 'Modal']],
                ['type' => 'number', 'name' => 'sort_order', 'label' => 'Urutan Tampil', 'value' => old('sort_order', $position['sort_order'] ?? (count($this->loadReportPositionRows()) + 1))],
            ],
        ];
    }

    private function persistReportPosition(?string $id = null): RedirectResponse
    {
        $model = new ReportPositionModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '') {
            return redirect()->back()->withInput()->with('error', 'Nama pos laporan wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'group' => (string) $this->request->getPost('group'),
            'kind' => (string) $this->request->getPost('kind'),
            'sort_order' => max(1, (int) $this->request->getPost('sort_order')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/pos-laporan/' . $current['id'] . '/edit'))->with('success', 'Pos laporan berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/pos-laporan/' . $newId . '/edit'))->with('success', 'Pos laporan berhasil ditambahkan.');
    }

    private function getActiveBookPeriodId(int $institutionId): ?int
    {
        $activePeriod = (new BookPeriodModel())
            ->where('institution_id', $institutionId)
            ->where('is_active', true)
            ->first();
        return $activePeriod['id'] ?? null;
    }

    private function loadBookPeriodRows(): array
    {
        $rows = (new BookPeriodModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->orderBy('start_date', 'DESC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['start'] = $row['start_date'];
            $row['end'] = $row['end_date'];
            $row['status'] = (int) $row['is_locked'] === 1 ? 'Ditutup' : ((int) $row['is_active'] === 1 ? 'Aktif' : 'Draft');
            $row['note'] = (int) $row['is_locked'] === 1 ? 'Periode sudah ditutup.' : 'Periode buku aktif untuk fondasi laporan.';
        }
        unset($row);

        return $rows;
    }

    private function buildBookPeriodFormData(?array $period = null, bool $isEdit = false): array
    {
        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($period['name'] ?? 'Tahun Buku') : 'Tambah Tahun Buku',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/tahun-buku'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Tahun Buku',
            'formDescription' => 'Tahun buku mengikat saldo awal dan nanti akan menjadi pintu masuk filter laporan.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Tahun Buku',
            'formAction' => $isEdit ? site_url('pengaturan/tahun-buku/' . $period['slug']) : site_url('pengaturan/tahun-buku'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'text', 'name' => 'name', 'label' => 'Nama Tahun Buku', 'value' => old('name', $period['name'] ?? '')],
                ['type' => 'date', 'name' => 'start_date', 'label' => 'Tanggal Mulai', 'value' => old('start_date', $period['start_date'] ?? '')],
                ['type' => 'date', 'name' => 'end_date', 'label' => 'Tanggal Selesai', 'value' => old('end_date', $period['end_date'] ?? '')],
                ['type' => 'select', 'name' => 'status', 'label' => 'Status', 'value' => old('status', ((int) ($period['is_locked'] ?? 0) === 1 ? 'Ditutup' : ((int) ($period['is_active'] ?? 0) === 1 ? 'Aktif' : 'Draft'))), 'options' => ['Draft', 'Aktif', 'Ditutup']],
            ],
        ];
    }

    private function persistBookPeriod(?string $slug = null): RedirectResponse
    {
        $model = new BookPeriodModel();
        $current = $slug !== null ? $model->where('slug', $slug)->first() : null;

        if ($slug !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $name = trim((string) $this->request->getPost('name'));
        $startDate = (string) $this->request->getPost('start_date');
        $endDate = (string) $this->request->getPost('end_date');
        $status = (string) $this->request->getPost('status');

        if ($name === '' || $startDate === '' || $endDate === '') {
            return redirect()->back()->withInput()->with('error', 'Nama tahun buku dan rentang tanggal wajib diisi.');
        }

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'name' => $name,
            'slug' => $this->uniqueSlug(new BookPeriodModel(), $name, $current['id'] ?? null),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => $status === 'Aktif' ? 1 : 0,
            'is_locked' => $status === 'Ditutup' ? 1 : 0,
        ];

        if ($status === 'Aktif') {
            $model->where('institution_id', $this->currentInstitutionId())->set(['is_active' => 0])->update();
        }

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/tahun-buku/' . $payload['slug'] . '/edit'))->with('success', 'Tahun buku berhasil diperbarui.');
        }

        $model->insert($payload);
        return redirect()->to(site_url('pengaturan/tahun-buku/' . $payload['slug'] . '/edit'))->with('success', 'Tahun buku berhasil ditambahkan.');
    }

    private function loadOpeningBalanceRows(): array
    {
        $periodMap = [];
        foreach ($this->loadBookPeriodRows() as $period) {
            $periodMap[(int) $period['id']] = $period;
        }
        $positionMap = $this->reportPositionNameMap();
        $accountMap = [];
        foreach ($this->loadAccountRows() as $acc) {
            $accountMap[(int) $acc['id']] = $acc['name'] . ' (' . $acc['mark'] . ')';
        }

        $rows = (new OpeningBalanceModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->orderBy('book_period_id', 'DESC')
            ->findAll();

        foreach ($rows as &$row) {
            $row['slug'] = (string) $row['id'];
            $row['label'] = $row['source_label'];
            $row['book_period_name'] = $periodMap[(int) $row['book_period_id']]['name'] ?? 'Tanpa Periode';
            $row['report_position_name'] = $positionMap[(int) $row['report_position_id']] ?? 'Belum dipilih';
            $row['account_name'] = !empty($row['account_id']) ? ($accountMap[(int) $row['account_id']] ?? '') : '';
            $row['type'] = !empty($row['account_name']) ? 'Rekening' : 'Pos Neraca';
            $row['note'] = 'Saldo awal untuk ' . strtolower($row['report_position_name']) . '.';
        }
        unset($row);

        return $rows;
    }

    private function buildOpeningBalanceFormData(?array $balance = null, bool $isEdit = false): array
    {
        $periods = $this->loadBookPeriodRows();
        $positions = $this->loadReportPositionRows();
        $accounts = $this->loadAccountRows();

        $accountOptions = [['value' => '', 'label' => '— Tidak terkait rekening —']];
        foreach ($accounts as $acc) {
            $accountOptions[] = ['value' => (string) $acc['id'], 'label' => $acc['name'] . ' (' . $acc['mark'] . ')'];
        }

        return [
            'pageTitle' => $isEdit ? 'Edit ' . ($balance['source_label'] ?? 'Saldo Awal') : 'Tambah Saldo Awal',
            'activeNav' => 'beranda',
            'backUrl' => site_url('pengaturan/saldo-awal'),
            'formMode' => $isEdit ? 'Edit Data' : 'Tambah Data',
            'formTitle' => 'Form Saldo Awal',
            'formDescription' => 'Saldo awal menyimpan posisi awal tiap rekening atau pos neraca sebelum transaksi berjalan mulai dicatat.',
            'saveLabel' => $isEdit ? 'Simpan Perubahan' : 'Simpan Saldo Awal',
            'formAction' => $isEdit ? site_url('pengaturan/saldo-awal/' . $balance['id']) : site_url('pengaturan/saldo-awal'),
            'formMethod' => 'post',
            'formFields' => [
                ['type' => 'select', 'name' => 'book_period_id', 'label' => 'Tahun Buku', 'value' => (string) old('book_period_id', $balance['book_period_id'] ?? ($periods[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($periods)],
                ['type' => 'select', 'name' => 'account_id', 'label' => 'Rekening / Dompet (Opsional)', 'value' => (string) old('account_id', $balance['account_id'] ?? ''), 'options' => $accountOptions],
                ['type' => 'text', 'name' => 'source_label', 'label' => 'Pos / Sumber Saldo', 'value' => old('source_label', $balance['source_label'] ?? '')],
                ['type' => 'select', 'name' => 'report_position_id', 'label' => 'Pos Laporan Terkait', 'value' => (string) old('report_position_id', $balance['report_position_id'] ?? ($positions[0]['id'] ?? '')), 'options' => $this->buildSelectOptions($positions)],
                ['type' => 'text', 'name' => 'amount', 'label' => 'Nilai Saldo Awal', 'value' => old('amount', isset($balance['amount']) ? rupiah((float) $balance['amount']) : 'Rp 0')],
            ],
        ];
    }

    private function persistOpeningBalance(?string $id = null): RedirectResponse
    {
        $model = new OpeningBalanceModel();
        $current = $id !== null ? $model->find((int) $id) : null;

        if ($id !== null && ! is_array($current)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $sourceLabel = trim((string) $this->request->getPost('source_label'));
        if ($sourceLabel === '') {
            return redirect()->back()->withInput()->with('error', 'Pos / sumber saldo wajib diisi.');
        }

        $accountId = $this->request->getPost('account_id');

        $payload = [
            'institution_id' => $this->currentInstitutionId(),
            'account_id' => ($accountId !== '' && $accountId !== null) ? (int) $accountId : null,
            'book_period_id' => (int) $this->request->getPost('book_period_id'),
            'report_position_id' => (int) $this->request->getPost('report_position_id'),
            'source_label' => $sourceLabel,
            'amount' => $this->normalizeMoney((string) $this->request->getPost('amount')),
        ];

        if (is_array($current)) {
            $model->update((int) $current['id'], $payload);
            return redirect()->to(site_url('pengaturan/saldo-awal/' . $current['id'] . '/edit'))->with('success', 'Saldo awal berhasil diperbarui.');
        }

        $model->insert($payload);
        $newId = (string) $model->getInsertID();
        return redirect()->to(site_url('pengaturan/saldo-awal/' . $newId . '/edit'))->with('success', 'Saldo awal berhasil ditambahkan.');
    }
}
