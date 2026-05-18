<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class FileUploadService
{
    private const MAX_IMAGE_DIMENSION = 1800;
    private const JPEG_QUALITY = 84;
    private const WEBP_QUALITY = 82;

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
        $this->optimizeStoredImage($directory . $filename);

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

    private function optimizeStoredImage(string $path): void
    {
        if (! extension_loaded('gd') || ! is_file($path)) {
            return;
        }

        $imageInfo = @getimagesize($path);
        if (! is_array($imageInfo)) {
            return;
        }

        [$width, $height, $imageType] = $imageInfo;
        if ($width < 1 || $height < 1) {
            return;
        }

        if (! in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return;
        }

        $source = $this->createImageResource($path, $imageType);
        if ($source === null) {
            return;
        }

        $target = $this->resizeResourceIfNeeded($source, $width, $height);
        if ($target === null) {
            imagedestroy($source);
            return;
        }

        $saved = $this->saveImageResource($target, $path, $imageType);

        if ($target !== $source) {
            imagedestroy($target);
        }
        imagedestroy($source);

        if (! $saved) {
            return;
        }
    }

    private function createImageResource(string $path, int $imageType)
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private function resizeResourceIfNeeded($source, int $width, int $height)
    {
        $longestSide = max($width, $height);
        if ($longestSide <= self::MAX_IMAGE_DIMENSION) {
            return $source;
        }

        $scale = self::MAX_IMAGE_DIMENSION / $longestSide;
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($target === false) {
            return null;
        }

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
        imagefill($target, 0, 0, $transparent);

        if (! imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
            imagedestroy($target);
            return null;
        }

        return $target;
    }

    private function saveImageResource($resource, string $path, int $imageType): bool
    {
        return match ($imageType) {
            IMAGETYPE_JPEG => @imagejpeg($resource, $path, self::JPEG_QUALITY),
            IMAGETYPE_PNG => @imagepng($resource, $path, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? @imagewebp($resource, $path, self::WEBP_QUALITY) : false,
            default => false,
        };
    }
}
