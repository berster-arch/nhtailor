<?php
require_once __DIR__ . '/../guard.php';
require_once __DIR__ . '/../../../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['hapus_foto'])) {
    http_response_code(405);
    exit('Metode permintaan tidak diizinkan.');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    echo "<script>alert('Data foto tidak valid.'); window.location='" . panel_url('view_galeri') . "';</script>";
    exit;
}

$selectStatement = mysqli_prepare($koneksi, 'SELECT foto FROM galeri WHERE id = ? LIMIT 1');
if ($selectStatement === false) {
    error_log('Gallery deletion lookup preparation failed: ' . mysqli_error($koneksi));
    echo "<script>alert('Galeri gagal dimuat. Silakan coba kembali.'); window.location='" . panel_url('view_galeri') . "';</script>";
    exit;
}

mysqli_stmt_bind_param($selectStatement, 'i', $id);
mysqli_stmt_execute($selectStatement);
$result = mysqli_stmt_get_result($selectStatement);
$photo = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($selectStatement);

if (!$photo) {
    echo "<script>alert('Foto tidak ditemukan.'); window.location='" . panel_url('view_galeri') . "';</script>";
    exit;
}

$deleteStatement = mysqli_prepare($koneksi, 'DELETE FROM galeri WHERE id = ?');
if ($deleteStatement === false) {
    error_log('Gallery deletion preparation failed: ' . mysqli_error($koneksi));
    echo "<script>alert('Foto gagal dihapus. Silakan coba kembali.'); window.location='" . panel_url('view_galeri') . "';</script>";
    exit;
}

mysqli_stmt_bind_param($deleteStatement, 'i', $id);
$deleted = mysqli_stmt_execute($deleteStatement);
mysqli_stmt_close($deleteStatement);

if (!$deleted) {
    error_log('Gallery deletion failed: ' . mysqli_error($koneksi));
    echo "<script>alert('Foto gagal dihapus. Silakan coba kembali.'); window.location='" . panel_url('view_galeri') . "';</script>";
    exit;
}

$filename = basename((string) $photo['foto']);
$path = __DIR__ . '/../../../foto/' . $filename;
if ($filename !== '' && is_file($path) && !unlink($path)) {
    error_log('Deleted gallery row but failed to remove image file: ' . $path);
}

echo "<script>alert('Foto berhasil dihapus dari galeri.'); window.location='" . panel_url('view_galeri') . "';</script>";
?>
