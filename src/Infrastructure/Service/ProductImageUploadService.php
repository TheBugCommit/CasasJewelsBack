<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Domain\Service\UuidGeneratorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ProductImageUploadService
{
    public function __construct(
        private readonly string $uploadDir,
        private readonly UuidGeneratorInterface $uuidGenerator
    ) {}

    public function upload(UploadedFile $file): string
    {
        $extension = $file->guessExtension() ?? 'jpg';
        $filename  = $this->uuidGenerator->generate() . '.' . $extension;

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
