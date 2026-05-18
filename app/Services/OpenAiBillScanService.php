<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class OpenAiBillScanService
{
    private const MODEL = 'gpt-4.1-mini';
    private const API_URL = 'https://api.openai.com/v1/responses';

    /**
     * @param array<int, string> $allowedCategories
     * @param array<int, string> $availableAccounts
     * @return array<string, mixed>
     */
    public function scanTransactionDocument(UploadedFile $file, string $mode, array $allowedCategories, array $availableAccounts): array
    {
        $apiKey = trim((string) env('OPENAI_API_KEY'));
        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY missing');
        }

        $mimeType = (string) ($file->getMimeType() ?: 'image/jpeg');
        $binary = file_get_contents($file->getTempName());
        if ($binary === false) {
            throw new RuntimeException('Unable to read uploaded bill image');
        }

        $mode = strtolower(trim($mode));
        if (! in_array($mode, ['expense', 'income', 'honor', 'transfer'], true)) {
            $mode = 'expense';
        }

        $prompt = $this->buildPrompt($mode, $allowedCategories, $availableAccounts);
        $payload = [
            'model' => self::MODEL,
            'input' => [[
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'input_text',
                        'text' => $prompt,
                    ],
                    [
                        'type' => 'input_image',
                        'image_url' => 'data:' . $mimeType . ';base64,' . base64_encode($binary),
                    ],
                ],
            ]],
            'text' => [
                'format' => [
                    'type' => 'json_schema',
                    'name' => 'transaction_document_scan',
                    'strict' => true,
                    'schema' => $this->responseSchema(),
                ],
            ],
        ];

        $responseBody = $this->sendRequest($apiKey, $payload);
        return $this->normalizeResult($responseBody, $mode, $allowedCategories, $availableAccounts);
    }

    /**
     * @param array<int, string> $allowedCategories
     * @param array<int, string> $availableAccounts
     */
    private function buildPrompt(string $mode, array $allowedCategories, array $availableAccounts): string
    {
        $taskMap = [
            'expense' => 'Biaya / Belanja',
            'income' => 'Uang Masuk',
            'honor' => 'Honor & Gaji',
            'transfer' => 'Pindah Dana',
        ];

        $modeText = $taskMap[$mode] ?? 'Biaya / Belanja';
        $categoryBlock = $allowedCategories === []
            ? '- Tidak ada kategori khusus. Jika tidak yakin gunakan null.'
            : '- ' . implode("\n- ", $allowedCategories);

        return implode("\n", [
            'Anda adalah ekstraktor data transaksi untuk aplikasi pencatatan kas perusahaan bernama Arus.',
            '',
            'Tugas:',
            'Baca gambar struk, nota, invoice, bukti transfer, atau bill.',
            'Ambil informasi yang relevan untuk mengisi form ' . $modeText . '.',
            'Kembalikan JSON saja.',
            '',
            'Field yang harus dikembalikan:',
            '- transaction_date: tanggal transaksi dalam format YYYY-MM-DD, atau null jika tidak terbaca.',
            '- amount: nominal utama transaksi dalam angka integer rupiah, atau null jika tidak yakin.',
            '- admin_fee: biaya admin dalam angka integer rupiah, atau 0 jika tidak ada.',
            '- merchant_name: nama merchant/vendor/bank/penerima jika terbaca.',
            '- category_suggestion: salah satu dari kategori yang diizinkan, atau null jika tidak relevan.',
            '- account_suggestion: rekening/dompet jika hanya ada satu rekening yang jelas, atau null.',
            '- from_account_suggestion: rekening/dompet asal jika bisa ditebak, khusus pindah dana atau pengeluaran.',
            '- to_account_suggestion: rekening/dompet tujuan jika bisa ditebak, khusus pindah dana atau pemasukan.',
            '- description: deskripsi singkat untuk field keterangan.',
            '- confidence: angka 0 sampai 1.',
            '- needs_review: true jika ada keraguan.',
            '- raw_summary: ringkasan singkat isi bukti.',
            '',
            'Kategori yang diizinkan:',
            $categoryBlock,
            '',
            'Rekening/dompet yang tersedia:',
            '- ' . implode("\n- ", $availableAccounts),
            '',
            'Aturan:',
            '- Mode form saat ini adalah ' . $modeText . '. Sesuaikan hasil dengan mode itu.',
            '- Gunakan total akhir/final amount sebagai amount.',
            '- Jangan gunakan subtotal jika ada total akhir.',
            '- Jika ada biaya admin bank, isi admin_fee.',
            '- Jika nominal tidak jelas, amount null dan needs_review true.',
            '- Jika tanggal tidak jelas, transaction_date null.',
            '- Jika kategori tidak yakin dan kategori memang dipakai di mode ini, gunakan Lainnya dan needs_review true.',
            '- Jangan mengarang data yang tidak ada di bukti.',
            '- Jangan menyimpan transaksi otomatis.',
            '- Output harus JSON valid.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'transaction_date' => [
                    'type' => ['string', 'null'],
                    'description' => 'Tanggal transaksi format YYYY-MM-DD atau null.',
                ],
                'amount' => [
                    'type' => ['integer', 'null'],
                ],
                'admin_fee' => [
                    'type' => 'integer',
                ],
                'merchant_name' => [
                    'type' => ['string', 'null'],
                ],
                'category_suggestion' => [
                    'type' => 'string',
                ],
                'account_suggestion' => [
                    'type' => ['string', 'null'],
                ],
                'from_account_suggestion' => [
                    'type' => ['string', 'null'],
                ],
                'to_account_suggestion' => [
                    'type' => ['string', 'null'],
                ],
                'description' => [
                    'type' => 'string',
                ],
                'confidence' => [
                    'type' => 'number',
                ],
                'needs_review' => [
                    'type' => 'boolean',
                ],
                'raw_summary' => [
                    'type' => 'string',
                ],
            ],
            'required' => [
                'transaction_date',
                'amount',
                'admin_fee',
                'merchant_name',
                'category_suggestion',
                'account_suggestion',
                'from_account_suggestion',
                'to_account_suggestion',
                'description',
                'confidence',
                'needs_review',
                'raw_summary',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function sendRequest(string $apiKey, array $payload): array
    {
        $ch = curl_init(self::API_URL);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize OpenAI request');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $raw = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $curlError !== '') {
            throw new RuntimeException('OpenAI request failed: ' . $curlError);
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('OpenAI response is not valid JSON');
        }

        if ($httpCode >= 400) {
            $message = $decoded['error']['message'] ?? ('HTTP ' . $httpCode);
            throw new RuntimeException('OpenAI returned error: ' . $message);
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $responseBody
     * @param array<int, string> $allowedCategories
     * @param array<int, string> $availableAccounts
     * @return array<string, mixed>
     */
    private function normalizeResult(array $responseBody, string $mode, array $allowedCategories, array $availableAccounts): array
    {
        $text = trim((string) ($responseBody['output_text'] ?? ''));

        if ($text === '' && isset($responseBody['output']) && is_array($responseBody['output'])) {
            foreach ($responseBody['output'] as $outputItem) {
                if (! is_array($outputItem['content'] ?? null)) {
                    continue;
                }
                foreach ($outputItem['content'] as $contentItem) {
                    $candidate = trim((string) ($contentItem['text'] ?? ''));
                    if ($candidate !== '') {
                        $text = $candidate;
                        break 2;
                    }
                }
            }
        }

        if ($text === '') {
            throw new RuntimeException('OpenAI output_text is empty');
        }

        $data = json_decode($text, true);
        if (! is_array($data)) {
            throw new RuntimeException('Structured bill scan JSON invalid');
        }

        $category = $this->nullableString($data['category_suggestion'] ?? null);
        if ($allowedCategories !== []) {
            if ($category === null || ! in_array($category, $allowedCategories, true)) {
                $category = 'Lainnya';
                $data['needs_review'] = true;
            }
        } else {
            $category = null;
        }

        $accountSuggestion = $this->matchSuggestion((string) ($data['account_suggestion'] ?? ''), $availableAccounts);
        $fromAccountSuggestion = $this->matchSuggestion((string) ($data['from_account_suggestion'] ?? ''), $availableAccounts);
        $toAccountSuggestion = $this->matchSuggestion((string) ($data['to_account_suggestion'] ?? ''), $availableAccounts);

        $amount = isset($data['amount']) && is_numeric($data['amount']) ? (int) $data['amount'] : null;
        $adminFee = isset($data['admin_fee']) && is_numeric($data['admin_fee']) ? max(0, (int) $data['admin_fee']) : 0;
        $confidence = isset($data['confidence']) ? max(0, min(1, (float) $data['confidence'])) : 0.0;
        $needsReview = (bool) ($data['needs_review'] ?? true);
        if ($amount === null) {
            $needsReview = true;
        }

        return [
            'transaction_date' => $this->normalizeDate($data['transaction_date'] ?? null),
            'amount' => $amount,
            'admin_fee' => $adminFee,
            'merchant_name' => $this->nullableString($data['merchant_name'] ?? null),
            'category_suggestion' => $category,
            'account_suggestion' => $accountSuggestion,
            'from_account_suggestion' => $fromAccountSuggestion,
            'to_account_suggestion' => $toAccountSuggestion,
            'description' => trim((string) ($data['description'] ?? '')),
            'confidence' => $confidence,
            'needs_review' => $needsReview,
            'raw_summary' => trim((string) ($data['raw_summary'] ?? '')),
            'mode' => $mode,
        ];
    }

    private function normalizeDate($value): ?string
    {
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function nullableString($value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    /**
     * @param array<int, string> $availableAccounts
     */
    private function matchSuggestion(string $suggestion, array $availableAccounts): ?string
    {
        $needle = $this->normalizeToken($suggestion);
        if ($needle === '') {
            return null;
        }

        foreach ($availableAccounts as $account) {
            $normalized = $this->normalizeToken($account);
            if ($normalized === $needle || str_contains($normalized, $needle) || str_contains($needle, $normalized)) {
                return $account;
            }
        }

        return null;
    }

    private function normalizeToken(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';
        return trim($value);
    }
}
