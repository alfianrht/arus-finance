<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class FileUploadService
{
    public function proofDirectory(string $bucket): string
    {
        return FCPATH . 'uploads/transaksi/' . trim($bucket, '/') . '/' . date('Y/m') . '/';
    }

    public function ensureProofDirectory(string $bucket): string
    {
        $directory = $this->proofDirectory($bucket);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder bukti transaksi gagal dibuat.');
        }

        return $directory;
    }

    public function storeProof(UploadedFile $file, string $bucket, string $prefix = 'proof'): string
    {
        $directory = $this->ensureProofDirectory($bucket);
        $extension = $file->getExtension() ?: 'bin';
        $filename = sprintf('%s_%s.%s', $prefix, bin2hex(random_bytes(6)), $extension);

        $file->move($directory, $filename);

        return 'uploads/transaksi/' . trim($bucket, '/') . '/' . date('Y/m') . '/' . $filename;
    }

    public function deleteProof(?string $relativePath): void
    {
        $path = trim((string) $relativePath);
        if ($path === '') {
            return;
        }

        $fullPath = FCPATH . ltrim($path, '/');
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
