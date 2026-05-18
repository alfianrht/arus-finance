<?php

namespace App\Controllers;

use App\Models\AccountModel;
use App\Services\OpenAiBillScanService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class AiController extends BaseController
{
    private OpenAiBillScanService $billScanService;

    public function __construct()
    {
        $this->billScanService = new OpenAiBillScanService();
    }

    public function scanBill(): ResponseInterface
    {
        $rules = [
            'bill_image' => [
                'label' => 'Bukti transaksi',
                'rules' => 'uploaded[bill_image]|max_size[bill_image,5120]|mime_in[bill_image,image/jpeg,image/png,image/webp]',
            ],
        ];

        if (! $this->validate($rules)) {
            return $this->respondScanError('Bukti belum bisa dibaca. Silakan isi manual.');
        }

        $file = $this->request->getFile('bill_image');
        if ($file === null || ! $file->isValid() || $file->hasMoved()) {
            return $this->respondScanError('Bukti belum bisa dibaca. Silakan isi manual.');
        }

        $mode = strtolower(trim((string) $this->request->getPost('mode')));
        if (! in_array($mode, ['expense', 'income', 'honor', 'transfer'], true)) {
            $mode = 'expense';
        }

        $allowedCategories = $this->allowedCategoriesForMode($mode);

        $availableAccounts = array_map(
            static fn(array $account): string => (string) ($account['name'] ?? ''),
            (new AccountModel())
                ->where('institution_id', $this->currentInstitutionId())
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll()
        );

        try {
            $result = $this->billScanService->scanTransactionDocument($file, $mode, $allowedCategories, $availableAccounts);
        } catch (Throwable $e) {
            log_message('error', 'Bill scan failed: {message}', ['message' => $e->getMessage()]);
            return $this->respondScanError('Bukti belum bisa dibaca. Silakan isi manual.');
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Bukti transaksi berhasil dibaca.',
            'data' => $result,
            'csrf' => [
                'token' => csrf_token(),
                'hash' => csrf_hash(),
            ],
        ]);
    }

    private function respondScanError(string $message): ResponseInterface
    {
        return $this->response
            ->setStatusCode(422)
            ->setJSON([
                'success' => false,
                'message' => $message,
                'data' => null,
                'csrf' => [
                    'token' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
    }

    /**
     * @return array<int, string>
     */
    private function allowedCategoriesForMode(string $mode): array
    {
        if ($mode === 'transfer') {
            return [];
        }

        if ($mode === 'honor') {
            $rows = (new \App\Models\TransactionCategoryModel())
                ->where('institution_id', $this->currentInstitutionId())
                ->where('deleted_at', null)
                ->where('is_active', 1)
                ->where('kind', 'Keluar')
                ->like('name', 'Honor', 'both')
                ->orderBy('sort_order', 'ASC')
                ->orderBy('name', 'ASC')
                ->findAll();

            $names = array_values(array_filter(array_map(
                static fn(array $row): string => trim((string) ($row['name'] ?? '')),
                $rows
            )));

            return $names === [] ? ['Honor'] : $names;
        }

        $kind = $mode === 'income' ? 'Masuk' : 'Keluar';

        $rows = (new \App\Models\TransactionCategoryModel())
            ->where('institution_id', $this->currentInstitutionId())
            ->where('deleted_at', null)
            ->where('is_active', 1)
            ->where('kind', $kind)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();

        $names = array_values(array_filter(array_map(
            static fn(array $row): string => trim((string) ($row['name'] ?? '')),
            $rows
        )));

        return $names === [] ? ['Lainnya'] : $names;
    }
}
