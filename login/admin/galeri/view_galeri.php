<?php
require_once __DIR__ . '/../guard.php';

    require_once __DIR__ . '/../../../koneksi.php';
  
    $query = mysqli_query($koneksi, "SELECT id, foto, keterangan FROM galeri ORDER BY id DESC");
    $data = $query ? mysqli_fetch_array($query) : null;
    $no = 1;
?>

<div class="dashboard-content">
    <div class="flex items-center justify-between mb-6">
        <a href="<?= panel_url('tambah_foto'); ?>" class="bg-primary text-white px-4 py-2 rounded shadow hover:bg-opacity-90 transition">
            <i class="fas fa-plus mr-1"></i> Tambah Foto
        </a>
    </div>

    <div class="overflow-x-auto rounded-lg">
    <table class="w-full min-w-[640px] border-collapse bg-white shadow-sm rounded-lg overflow-hidden">
        <thead>
            <tr class="bg-gray-100 border-b">
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-600 w-12">No.</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-600 w-24">Foto</th>
                <th class="px-4 py-3 text-left text-sm font-bold text-gray-600">Keterangan</th>
                <th class="px-4 py-3 text-center text-sm font-bold text-gray-600 w-40">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($data): ?>
                <?php do { ?>
                <tr class="border-b hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-sm text-gray-700"><?= $no++; ?></td>
                    <td class="px-4 py-3">
                        <img src="../../foto/<?= htmlspecialchars(rawurlencode(basename((string) $data['foto'])), ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars((string) $data['keterangan'], ENT_QUOTES, 'UTF-8'); ?>" class="w-16 h-16 object-cover rounded border" loading="lazy">
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <span class="font-medium"><?= htmlspecialchars((string) $data['keterangan'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="<?= panel_url('edit', ['id' => $data['id']]); ?>" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form method="POST" action="<?= panel_url('hapus'); ?>" onsubmit="return confirm('Yakin ingin menghapus foto ini?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int) $data['id']; ?>">
                                <button type="submit" name="hapus_foto" class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">
                                    <i class="fas fa-trash" aria-hidden="true"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php } while ($data = mysqli_fetch_array($query)); ?>
            <?php elseif (!$query): ?>
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-red-600">Galeri gagal dimuat. Silakan muat ulang halaman.</td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada data gambar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<style>
    .dashboard-content {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    table {
        border: 1px solid #e5e7eb;
    }
</style>
