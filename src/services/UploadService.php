<?php

class UploadService
{
    private string $uploadDir;
    private string $publicPath;

    // Needs 2 paths to create directory if not exists and return public URL
    public function __construct(string $uploadDir, string $publicPath)
    {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->publicPath = rtrim($publicPath, '/') . '/';

        // Ensure the upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function save(array $file, ?string $prefix): ?string
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Invalid file upload or error code: ' . ($file['error'] ?? 'unknown'));
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = ($prefix ?: 'img') . '-' . time() . '.' . $extension;
        $destination = $this->uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception('Failed to move uploaded file to destination');
        }

        return $this->publicPath . $fileName;
    }

    public function delete(string $fileUrl): bool
    {
        $filePath = $this->uploadDir . basename($fileUrl);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
