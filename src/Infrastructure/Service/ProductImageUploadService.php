<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

final class ProductImageUploadService
{
    public function __construct(private readonly string $uploadDir) {}

    public function upload(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? 'jpg';
        $filename  = Uuid::v4()->toRfc4122() . '.' . $extension;

        $file->move($this->uploadDir, $filename);

        return '/uploads/products/' . $filename;
    }

    public function delete(string $imageUrl): void
    {
        $filename = basename($imageUrl);
        $path     = $this->uploadDir . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
