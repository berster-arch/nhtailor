<?php
function gallery_save_upload(array $file): array
{
    if (!isset($file['error']) || !is_int($file['error'])) {
        return ['filename' => null, 'error' => 'Data unggahan tidak valid.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['filename' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK
        || !isset($file['tmp_name'], $file['size'])
        || !is_string($file['tmp_name'])
        || !is_numeric($file['size'])
        || !is_uploaded_file($file['tmp_name'])) {
        return ['filename' => null, 'error' => 'Unggahan gagal. Silakan pilih ulang gambar.'];
    }

    if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
        return ['filename' => null, 'error' => 'Ukuran gambar maksimal 5 MB.'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $extensionByMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = is_array($imageInfo) ? ($imageInfo['mime'] ?? '') : '';

    if (!isset($extensionByMime[$mime])) {
        return ['filename' => null, 'error' => 'Format gambar harus JPG, PNG, atau WEBP.'];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensionByMime[$mime];
    $destination = __DIR__ . '/../../../foto/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['filename' => null, 'error' => 'Gambar gagal disimpan. Periksa izin folder foto.'];
    }

    return ['filename' => $filename, 'error' => null];
}

function gallery_remove_upload(string $filename): void
{
    $path = __DIR__ . '/../../../foto/' . basename($filename);
    if (is_file($path) && !unlink($path)) {
        error_log('Unable to remove unused gallery upload: ' . $path);
    }
}
