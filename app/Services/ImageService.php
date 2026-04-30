<?php

namespace ECommerce\App\Services;

class ImageService
{
    private $uploadDir;
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    private $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(string $uploadDir = 'public/uploads')
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * Upload and optimize image
     */
    public function upload(array $file, string $prefix = 'img'): ?string
    {
        // Validate file
        if (!isset($file['tmp_name']) || !isset($file['error']) || !isset($file['type'])) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $this->maxFileSize) {
            return null;
        }

        if (!in_array($file['type'], $this->allowedMimes)) {
            return null;
        }

        // Validate extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $this->allowedExtensions)) {
            return null;
        }

        // Create upload directory
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
        $destination = $this->uploadDir . '/' . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        // Optimize image
        $this->optimize($destination, $extension);

        return $filename;
    }

    /**
     * Optimize image (resize if needed)
     */
    private function optimize(string $path, string $extension): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        try {
            // Load image
            $image = match ($extension) {
                'jpeg', 'jpg' => imagecreatefromjpeg($path),
                'png' => imagecreatefrompng($path),
                'gif' => imagecreatefromgif($path),
                'webp' => imagecreatefromwebp($path),
                default => null
            };

            if (!$image) {
                return;
            }

            // Get current dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Resize if needed (max 1200x1200)
            if ($width > 1200 || $height > 1200) {
                $this->resizeImage($path, $image, $width, $height, 1200, $extension);
            }

            imagedestroy($image);
        } catch (\Exception $e) {
            // Silently fail optimization
        }
    }

    /**
     * Resize image
     */
    private function resizeImage(string $path, $image, int $width, int $height, int $maxSize, string $extension): void
    {
        // Calculate new dimensions
        $ratio = $width / $height;

        if ($width > $height) {
            $newWidth = $maxSize;
            $newHeight = (int) ($maxSize / $ratio);
        } else {
            $newHeight = $maxSize;
            $newWidth = (int) ($maxSize * $ratio);
        }

        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($extension === 'png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save resized image
        match ($extension) {
            'jpeg', 'jpg' => imagejpeg($resized, $path, 85),
            'png' => imagepng($resized, $path, 8),
            'gif' => imagegif($resized, $path),
            'webp' => imagewebp($resized, $path, 85),
        };

        imagedestroy($resized);
    }

    /**
     * Delete image
     */
    public function delete(string $filename): bool
    {
        $path = $this->uploadDir . '/' . basename($filename);

        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Get image URL
     */
    public function getUrl(string $filename): string
    {
        return '/' . $this->uploadDir . '/' . basename($filename);
    }

    /**
     * Validate image dimensions
     */
    public function validateDimensions(string $path, int $minWidth = 0, int $minHeight = 0): bool
    {
        $size = getimagesize($path);

        if (!$size) {
            return false;
        }

        return $size[0] >= $minWidth && $size[1] >= $minHeight;
    }
}
