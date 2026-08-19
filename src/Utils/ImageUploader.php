<?php

namespace App\Utils;

class ImageUploader {

    private $uploadDir;
    private $maxFileSize = 2097152; // 2MB
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function __construct($uploadDir = null) {
        // Đảm bảo luôn dùng đường dẫn tuyệt đối (gồm /public/uploads) để tránh sai đường dẫn do CWD khác nhau
        if (!$uploadDir) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';
        }

        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload($file, $subDir = '') {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Upload failed with error code: ' . $file['error']);
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \Exception('File size too large. Max 2MB allowed.');
        }

        if (!in_array($file['type'], $this->allowedTypes)) {
            throw new \Exception('Invalid file type. Only JPEG, PNG, GIF, WEBP allowed.');
        }

        // Create sub directory if needed
        $targetDir = $this->uploadDir . $subDir . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $targetDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Trả về tên file (không include subdir) để dễ lưu và hiển thị
            return $filename;
        } else {
            throw new \Exception('Failed to move uploaded file.');
        }
    }
}
