<?php
require_once __DIR__ . '/../guard.php';

$usernameSafe = mysqli_real_escape_string($koneksi, (string) $username);
$akun_query = mysqli_query($koneksi, "SELECT nama, nomortelepon, email FROM login WHERE username = '$usernameSafe' LIMIT 1");
$akun = $akun_query ? mysqli_fetch_assoc($akun_query) : null;

$akun_nama = $akun['nama'] ?? null;
$akun_telepon = $akun['nomortelepon'] ?? null;
$akun_email = $akun['email'] ?? null;

$nama_customer = $akun_nama ?? $username;
$telepon_customer = $akun_telepon ?? '';

$query_pesanan = "SELECT *, DATEDIFF(tgl_selesai, CURDATE()) AS days_remaining
    FROM pesanan
    WHERE username_customer = '$usernameSafe'";

if ($akun && $nama_customer !== '' && $telepon_customer !== '') {
    $query_pesanan .= " OR ((username_customer IS NULL OR username_customer = '')
        AND nama_pelanggan = '" . mysqli_real_escape_string($koneksi, (string) $nama_customer) . "'
        AND no_hp = '" . mysqli_real_escape_string($koneksi, (string) $telepon_customer) . "')";
}

$query_pesanan .= " ORDER BY id DESC";
$pesanan_query = mysqli_query($koneksi, $query_pesanan);
$total_pesanan = $pesanan_query ? mysqli_num_rows($pesanan_query) : 0;
$selesai = 0;
$diproses = 0;
$menunggu = 0;
$terlambat = 0;
$segera_jatuh_tempo = 0;
$pesanan_terdekat = null;
$pesanan_terbaru = [];

while ($row = mysqli_fetch_assoc($pesanan_query)) {
    $status = trim((string) ($row['status'] ?? '')) ?: 'Menunggu Antrian';

    if (stripos($status, 'Selesai') !== false) {
        $selesai++;
    } elseif ($status == 'Menunggu Antrian') {
        $menunggu++;
    } else {
        $diproses++;
    }

    if (stripos($status, 'Selesai') === false) {
        if ((int) $row['days_remaining'] < 0) {
            $terlambat++;
        } elseif ((int) $row['days_remaining'] <= 7) {
            $segera_jatuh_tempo++;
        }

        if ($pesanan_terdekat === null || (int) $row['days_remaining'] < (int) $pesanan_terdekat['days_remaining']) {
            $pesanan_terdekat = $row;
        }
    }

    if (count($pesanan_terbaru) < 3) {
        $pesanan_terbaru[] = $row;
    }
}
?>

<div class="w-full space-y-6">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 via-gray-900 to-stone-800 px-6 py-7 text-white shadow-sm sm:px-8" aria-labelledby="customer-dashboard-title">
        <div class="pointer-events-none absolute -right-12 -top-24 h-64 w-64 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -right-2 -top-14 h-44 w-44 rounded-full border border-white/10"></div>
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.2em] text-amber-300">Ruang pelanggan</p>
                <h2 id="customer-dashboard-title" class="font-luxury text-3xl text-white">
                    Halo, <?= htmlspecialchars((string) $nama_customer, ENT_QUOTES, 'UTF-8'); ?>
                </h2>
                <p class="mt-2 max-w-lg text-sm text-gray-300">Lihat perkembangan pesanan dan kelola informasi ukuran Anda.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= panel_url('data_ukuran'); ?>" class="inline-flex items-center gap-2 rounded-lg bg-amber-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-800">
                    <i class="fas fa-ruler-combined" aria-hidden="true"></i> Kelola Data Ukuran
                </a>
                <a href="<?= panel_url('riwayat'); ?>" class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    <i class="fas fa-history" aria-hidden="true"></i> Riwayat Pesanan
                </a>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistik pesanan Anda">
        <div class="rounded-xl border border-gray-200 border-l-4 border-l-blue-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Pesanan</span>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900"><?= $total_pesanan; ?></h3>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-shopping-bag text-lg" aria-hidden="true"></i></span>
            </div>
            <p class="mt-3 text-xs text-gray-400">Pesanan yang terhubung ke akun Anda</p>
        </div>

        <div class="rounded-xl border border-gray-200 border-l-4 border-l-rose-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Menunggu Antrian</span>
                    <h3 class="mt-2 text-3xl font-bold text-rose-600"><?= $menunggu; ?></h3>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600"><i class="fas fa-clock text-lg" aria-hidden="true"></i></span>
            </div>
            <p class="mt-3 text-xs text-gray-400">Menunggu pengerjaan dimulai</p>
        </div>

        <div class="rounded-xl border border-gray-200 border-l-4 border-l-amber-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Dalam Proses</span>
                    <h3 class="mt-2 text-3xl font-bold text-amber-600"><?= $diproses; ?></h3>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fas fa-cut text-lg" aria-hidden="true"></i></span>
            </div>
            <p class="mt-3 text-xs text-gray-400">Sedang dikerjakan oleh NH Tailor</p>
        </div>

        <div class="rounded-xl border border-gray-200 border-l-4 border-l-emerald-500 bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Selesai</span>
                    <h3 class="mt-2 text-3xl font-bold text-emerald-600"><?= $selesai; ?></h3>
                </div>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-circle-check text-lg" aria-hidden="true"></i></span>
            </div>
            <p class="mt-3 text-xs text-gray-400">Pesanan yang telah diselesaikan</p>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2" aria-label="Ringkasan tenggat pesanan">
        <div class="flex items-center justify-between gap-4 rounded-xl border border-orange-100 bg-gradient-to-r from-orange-50 to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 text-orange-600"><i class="fas fa-calendar-xmark" aria-hidden="true"></i></span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-orange-700">Lewat Tenggat</p>
                    <p class="mt-1 text-xs text-gray-500">Pesanan belum selesai melewati target</p>
                </div>
            </div>
            <span class="text-2xl font-bold <?= $terlambat > 0 ? 'text-red-600' : 'text-gray-700'; ?>"><?= $terlambat; ?></span>
        </div>
        <div class="flex items-center justify-between gap-4 rounded-xl border border-amber-100 bg-gradient-to-r from-amber-50 to-white px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700"><i class="fas fa-calendar-day" aria-hidden="true"></i></span>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Tenggat 7 Hari</p>
                    <p class="mt-1 text-xs text-gray-500">Termasuk tenggat hari ini</p>
                </div>
            </div>
            <span class="text-2xl font-bold text-amber-600"><?= $segera_jatuh_tempo; ?></span>
        </div>
    </section>

    <?php if ($pesanan_terdekat !== null): ?>
        <?php
        $hari_tersisa = (int) $pesanan_terdekat['days_remaining'];
        $tenggat_status = $hari_tersisa < 0
            ? 'Terlambat ' . abs($hari_tersisa) . ' hari'
            : ($hari_tersisa === 0 ? 'Tenggat hari ini' : 'Tersisa ' . $hari_tersisa . ' hari');
        $tenggat_warna = $hari_tersisa < 0 ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-900';
        ?>
        <div class="rounded-xl border p-5 <?= $tenggat_warna; ?>" role="status">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider opacity-75">Prioritas tenggat</p>
                    <p class="mt-1 font-bold"><?= htmlspecialchars((string) $pesanan_terdekat['jenis_pakaian'], ENT_QUOTES, 'UTF-8'); ?> · <?= htmlspecialchars($tenggat_status, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-sm">Target selesai <?= htmlspecialchars(date('d M Y', strtotime($pesanan_terdekat['tgl_selesai'])), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <a href="<?= panel_url('riwayat'); ?>" class="inline-flex shrink-0 items-center gap-2 text-sm font-bold underline">
                    Lihat riwayat <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <section class="rounded-xl border border-gray-200 bg-white p-5" aria-labelledby="customer-profile-title">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-800"><i class="fas fa-user" aria-hidden="true"></i></span>
                <div>
                    <h3 id="customer-profile-title" class="font-semibold text-gray-800">Profil Singkat</h3>
                    <p class="text-xs text-gray-400">Informasi akun Anda</p>
                </div>
            </div>
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Nama</dt>
                    <dd class="mt-1 font-medium text-gray-800"><?= htmlspecialchars((string) $nama_customer, ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Telepon</dt>
                    <dd class="mt-1 text-gray-700"><?= htmlspecialchars((string) ($telepon_customer ?: '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Email</dt>
                    <dd class="mt-1 break-all text-gray-700"><?= htmlspecialchars((string) ($akun_email ?: '-'), ENT_QUOTES, 'UTF-8'); ?></dd>
                </div>
            </dl>
            <a href="<?= panel_url('data_diri'); ?>" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-amber-800 hover:underline">
                Lihat Data Diri <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
            </a>
        </section>

        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white lg:col-span-2" aria-labelledby="customer-orders-title">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-5">
                <div>
                    <h3 id="customer-orders-title" class="font-semibold text-gray-800">Pesanan Terbaru</h3>
                    <p class="mt-1 text-xs text-gray-400">Ringkasan status pesanan Anda.</p>
                </div>
                <a href="<?= panel_url('riwayat'); ?>" class="inline-flex items-center gap-1 text-xs font-semibold text-amber-800 hover:underline">
                    Lihat Semua <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50/80 text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="p-4 pl-6">Jenis</th>
                            <th class="p-4">Target</th>
                            <th class="p-4">Progres</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 pr-6 text-center">Bantuan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        <?php if (empty($pesanan_terbaru)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Belum ada pesanan yang terhubung dengan akun Anda. Hubungi NH Tailor jika Anda merasa data pesanan belum terhubung.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($pesanan_terbaru as $pesanan): ?>
                            <?php
                            $status = trim((string) $pesanan['status']) ?: 'Menunggu Antrian';
                            $badge = "bg-gray-100 text-gray-600";
                            $persen = 10;
                            if (stripos($status, "Pemotongan Kain") !== false) { $badge = "bg-yellow-100 text-yellow-700"; $persen = 35; }
                            if (stripos($status, "Proses Menjahit") !== false) { $badge = "bg-blue-100 text-blue-700"; $persen = 65; }
                            if (stripos($status, "Fitting / Pas") !== false) { $badge = "bg-purple-100 text-purple-700"; $persen = 85; }
                            if (stripos($status, "Selesai") !== false) { $badge = "bg-green-100 text-green-700"; $persen = 100; }
                            ?>
                            <tr class="transition hover:bg-amber-50/30">
                                <td class="p-4 pl-6 font-semibold text-gray-800"><?= htmlspecialchars($pesanan['jenis_pakaian']); ?></td>
                                <td class="p-4 text-gray-500">
                                    <?= htmlspecialchars(date('d M Y', strtotime($pesanan['tgl_selesai'])), ENT_QUOTES, 'UTF-8'); ?>
                                    <?php if (stripos($status, 'Selesai') === false && (int) $pesanan['days_remaining'] < 0): ?>
                                        <span class="mt-1 block text-xs font-semibold text-red-600">Lewat <?= abs((int) $pesanan['days_remaining']); ?> hari</span>
                                    <?php elseif (stripos($status, 'Selesai') === false && (int) $pesanan['days_remaining'] <= 7): ?>
                                        <span class="mt-1 block text-xs font-semibold text-amber-700"><?= (int) $pesanan['days_remaining'] === 0 ? 'Tenggat hari ini' : 'Tersisa ' . (int) $pesanan['days_remaining'] . ' hari'; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 w-44">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full transition-all duration-500" role="progressbar" aria-label="Progres pesanan" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $persen; ?>" style="width: <?= $persen; ?>%"></div>
                                    </div>
                                    <span class="text-[11px] text-gray-400 mt-1 block font-medium"><?= $persen; ?>% Terpenuhi</span>
                                </td>
                                <td class="p-4 pr-6 text-center">
                                    <span class="inline-block rounded-full px-3 py-1 <?= $badge; ?> text-[10px] font-bold"><?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>
                                <?php
                                $pesan_bantuan = rawurlencode('Halo NH Tailor, saya ingin menanyakan pesanan #' . (int) $pesanan['id'] . ' (' . $pesanan['jenis_pakaian'] . ').');
                                ?>
                                <td class="p-4 pr-6 text-center">
                                    <a href="https://wa.me/6281370803255?text=<?= htmlspecialchars($pesan_bantuan, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 whitespace-nowrap rounded-lg border border-green-700 px-3 py-2 text-xs font-semibold text-green-800 hover:bg-green-50" aria-label="Tanyakan status pesanan #<?= (int) $pesanan['id']; ?>">
                                        <i class="fab fa-whatsapp" aria-hidden="true"></i> Tanya status
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
