<?php

namespace App\Core;

class Controller {
    // Send a JSON response
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Helper for successful response
    protected function success($data = [], $message = null) {
        $response = ['success' => true];
        if ($message !== null) {
            $response['message'] = $message;
        }
        if (!empty($data) || is_array($data)) {
            $response = array_merge($response, $data);
        }
        $this->json($response);
    }

    // Helper for error response
    protected function error($message, $statusCode = 400) {
        $this->json([
            'success' => false,
            'message' => $message
        ], $statusCode);
    }

    // Helper for handling file uploads
    protected function handleFileUpload($fieldName, $targetDir = null) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $targetDir = $targetDir ?: __DIR__ . '/../../../uploads';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $file = $_FILES[$fieldName];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = time() . '-' . mt_rand(100000, 999999) . ($ext ? '.' . $ext : '');
        $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return '/uploads/' . $filename;
        }

        return null;
    }

    // Helper for handling multiple file uploads (e.g., booklets, homeworks)
    protected function handleMultipleFilesUpload($fieldName, $targetDir = null) {
        if (!isset($_FILES[$fieldName])) {
            return [];
        }

        $targetDir = $targetDir ?: __DIR__ . '/../../../uploads';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $files = $_FILES[$fieldName];
        $uploaded = [];

        // Check if multiple files (array format)
        if (is_array($files['name'])) {
            $count = count($files['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $originalName = $files['name'][$i];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $filename = time() . '-' . mt_rand(100000, 999999) . ($ext ? '.' . $ext : '');
                    $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

                    if (move_uploaded_file($files['tmp_name'][$i], $destination)) {
                        $sizeBytes = $files['size'][$i] ?? 0;
                        $sizeStr = $sizeBytes >= 1048576 
                            ? round($sizeBytes / 1048576, 1) . ' MB' 
                            : round($sizeBytes / 1024, 1) . ' KB';

                        $uploaded[] = [
                            'name' => $originalName,
                            'url' => '/uploads/' . $filename,
                            'size' => $sizeStr
                        ];
                    }
                }
            }
        } elseif ($files['error'] === UPLOAD_ERR_OK) {
            // Single file uploaded under this field name
            $originalName = $files['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $filename = time() . '-' . mt_rand(100000, 999999) . ($ext ? '.' . $ext : '');
            $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

            if (move_uploaded_file($files['tmp_name'], $destination)) {
                $sizeBytes = $files['size'] ?? 0;
                $sizeStr = $sizeBytes >= 1048576 
                    ? round($sizeBytes / 1048576, 1) . ' MB' 
                    : round($sizeBytes / 1024, 1) . ' KB';

                $uploaded[] = [
                    'name' => $originalName,
                    'url' => '/uploads/' . $filename,
                    'size' => $sizeStr
                ];
            }
        }

        return $uploaded;
    }
}
