<?php
require_once __DIR__ . '/../guard.php';

require_once __DIR__ . '/../../../koneksi.php';
require_once __DIR__ . '/upload_helper.php';

$pesan = "";

if (isset($_POST["add"]) && $_POST["add"] == "form1") {
    $keterangan = trim((string) ($_POST['keterangan'] ?? ''));
    $upload = gallery_save_upload($_FILES['foto'] ?? []);

    if ($keterangan === '') {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Keterangan foto wajib diisi.</div>";
    } elseif ($upload['error'] !== null) {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>" . htmlspecialchars($upload['error'], ENT_QUOTES, 'UTF-8') . "</div>";
    } elseif ($upload['filename'] === null) {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Pilih gambar untuk diunggah.</div>";
    } else {
        $statement = mysqli_prepare($koneksi, 'INSERT INTO galeri (foto, keterangan) VALUES (?, ?)');
        if ($statement === false) {
            error_log('Gallery insert preparation failed: ' . mysqli_error($koneksi));
            gallery_remove_upload($upload['filename']);
            $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Galeri gagal disimpan. Silakan coba kembali.</div>";
        } else {
            mysqli_stmt_bind_param($statement, 'ss', $upload['filename'], $keterangan);
            if (mysqli_stmt_execute($statement)) {
                mysqli_stmt_close($statement);
                echo "<script>alert('Data berhasil ditambahkan'); window.location='" . panel_url('view_galeri') . "';</script>";
                exit;
            }

            error_log('Gallery insert failed: ' . mysqli_stmt_error($statement));
            mysqli_stmt_close($statement);
            gallery_remove_upload($upload['filename']);
            $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>Galeri gagal disimpan. Silakan coba kembali.</div>";
        }
    }
}
?>

<div class="w-full">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="font-luxury text-3xl text-gray-800">Tambah Koleksi Galeri</h2>
            <p class="text-gray-500 text-sm">Unggah foto karya terbaru NH Tailor ke portfolio</p>
        </div>
        <div class="bg-primary/10 p-3 rounded-xl hidden sm:block">
            <i class="fas fa-upload text-primary text-xl"></i>
        </div>
    </div>

    <?= $pesan; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field(); ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                        <i class="fas fa-image mr-2 text-primary"></i> Berkas Foto
                    </h3>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Pilih Gambar</label>
                        <input type="file" name="foto" required accept="image/*"
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                        <p class="text-[10px] text-gray-400 mt-2">* JPG, PNG, atau WEBP, maksimal 5 MB</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                    <i class="fas fa-align-left mr-2 text-primary"></i> Informasi Foto
                </h3>
                
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Keterangan Singkat</label>
                    <textarea name="keterangan" rows="6" required placeholder="Contoh: Wedding Dress dengan detail mutiara payet..."
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-50">
                    <input type="hidden" name="add" value="form1">
                    <button type="reset" class="px-6 py-2.5 text-gray-400 font-bold text-sm hover:text-gray-600 transition">
                        Batal
                    </button>

                    <button type="submit" name="submit" class="px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg hover:bg-blue-600 transition-all hover:scale-105 outline-none">
                        Simpan ke Galeri
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>
