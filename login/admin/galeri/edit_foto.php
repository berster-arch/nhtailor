<?php
require_once __DIR__ . '/../guard.php';

require_once __DIR__ . '/../../../koneksi.php';
require_once __DIR__ . '/upload_helper.php';

$pesan = "";

// 1. AMBIL ID DARI URL & QUERY DATA LAMA
if (isset($_GET['id']) && is_scalar($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $ambilStatement = $id ? mysqli_prepare($koneksi, 'SELECT * FROM galeri WHERE id = ?') : false;
    if ($ambilStatement) {
        mysqli_stmt_bind_param($ambilStatement, 'i', $id);
        mysqli_stmt_execute($ambilStatement);
        $ambilResult = mysqli_stmt_get_result($ambilStatement);
        $pecah = $ambilResult ? mysqli_fetch_assoc($ambilResult) : null;
        mysqli_stmt_close($ambilStatement);
    } else {
        $pecah = null;
    }

    // Proteksi jika data tidak ada di database
    if (!$pecah) {
        echo "<script>alert('Data tidak ditemukan!'); window.location='" . panel_url('view_galeri') . "';</script>";
        exit;
    }
} else {
    echo "<script>window.location='" . panel_url('view_galeri') . "';</script>";
    exit;
}

// 2. PROSES UPDATE KETIKA FORM DI-SUBMIT
if (isset($_POST["edit"]) && $_POST["edit"] == "form_edit") {
    $keterangan = trim((string) ($_POST['keterangan'] ?? ''));
    $upload = gallery_save_upload($_FILES['foto'] ?? []);

    if ($keterangan === '') {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Keterangan foto wajib diisi.</div>";
    } elseif ($upload['error'] !== null) {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>" . htmlspecialchars($upload['error'], ENT_QUOTES, 'UTF-8') . "</div>";
    } else {
        $fotoBaru = $upload['filename'];
        $updateStatement = $fotoBaru === null
            ? mysqli_prepare($koneksi, 'UPDATE galeri SET keterangan = ? WHERE id = ?')
            : mysqli_prepare($koneksi, 'UPDATE galeri SET foto = ?, keterangan = ? WHERE id = ?');

        if ($updateStatement === false) {
            error_log('Gallery update preparation failed: ' . mysqli_error($koneksi));
            $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Perubahan galeri gagal disimpan. Silakan coba kembali.</div>";
            if ($fotoBaru !== null) {
                gallery_remove_upload($fotoBaru);
            }
        } else {
            if ($fotoBaru === null) {
                mysqli_stmt_bind_param($updateStatement, 'si', $keterangan, $id);
            } else {
                mysqli_stmt_bind_param($updateStatement, 'ssi', $fotoBaru, $keterangan, $id);
            }

            if (mysqli_stmt_execute($updateStatement)) {
                mysqli_stmt_close($updateStatement);
                if ($fotoBaru !== null) {
                    $fotoLama = basename((string) $pecah['foto']);
                    $pathFotoLama = __DIR__ . '/../../../foto/' . $fotoLama;
                    if ($fotoLama !== '' && is_file($pathFotoLama)) {
                        unlink($pathFotoLama);
                    }
                }
                echo "<script>alert('Data berhasil diperbarui'); window.location='" . panel_url('view_galeri') . "';</script>";
                exit;
            }

            error_log('Gallery update failed: ' . mysqli_stmt_error($updateStatement));
            mysqli_stmt_close($updateStatement);
            if ($fotoBaru !== null) {
                gallery_remove_upload($fotoBaru);
            }
            $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Perubahan galeri gagal disimpan. Silakan coba kembali.</div>";
        }
    }
}
?>

<div class="w-full">
    <!-- Header Halaman -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="font-luxury text-3xl text-gray-800">Edit Koleksi Galeri</h2>
            <p class="text-gray-500 text-sm">Perbarui informasi atau ganti foto portfolio NH Tailor</p>
        </div>
        <div class="bg-primary/10 p-3 rounded-xl hidden sm:block">
            <i class="fas fa-edit text-primary text-xl"></i>
        </div>
    </div>

    <!-- Tampilan Pesan Error -->
    <?= $pesan; ?>

    <!-- Form Pengiriman Data -->
    <form method="post" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field(); ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Kolom Kiri: Pengaturan Berkas Gambar -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                        <i class="fas fa-image mr-2 text-primary"></i> Berkas Foto
                    </h3>
                    
                    <!-- Pratinjau Gambar Aktif Saat Ini -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Foto Saat Ini</label>
                        <div class="p-2 bg-gray-50 border border-gray-200 rounded-xl flex justify-center items-center">
                            <img src="../../foto/<?= htmlspecialchars(rawurlencode(basename((string) $pecah['foto'])), ENT_QUOTES, 'UTF-8'); ?>" alt="Foto Portfolio" class="max-h-48 rounded-lg object-cover shadow-sm">
                        </div>
                    </div>

                    <!-- Input Berkas Pengganti -->
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ganti Gambar Baru</label>
                        <input type="file" name="foto" accept="image/*"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                        <p class="text-[10px] text-red-400 mt-2">* JPG, PNG, atau WEBP, maksimal 5 MB. Biarkan kosong untuk memakai foto saat ini.</p>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Pengaturan Informasi Teks -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                    <i class="fas fa-align-left mr-2 text-primary"></i> Informasi Foto
                </h3>
                
                <!-- Textarea Keterangan -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Keterangan Singkat</label>
                    <textarea name="keterangan" rows="8" required placeholder="Tulis deskripsi atau detail baru dari karya busana..."
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm"><?= htmlspecialchars($pecah['keterangan']); ?></textarea>
                </div>

                <!-- Tombol Aksi Kendali -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-50">
                    <input type="hidden" name="edit" value="form_edit">
                    <a href="<?= panel_url('view_galeri'); ?>" class="px-6 py-2.5 text-gray-400 font-bold text-sm hover:text-gray-600 transition flex items-center">
                        Batal
                    </a>
                    <button type="submit" name="submit" class="px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg hover:bg-blue-600 transition-all hover:scale-105 outline-none">
                        Simpan Perubahan
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
