<?php
require_once __DIR__ . '/../guard.php';

if (!isset($koneksi)) {
    require_once __DIR__ . '/../../../koneksi.php';
}

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan");
$r_total = mysqli_fetch_assoc($q_total);

$q_antrian = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status='' OR status='Menunggu Antrian'");
$r_antrian = mysqli_fetch_assoc($q_antrian);

$q_proses = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE status IN ('Pemotongan Kain', 'Proses Menjahit', 'Fitting / Pas')");
$r_proses = mysqli_fetch_assoc($q_proses);

$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE TRIM(status) LIKE '%Selesai%'");
$r_selesai = mysqli_fetch_assoc($q_selesai);

$q_terlambat = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE tgl_selesai < CURDATE() AND TRIM(status) NOT LIKE '%Selesai%'");
$r_terlambat = mysqli_fetch_assoc($q_terlambat);

$q_segera = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pesanan WHERE tgl_selesai BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND TRIM(status) NOT LIKE '%Selesai%'");
$r_segera = mysqli_fetch_assoc($q_segera);

$status_options = ['all', 'Menunggu Antrian', 'Pemotongan Kain', 'Proses Menjahit', 'Fitting / Pas', 'Selesai'];
$status_filter_param = $_GET['status_filter'] ?? 'all';
$status_filter = is_string($status_filter_param) && in_array($status_filter_param, $status_options, true)
    ? $status_filter_param
    : 'all';
$search_param = $_GET['search_pesanan'] ?? '';
$search_pesanan = is_string($search_param) ? substr(trim($search_param), 0, 100) : '';

$filter_sql = '';
if ($status_filter === 'Menunggu Antrian') {
    $filter_sql .= " AND (TRIM(status) = '' OR TRIM(status) = 'Menunggu Antrian') ";
} elseif ($status_filter !== 'all') {
    $safe_status_filter = mysqli_real_escape_string($koneksi, $status_filter);
    $filter_sql .= " AND TRIM(status) = '$safe_status_filter' ";
}
if ($search_pesanan !== '') {
    $token = mysqli_real_escape_string($koneksi, '%' . $search_pesanan . '%');
    $filter_sql .= " AND (
        nama_pelanggan LIKE '$token'
        OR no_hp LIKE '$token'
        OR jenis_pakaian LIKE '$token'
        OR username_customer LIKE '$token'
    )";
}

$pesanan_perhatian = mysqli_query($koneksi, "SELECT *, DATEDIFF(tgl_selesai, CURDATE()) AS days_remaining
    FROM pesanan
    WHERE tgl_selesai <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND TRIM(status) NOT LIKE '%Selesai%'
        $filter_sql
    ORDER BY tgl_selesai ASC, id DESC
    LIMIT 5");

$pesanan_terbaru = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE 1=1 $filter_sql ORDER BY id DESC LIMIT 5");
?>

<div class="w-full space-y-6">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 via-gray-900 to-stone-800 px-6 py-7 text-white shadow-sm sm:px-8" aria-labelledby="dashboard-title">
        <div class="pointer-events-none absolute -right-12 -top-24 h-64 w-64 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -right-2 -top-14 h-44 w-44 rounded-full border border-white/10"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Panel pengelolaan</p>
                <h2 id="dashboard-title" class="font-luxury text-3xl text-white">Ringkasan Bisnis</h2>
                <p class="mt-2 max-w-lg text-sm text-gray-300">Pantau pesanan, progres jahitan, dan tenggat pengerjaan dalam satu tempat.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= panel_url('buat_pesanan'); ?>" class="inline-flex items-center gap-2 rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-800">
                    <i class="fas fa-plus" aria-hidden="true"></i> Buat Pesanan
                </a>
                <a href="<?= panel_url('daftar_member'); ?>" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    <i class="fas fa-user-plus" aria-hidden="true"></i> Member
                </a>
                <a href="<?= panel_url('tambah_foto'); ?>" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    <i class="fas fa-camera" aria-hidden="true"></i> Tambah Karya
                </a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistik pesanan">
        <!-- Kartu 1: Total Pesanan -->
        <div class="group rounded-xl border border-gray-200 border-l-4 border-l-blue-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pesanan</span>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900"><?= $r_total['total']; ?></h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-shopping-bag text-lg" aria-hidden="true"></i>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Seluruh pesanan terdaftar</p>
        </div>

        <!-- Kartu 2: Menunggu -->
        <div class="group rounded-xl border border-gray-200 border-l-4 border-l-rose-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Menunggu Antrian</span>
                    <h3 class="mt-2 text-3xl font-bold text-rose-600"><?= $r_antrian['total']; ?></h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <i class="fas fa-clock text-lg" aria-hidden="true"></i>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Siap masuk proses pengerjaan</p>
        </div>

        <!-- Kartu 3: Sedang Diproses -->
        <div class="group rounded-xl border border-gray-200 border-l-4 border-l-amber-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Dalam Proses</span>
                    <h3 class="mt-2 text-3xl font-bold text-amber-600"><?= $r_proses['total']; ?></h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <i class="fas fa-cut text-lg" aria-hidden="true"></i>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Sedang dikerjakan di studio</p>
        </div>

        <!-- Kartu 4: Selesai -->
        <div class="group rounded-xl border border-gray-200 border-l-4 border-l-emerald-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Selesai</span>
                    <h3 class="mt-2 text-3xl font-bold text-emerald-600"><?= $r_selesai['total']; ?></h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <i class="fas fa-check-double text-lg" aria-hidden="true"></i>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Pesanan telah diselesaikan</p>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2" aria-label="Ringkasan tenggat">
        <div class="flex items-center justify-between gap-4 rounded-xl border border-orange-100 bg-gradient-to-r from-orange-50 to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-orange-700">Lewat Tenggat</p>
                    <p class="mt-1 text-xs text-gray-500">Pesanan belum selesai melewati target</p>
                </div>
            </div>
            <span class="text-2xl font-bold text-orange-600"><?= (int) $r_terlambat['total']; ?></span>
        </div>
        <div class="flex items-center justify-between gap-4 rounded-xl border border-amber-100 bg-gradient-to-r from-amber-50 to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><i class="fas fa-calendar-day" aria-hidden="true"></i></span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Tenggat 7 Hari</p>
                    <p class="mt-1 text-xs text-gray-500">Termasuk tenggat hari ini</p>
                </div>
            </div>
            <span class="text-2xl font-bold text-amber-600"><?= (int) $r_segera['total']; ?></span>
        </div>
    </section>

    <form method="GET" action="<?= panel_url(); ?>" class="bg-white rounded-xl border border-gray-200 p-4 sm:p-5 space-y-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="font-bold text-gray-800">Filter Pesanan</h3>
                <p class="text-xs text-gray-400">Saring ringkasan di beranda. Untuk mencari seluruh pesanan, buka Progres Kerja.</p>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
                <a href="<?= panel_url('progres_kerja'); ?>" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 font-semibold text-primary hover:bg-gray-50">
                    <i class="fas fa-list-check"></i> Pencarian lengkap
                </a>
                <a href="<?= panel_url(); ?>" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-semibold text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-rotate-left"></i> Reset
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-[minmax(0,1.5fr)_minmax(180px,0.9fr)] gap-3">
            <div class="relative">
                <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input
                    type="search"
                    name="search_pesanan"
                    value="<?= htmlspecialchars((string) $search_pesanan, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Cari nama pelanggan / WhatsApp / jenis pakaian"
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-3 text-sm text-gray-700 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                >
            </div>

            <div class="relative">
                <select name="status_filter" class="w-full appearance-none rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 pr-9 text-sm text-gray-700 outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10">
                    <option value="all" <?= $status_filter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="Menunggu Antrian" <?= $status_filter === 'Menunggu Antrian' ? 'selected' : ''; ?>>Menunggu Antrian</option>
                    <option value="Pemotongan Kain" <?= $status_filter === 'Pemotongan Kain' ? 'selected' : ''; ?>>Pemotongan Kain</option>
                    <option value="Proses Menjahit" <?= $status_filter === 'Proses Menjahit' ? 'selected' : ''; ?>>Proses Menjahit</option>
                    <option value="Fitting / Pas" <?= $status_filter === 'Fitting / Pas' ? 'selected' : ''; ?>>Fitting / Pas</option>
                    <option value="Selesai" <?= $status_filter === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500">
            <span>Menampilkan <?= mysqli_num_rows($pesanan_terbaru); ?> pesanan terbaru</span>
            <?php if ($search_pesanan !== '' || $status_filter !== 'all'): ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 font-semibold text-primary">
                    <i class="fas fa-filter"></i>
                    Filter aktif
                </span>
            <?php endif; ?>
        </div>

        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-opacity-90">
            <i class="fas fa-filter"></i> Terapkan Filter
        </button>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex flex-wrap justify-between items-center gap-3">
            <div>
                <h3 class="font-bold text-gray-800">Perlu Perhatian</h3>
                <p class="text-xs text-gray-400">Pesanan yang tenggatnya terlewati atau jatuh tempo dalam 7 hari.</p>
            </div>
            <a href="<?= panel_url('progres_kerja'); ?>" class="text-xs font-bold text-primary hover:underline">
                Kelola progres <i class="fas fa-arrow-right ml-1 text-[10px]" aria-hidden="true"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-4 pl-6">Pelanggan</th>
                        <th class="p-4">Jenis Pakaian</th>
                        <th class="p-4">Tenggat</th>
                        <th class="p-4 pr-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-600">
                    <?php if (mysqli_num_rows($pesanan_perhatian) === 0): ?>
                        <tr><td colspan="4" class="p-7 text-center text-sm text-gray-400">Tidak ada pesanan yang perlu perhatian dalam rentang ini.</td></tr>
                    <?php endif; ?>
                    <?php while ($row = mysqli_fetch_assoc($pesanan_perhatian)): ?>
                        <?php
                        $daysRemaining = (int) $row['days_remaining'];
                        $deadlineLabel = $daysRemaining < 0
                            ? 'Terlambat ' . abs($daysRemaining) . ' hari'
                            : ($daysRemaining === 0 ? 'Hari ini' : 'Dalam ' . $daysRemaining . ' hari');
                        $deadlineClass = $daysRemaining < 0
                            ? 'bg-red-50 text-red-700'
                            : 'bg-amber-50 text-amber-700';
                        $orderStatus = trim((string) $row['status']) ?: 'Menunggu Antrian';
                        ?>
                        <tr class="<?= $daysRemaining < 0 ? 'bg-red-50/30' : ''; ?>">
                            <td class="p-4 pl-6 font-semibold text-gray-800"><?= htmlspecialchars((string) $row['nama_pelanggan'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="p-4 text-xs"><?= htmlspecialchars((string) $row['jenis_pakaian'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="p-4">
                                <span class="block text-xs text-gray-500"><?= htmlspecialchars(date('d M Y', strtotime($row['tgl_selesai'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="mt-1 inline-block rounded-full px-2.5 py-1 text-[10px] font-bold <?= $deadlineClass; ?>"><?= htmlspecialchars($deadlineLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td class="p-4 pr-6 text-center text-xs"><?= htmlspecialchars($orderStatus, ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SEKSI 2: TABEL PROGRESS TERBARU -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h4 class="font-bold text-gray-800">Aktivitas Pesanan Terbaru</h4>
                <p class="text-xs text-gray-400">5 transaksi pengerjaan busana terakhir</p>
            </div>
            <a href="<?= panel_url('progres_kerja'); ?>" class="text-xs font-bold text-primary hover:underline flex items-center">
                Lihat Semua Progres <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4 pl-6">Pelanggan</th>
                        <th class="p-4">Jenis Pakaian</th>
                        <th class="p-4">Target Selesai</th>
                        <th class="p-4">Progres</th>
                        <th class="p-4 pr-6 text-center">Status Kerja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-600">
                    <?php if (mysqli_num_rows($pesanan_terbaru) == 0): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Belum ada data pesanan masuk.</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php while($row = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                        <tr class="hover:bg-gray-50/40 transition">
                            <td class="p-4 pl-6 font-semibold text-gray-800">
                                <?= htmlspecialchars((string) $row['nama_pelanggan'], ENT_QUOTES, 'UTF-8'); ?>
                                <span class="block text-[11px] font-normal text-gray-400"><?= htmlspecialchars((string) $row['no_hp'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td class="p-4 text-xs font-medium text-gray-500"><?= htmlspecialchars((string) $row['jenis_pakaian'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="p-4 text-xs"><?= htmlspecialchars(date('d M Y', strtotime($row['tgl_selesai'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="p-4 w-48">
                                <?php 
                                $status = $row['status'] ?? 'Menunggu Antrian';
                                if (empty($status)) $status = 'Menunggu Antrian';

                                $badge = "bg-gray-100 text-gray-600";
                                $persen = 10;
                                
                                if (stripos($status, "Pemotongan Kain") !== false) { $badge = "bg-yellow-100 text-yellow-700"; $persen = 35; }
                                if (stripos($status, "Proses Menjahit") !== false) { $badge = "bg-blue-100 text-blue-700"; $persen = 65; }
                                if (stripos($status, "Fitting / Pas") !== false) { $badge = "bg-purple-100 text-purple-700"; $persen = 85; }
                                if (stripos($status, "Selesai") !== false) { $badge = "bg-green-100 text-green-700"; $persen = 100; }
                                ?>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: <?= $persen; ?>%"></div>
                                </div>
                                <span class="text-[11px] text-gray-400 mt-1 block font-medium"><?= $persen; ?>% Terpenuhi</span>
                            </td>
                            <td class="p-4 pr-6 text-center">
                                <span class="px-3 py-1 inline-block <?= $badge; ?> text-[10px] font-bold rounded-full">
                                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
