<?php
require_once __DIR__ . '/../guard.php';

require_once __DIR__ . '/../../../koneksi.php';
include_once __DIR__ . '/../fonnte_helper.php';

$status_opsi = ['Menunggu Antrian', 'Pemotongan Kain', 'Proses Menjahit', 'Fitting / Pas', 'Selesai'];
$pesan_update = '';
$pesan_update_type = 'error';

if (isset($_POST['update_status'])) {
    $id_pesanan = filter_var($_POST['id_pesanan'] ?? null, FILTER_VALIDATE_INT);
    $status_baru = $_POST['status_baru'] ?? null;

    if (!$id_pesanan || !is_string($status_baru) || !in_array($status_baru, $status_opsi, true)) {
        $pesan_update = 'Data progres tidak valid. Muat ulang halaman dan coba kembali.';
    } else {
        $pesananStatement = mysqli_prepare($koneksi, 'SELECT nama_pelanggan, no_hp, jenis_pakaian, tgl_selesai, status FROM pesanan WHERE id = ? LIMIT 1');
        if ($pesananStatement === false) {
            error_log('Order status lookup failed: ' . mysqli_error($koneksi));
            $pesan_update = 'Gagal memuat pesanan. Silakan coba kembali.';
        } else {
            mysqli_stmt_bind_param($pesananStatement, 'i', $id_pesanan);
            if (!mysqli_stmt_execute($pesananStatement)) {
                error_log('Order status lookup failed: ' . mysqli_stmt_error($pesananStatement));
                mysqli_stmt_close($pesananStatement);
                $pesan_update = 'Gagal memuat pesanan. Silakan coba kembali.';
            } else {
                mysqli_stmt_bind_result($pesananStatement, $nama_pelanggan, $no_hp, $jenis_pakaian, $tgl_selesai, $status_lama);
                $pesanan_ditemukan = mysqli_stmt_fetch($pesananStatement);
                mysqli_stmt_close($pesananStatement);

                if (!$pesanan_ditemukan) {
                    $pesan_update = 'Pesanan tidak ditemukan.';
                } else {
                    $updateStatement = mysqli_prepare($koneksi, 'UPDATE pesanan SET status = ? WHERE id = ?');
                    if ($updateStatement === false) {
                        error_log('Order status update preparation failed: ' . mysqli_error($koneksi));
                        $pesan_update = 'Gagal memperbarui progres. Silakan coba kembali.';
                    } else {
                        mysqli_stmt_bind_param($updateStatement, 'si', $status_baru, $id_pesanan);
                        $updateBerhasil = mysqli_stmt_execute($updateStatement);
                        $updateError = $updateBerhasil ? '' : mysqli_stmt_error($updateStatement);
                        mysqli_stmt_close($updateStatement);

                        if ($updateBerhasil) {
                            if ($status_lama !== $status_baru) {
                                $notifikasi = fonnte_notify_status_updated([
                                    'nama_pelanggan' => $nama_pelanggan,
                                    'no_hp' => $no_hp,
                                    'jenis_pakaian' => $jenis_pakaian,
                                    'tgl_selesai' => $tgl_selesai,
                                ], $status_baru);
                                $pesan_update = !empty($notifikasi['success'])
                                    ? 'Progres berhasil diperbarui dan notifikasi WhatsApp terkirim.'
                                    : 'Progres berhasil diperbarui, tetapi notifikasi WhatsApp belum terkirim: ' .
                                        ($notifikasi['message'] ?? 'Fonnte tidak merespons.');
                                $pesan_update_type = !empty($notifikasi['success']) ? 'success' : 'warning';
                            } else {
                                $pesan_update = 'Status pesanan tidak berubah.';
                                $pesan_update_type = 'success';
                            }
                        } else {
                            error_log('Order status update failed: ' . $updateError);
                            $pesan_update = 'Gagal memperbarui progres. Silakan coba kembali.';
                        }
                    }
                }
            }
        }
    }
}

$status_filter_param = $_GET['status_filter'] ?? 'all';
$status_filter = is_string($status_filter_param)
    && in_array($status_filter_param, ['all', ...$status_opsi], true)
        ? $status_filter_param
        : 'all';
$search_param = $_GET['search_pesanan'] ?? '';
$search_pesanan = is_string($search_param) ? substr(trim($search_param), 0, 100) : '';
$filter_conditions = [];

if ($status_filter === 'Menunggu Antrian') {
    $filter_conditions[] = "TRIM(status) IN ('', 'Menunggu Antrian')";
} elseif ($status_filter !== 'all') {
    $filter_conditions[] = "TRIM(status) = '" . mysqli_real_escape_string($koneksi, $status_filter) . "'";
}

if ($search_pesanan !== '') {
    $searchToken = '%' . mysqli_real_escape_string($koneksi, $search_pesanan) . '%';
    $filter_conditions[] = "(nama_pelanggan LIKE '$searchToken' OR no_hp LIKE '$searchToken' OR jenis_pakaian LIKE '$searchToken')";
}

$whereSql = $filter_conditions ? 'WHERE ' . implode(' AND ', $filter_conditions) : '';
$countQuery = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan $whereSql");
$totalPesanan = $countQuery ? (int) (mysqli_fetch_assoc($countQuery)['total'] ?? 0) : 0;
$perPage = 20;
$totalHalaman = max(1, (int) ceil($totalPesanan / $perPage));
$pageParam = $_GET['page'] ?? '1';
$page = is_string($pageParam) && ctype_digit($pageParam) ? max(1, (int) $pageParam) : 1;
$page = min($page, $totalHalaman);
$offset = ($page - 1) * $perPage;
$ambil = mysqli_query($koneksi, "SELECT * FROM pesanan $whereSql ORDER BY id DESC LIMIT $perPage OFFSET $offset");
$rangeStart = $totalPesanan === 0 ? 0 : $offset + 1;
$rangeEnd = min($offset + $perPage, $totalPesanan);
?>

<div class="mx-4 mt-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="mb-4">
        <h3 class="font-bold text-gray-800">Cari & Filter Pesanan</h3>
        <p class="text-xs text-gray-400">Cari pelanggan, WhatsApp, atau jenis pakaian; hasil diurutkan dari pesanan terbaru.</p>
    </div>
    <form method="GET" action="<?= panel_url('progres_kerja'); ?>" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto_auto]">
        <input type="search" name="search_pesanan" value="<?= htmlspecialchars($search_pesanan, ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" placeholder="Nama, WhatsApp, atau jenis pakaian" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
        <select name="status_filter" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
            <option value="all" <?= $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
            <?php foreach ($status_opsi as $status_opsi_item): ?>
                <option value="<?= htmlspecialchars($status_opsi_item, ENT_QUOTES, 'UTF-8'); ?>" <?= $status_filter === $status_opsi_item ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($status_opsi_item, ENT_QUOTES, 'UTF-8'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white hover:bg-amber-600">
            <i class="fas fa-filter" aria-hidden="true"></i> Terapkan
        </button>
        <a href="<?= panel_url('progres_kerja'); ?>" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">Reset</a>
    </form>
    <p class="mt-3 text-xs text-gray-500" aria-live="polite">
        <?php if (!$countQuery || !$ambil): ?>
            Daftar pesanan gagal dimuat. Silakan coba kembali.
        <?php else: ?>
            Menampilkan <?= $rangeStart; ?>–<?= $rangeEnd; ?> dari <?= $totalPesanan; ?> pesanan.
        <?php endif; ?>
    </p>
</div>

<!-- Grid Kartu Progres -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
    <?php if ($pesan_update !== ''): ?>
        <div class="col-span-full rounded-xl border p-4 text-sm <?= $pesan_update_type === 'success' ? 'border-green-200 bg-green-50 text-green-700' : ($pesan_update_type === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-red-200 bg-red-50 text-red-700'); ?>" role="alert">
            <?= htmlspecialchars($pesan_update, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <?php
    if ($ambil && mysqli_num_rows($ambil) > 0):
        while ($pecah = mysqli_fetch_array($ambil)): 
    ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between space-y-4">
                
                <!-- Bagian Atas: Info Pelanggan & Jenis Pakaian -->
                <div>
                    <div class="flex justify-between items-start mb-3">
                        <span class="px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full">
                            <?= htmlspecialchars($pecah['jenis_pakaian'] ?? 'Pakaian'); ?>
                        </span>
                        
                        <?php 
                        // Ambil status, jika kosong beri default 'Menunggu Antrian'
                        $status = $pecah['status'] ?? 'Menunggu Antrian';
                        if (empty($status)) { $status = 'Menunggu Antrian'; }
                        
                        // Penentuan warna badge status
                        $warna_bg = "bg-gray-100 text-gray-600";
                        if ($status == "Pemotongan Kain") $warna_bg = "bg-yellow-100 text-yellow-700";
                        if ($status == "Proses Menjahit") $warna_bg = "bg-blue-100 text-blue-700";
                        if ($status == "Fitting / Pas") $warna_bg = "bg-purple-100 text-purple-700";
                        if ($status == "Selesai") $warna_bg = "bg-green-100 text-green-700";
                        ?>
                        <span class="px-3 py-1 <?= $warna_bg; ?> text-xs font-bold rounded-full">
                            <?= htmlspecialchars($status); ?>
                        </span>
                    </div>

                    <h4 class="font-bold text-gray-800 text-lg mb-1">
                        <?= htmlspecialchars($pecah['nama_pelanggan'] ?? 'Tanpa Nama'); ?>
                    </h4>
                    <p class="text-xs text-gray-400 mb-3">
                        <i class="fab fa-whatsapp text-green-500 mr-1"></i> <?= htmlspecialchars($pecah['no_hp'] ?? '-'); ?>
                    </p>
                    
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-xs text-gray-600 space-y-1">
                        <p><strong>Detail Model:</strong> <?= empty($pecah['detail_model']) ? '-' : htmlspecialchars($pecah['detail_model']); ?></p>
                        <?php if (!empty($pecah['kain_foto'])): ?>
                            <?php $foto_kain = basename((string) $pecah['kain_foto']); ?>
                            <div class="pt-2">
                                <p class="mb-2 font-bold text-gray-700">Foto Kain Customer</p>
                                <a href="../../foto/<?= rawurlencode($foto_kain); ?>" target="_blank" rel="noopener noreferrer">
                                    <img src="../../foto/<?= rawurlencode($foto_kain); ?>" alt="Foto kain milik <?= htmlspecialchars($pecah['nama_pelanggan'] ?? 'customer', ENT_QUOTES, 'UTF-8'); ?>" class="h-40 w-full rounded-lg border border-gray-200 bg-white object-contain">
                                </a>
                            </div>
                        <?php endif; ?>
                        <p><strong>Target Selesai:</strong> 
                            <span class="text-red-500 font-semibold">
                                <?= (!empty($pecah['tgl_selesai']) && $pecah['tgl_selesai'] != '0000-00-00') ? date('d M Y', strtotime($pecah['tgl_selesai'])) : '-'; ?>
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Bagian Bawah: Form Pengubah Progres Kerja -->
                <div class="pt-4 border-t border-gray-50">
                    <form method="POST" action="" class="flex items-center space-x-2">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id_pesanan" value="<?= $pecah['id']; ?>">
                        
                        <select name="status_baru" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <?php foreach ($status_opsi as $status_opsi_item): ?>
                                <option value="<?= htmlspecialchars($status_opsi_item, ENT_QUOTES, 'UTF-8'); ?>" <?= $status === $status_opsi_item ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($status_opsi_item, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" name="update_status" class="px-4 py-2 bg-primary text-white text-xs font-bold rounded-lg shadow-md hover:bg-blue-600 transition-all outline-none flex items-center shrink-0">
                            <i class="fas fa-sync-alt mr-1"></i> Update
                        </button>
                    </form>
                </div>

            </div>
    <?php 
        endwhile; 
    elseif ($ambil):
    ?>
        <!-- Tampilan jika database kosong -->
        <div class="col-span-full text-center py-12 text-gray-400 text-sm">
            Belum ada data pesanan yang tersedia.
        </div>
    <?php endif; ?>
</div>

<?php if ($totalHalaman > 1): ?>
    <nav class="flex flex-wrap items-center justify-center gap-2 px-4 pb-6" aria-label="Halaman daftar pesanan">
        <?php if ($page > 1): ?>
            <a href="<?= panel_url('progres_kerja', ['search_pesanan' => $search_pesanan, 'status_filter' => $status_filter, 'page' => $page - 1]); ?>" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Sebelumnya</a>
        <?php endif; ?>
        <span class="px-3 py-2 text-sm font-semibold text-gray-500">Halaman <?= $page; ?> dari <?= $totalHalaman; ?></span>
        <?php if ($page < $totalHalaman): ?>
            <a href="<?= panel_url('progres_kerja', ['search_pesanan' => $search_pesanan, 'status_filter' => $status_filter, 'page' => $page + 1]); ?>" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">Berikutnya</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
