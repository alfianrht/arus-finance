<?php

namespace App\Services;

use CodeIgniter\Files\File;
use RuntimeException;

class FileUploadService
{
    public function proofDirectory(): string
    {
        return WRITEPATH . 'uploads/proofs/' . date('Y/m') . '/';
    }

    public function ensureProofDirectory(): string
    {
        $directory = $this->proofDirectory();

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder bukti transaksi gagal dibuat.');
        }

        return $directory;
    }

    public function storeProof(File $file, string $prefix = 'proof'): string
    {
        $directory = $this->ensureProofDirectory();
        $extension = $file->getExtension() ?: 'bin';
        $filename = sprintf('%s_%s.%s', $prefix, date('Ymd_His'), $extension);

        $file->move($directory, $filename);

        return 'uploads/proofs/' . date('Y/m') . '/' . $filename;
    }
}
