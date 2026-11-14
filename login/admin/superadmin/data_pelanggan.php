<?php
require_once __DIR__ . '/../guard.php';

if (!function_exists('admin_pelanggan_e')) {
    function admin_pelanggan_e($value) {
        if ($value === null || $value === '') {
            return '-';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('admin_pelanggan_cm')) {
    function admin_pelanggan_cm($value) {
        if ($value === null || $value === '') {
            return '-';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . ' cm';
    }
}

if (!function_exists('admin_pelanggan_tanggal')) {
    function admin_pelanggan_tanggal($tanggal, $format = 'd M Y') {
        if (empty($tanggal) || $tanggal == '0000-00-00') {
            return '-';
        }

        $waktu = strtotime($tanggal);
        if (!$waktu) {
            return '-';
        }

        return date($format, $waktu);
    }
}

if (!function_exists('admin_pelanggan_rupiah')) {
    function admin_pelanggan_rupiah($angka) {
        return 'Rp ' . number_format((int) $angka, 0, ',', '.');
    }
}

if (!function_exists('admin_pelanggan_status_badge')) {
    function admin_pelanggan_status_badge($status) {
        $status = $status ?: 'Menunggu Antrian';
        $badge = 'bg-gray-100 text-gray-600';

        if (stripos($status, 'Pemotongan Kain') !== false) {
            $badge = 'bg-yellow-100 text-yellow-700';
        }
        if (stripos($status, 'Proses Menjahit') !== false) {
            $badge = 'bg-blue-100 text-blue-700';
        }
        if (stripos($status, 'Fitting / Pas') !== false) {
            $badge = 'bg-purple-100 text-purple-700';
        }
        if (stripos($status, 'Selesai') !== false) {
            $badge = 'bg-green-100 text-green-700';
        }

        return $badge;
    }
}

$q_total_customer = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM login WHERE rule='customer'");
$total_customer = $q_total_customer ? (mysqli_fetch_assoc($q_total_customer) ?: ['total' => 0]) : ['total' => 0];

$q_customer_aktif = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM login WHERE rule='customer' AND status='Y'");
$customer_aktif = $q_customer_aktif ? (mysqli_fetch_assoc($q_customer_aktif) ?: ['total' => 0]) : ['total' => 0];

$q_total_ukuran = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM ukuran_customer");
$total_ukuran = $q_total_ukuran ? (mysqli_fetch_assoc($q_total_ukuran) ?: ['total' => 0]) : ['total' => 0];

$q_total_pesanan = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT p.id) AS total
    FROM pesanan p
    INNER JOIN login l ON l.rule='customer'
    WHERE p.username_customer = l.username
       OR (
            (p.username_customer IS NULL OR p.username_customer = '')
            AND l.nama IS NOT NULL AND l.nama != ''
            AND l.nomortelepon IS NOT NULL AND l.nomortelepon != ''
            AND p.nama_pelanggan = l.nama
            AND p.no_hp = l.nomortelepon
       )
");
$total_pesanan = $q_total_pesanan ? (mysqli_fetch_assoc($q_total_pesanan) ?: ['total' => 0]) : ['total' => 0];

$pelanggan_result = mysqli_query($koneksi, "
    SELECT 
        l.id, l.nama, l.username, l.nomortelepon, l.email, l.alamat, l.status, l.rule,
        (
            SELECT COUNT(DISTINCT p.id)
            FROM pesanan p
            WHERE p.username_customer = l.username
               OR (
                    (p.username_customer IS NULL OR p.username_customer = '')
                    AND l.nama IS NOT NULL AND l.nama != ''
                    AND l.nomortelepon IS NOT NULL AND l.nomortelepon != ''
                    AND p.nama_pelanggan = l.nama
                    AND p.no_hp = l.nomortelepon
               )
        ) AS total_pesanan,
        (
            SELECT COUNT(*)
            FROM ukuran_customer u
            WHERE u.username = l.username
        ) AS total_ukuran,
        (
            SELECT MAX(p.tgl_selesai)
            FROM pesanan p
            WHERE p.username_customer = l.username
               OR (
                    (p.username_customer IS NULL OR p.username_customer = '')
                    AND l.nama IS NOT NULL AND l.nama != ''
                    AND l.nomortelepon IS NOT NULL AND l.nomortelepon != ''
                    AND p.nama_pelanggan = l.nama
                    AND p.no_hp = l.nomortelepon
               )
        ) AS pesanan_terakhir
    FROM login l
    WHERE l.rule='customer'
    ORDER BY l.id DESC
");

$pelanggan_rows = [];
if ($pelanggan_result) {
    while ($row = mysqli_fetch_assoc($pelanggan_result)) {
        $pelanggan_rows[] = $row;
    }
}

$selected_username_param = $_GET['username'] ?? '';
$selected_username = is_string($selected_username_param) ? trim($selected_username_param) : '';
if ($selected_username === '' && !empty($pelanggan_rows)) {
    $selected_username = (string) $pelanggan_rows[0]['username'];
}

$detail_customer = null;
foreach ($pelanggan_rows as $row) {
    if ((string) $row['username'] === $selected_username) {
        $detail_customer = $row;
        break;
    }
}

$ukuran_rows = [];
$pesanan_rows = [];
$ukuran_field_labels = [
    'lebar_punggung' => 'Lebar Punggung',
    'panjang_punggung' => 'Panjang Punggung',
    'panjang_pinggang' => 'Panjang Pinggang',
    'panjang_panggul' => 'Panjang Panggul',
    'lingkar_dada' => 'Lingkar Dada',
    'lingkar_pinggang' => 'Lingkar Pinggang',
    'lingkar_pinggul' => 'Lingkar Panggul',
    'lebar_bahu' => 'Lebar Bahu',
    'panjang_muka' => 'Panjang Muka',
    'panjang_baju' => 'Panjang Baju',
    'lebar_muka' => 'Lebar Muka',
    'panjang_lengan' => 'Panjang Lengan',
    'lingkar_lengan' => 'Lingkar Lengan',
    'kerung_lengan' => 'Kerung Lengan',
    'lingkar_pinggang_celana' => 'Lingkar Pinggang Celana',
    'lingkar_pesak' => 'Lingkar Pesak',
    'lingkar_paha' => 'Lingkar Paha',
    'lingkar_betis_bawah' => 'Lingkar Betis Bawah',
    'panjang_celana' => 'Panjang Celana',
    'lingkar_sabrina' => 'Lingkar Sabrina',
    'tinggi_leher' => 'Tinggi Leher',
    'panjang_rok' => 'Panjang Rok',
];

if ($detail_customer) {
    $detail_username = mysqli_real_escape_string($koneksi, $detail_customer['username']);
    $detail_nama = mysqli_real_escape_string($koneksi, $detail_customer['nama'] ?? '');
    $detail_telepon = mysqli_real_escape_string($koneksi, $detail_customer['nomortelepon'] ?? '');

    $ukuran_result = mysqli_query($koneksi, "
        SELECT *
        FROM ukuran_customer
        WHERE username='$detail_username'
        ORDER BY id DESC
    ");
    if ($ukuran_result) {
        while ($row = mysqli_fetch_assoc($ukuran_result)) {
            $ukuran_rows[] = $row;
        }
    }

    $where_pesanan = "p.username_customer='$detail_username'";
    if (!empty($detail_customer['nama']) && !empty($detail_customer['nomortelepon'])) {
        $where_pesanan .= " OR (
            (p.username_customer IS NULL OR p.username_customer = '')
            AND p.nama_pelanggan='$detail_nama'
            AND p.no_hp='$detail_telepon'
        )";
    }

    $pesanan_result = mysqli_query($koneksi, "
        SELECT *
        FROM pesanan p
        WHERE $where_pesanan
        ORDER BY p.id DESC
    ");
    if ($pesanan_result) {
        while ($row = mysqli_fetch_assoc($pesanan_result)) {
            $pesanan_rows[] = $row;
        }
    }
}
?>

<div class="w-full space-y-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="font-luxury text-3xl text-gray-800">Data Pelanggan</h2>
            <p class="text-gray-500 text-sm">Pantau profil, pesanan, dan ukuran pelanggan yang tersimpan dari dashboard customer.</p>
        </div>
        <a href="<?= panel_url('buat_pesanan'); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl text-sm font-bold shadow-sm hover:bg-amber-600 transition">
            <i class="fas fa-pen-nib"></i>
            Input Pesanan
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pelanggan</span>
                <h3 class="text-3xl font-extrabold text-gray-800"><?= (int) $total_customer['total']; ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Akun Aktif</span>
                <h3 class="text-3xl font-extrabold text-green-600"><?= (int) $customer_aktif['total']; ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-600 text-xl">
                <i class="fas fa-user-check"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pesanan Terhubung</span>
                <h3 class="text-3xl font-extrabold text-primary"><?= (int) $total_pesanan['total']; ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-primary text-xl">
                <i class="fas fa-shopping-bag"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Data Ukuran</span>
                <h3 class="text-3xl font-extrabold text-purple-600"><?= (int) $total_ukuran['total']; ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600 text-xl">
                <i class="fas fa-ruler-combined"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50">
            <h4 class="font-bold text-gray-800">Daftar Customer</h4>
            <p class="text-xs text-gray-400">Pilih salah satu customer untuk melihat profil dan ukuran secara rinci.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4 pl-6">Pelanggan</th>
                        <th class="p-4">Kontak</th>
                        <th class="p-4">Alamat</th>
                        <th class="p-4 text-center">Pesanan</th>
                        <th class="p-4 text-center">Ukuran</th>
                        <th class="p-4">Pesanan Terakhir</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-600">
                    <?php if (empty($pelanggan_rows)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400 text-xs">Belum ada akun customer yang terdaftar.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($pelanggan_rows as $row): ?>
                        <?php
                        $status_aktif = ($row['status'] ?? '') == 'Y';
                        $badge = $status_aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600';
                        $terpilih = (string) $row['username'] === $selected_username;
                        ?>
                        <tr class="<?= $terpilih ? 'bg-amber-50/70' : 'hover:bg-gray-50/40'; ?> transition align-top">
                            <td class="p-4 pl-6">
                                <span class="font-semibold text-gray-800"><?= admin_pelanggan_e($row['nama'] ?? ''); ?></span>
                                <span class="block text-[11px] text-gray-400">@<?= admin_pelanggan_e($row['username'] ?? ''); ?></span>
                            </td>
                            <td class="p-4 text-xs">
                                <span class="block text-gray-700 font-semibold"><?= admin_pelanggan_e($row['nomortelepon'] ?? ''); ?></span>
                                <span class="block text-gray-400 mt-1"><?= admin_pelanggan_e($row['email'] ?? ''); ?></span>
                            </td>
                            <td class="p-4 text-xs max-w-xs"><?= admin_pelanggan_e($row['alamat'] ?? ''); ?></td>
                            <td class="p-4 text-center font-bold text-gray-800"><?= (int) $row['total_pesanan']; ?></td>
                            <td class="p-4 text-center font-bold text-gray-800"><?= (int) $row['total_ukuran']; ?></td>
                            <td class="p-4 text-xs"><?= admin_pelanggan_tanggal($row['pesanan_terakhir'] ?? ''); ?></td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 inline-block <?= $badge; ?> text-[10px] font-bold rounded-full">
                                    <?= $status_aktif ? 'Aktif' : 'Nonaktif'; ?>
                                </span>
                            </td>
                            <td class="p-4 pr-6 text-right">
                                <a href="<?= panel_url('data_pelanggan', ['username' => $row['username']]); ?>" class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg <?= $terpilih ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?> text-xs font-bold transition">
                                    <i class="fas fa-eye"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($detail_customer): ?>
        <?php
        $nama_detail = $detail_customer['nama'] ?: $detail_customer['username'];
        $inisial = strtoupper(substr($nama_detail ?: 'CU', 0, 2));
        $akun_aktif = ($detail_customer['status'] ?? '') == 'Y';
        ?>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex items-center gap-4">
                    <div class="h-14 w-14 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg">
                        <?= admin_pelanggan_e($inisial); ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800"><?= admin_pelanggan_e($nama_detail); ?></h3>
                        <p class="text-xs text-gray-400">@<?= admin_pelanggan_e($detail_customer['username'] ?? ''); ?></p>
                    </div>
                </div>

                <div class="p-6 space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status</p>
                            <p class="font-semibold <?= $akun_aktif ? 'text-green-600' : 'text-gray-700'; ?> mt-1"><?= $akun_aktif ? 'Aktif' : 'Nonaktif'; ?></p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Role</p>
                            <p class="font-semibold text-gray-800 mt-1"><?= admin_pelanggan_e($detail_customer['rule'] ?? 'customer'); ?></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Email</p>
                        <p class="font-semibold text-gray-800 mt-1 break-words"><?= admin_pelanggan_e($detail_customer['email'] ?? ''); ?></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nomor Telepon</p>
                        <p class="font-semibold text-gray-800 mt-1"><?= admin_pelanggan_e($detail_customer['nomortelepon'] ?? ''); ?></p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Alamat</p>
                        <p class="font-semibold text-gray-800 mt-1"><?= admin_pelanggan_e($detail_customer['alamat'] ?? ''); ?></p>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-gray-800">Detail Ukuran Pelanggan</h3>
                            <p class="text-xs text-gray-400">Data ini mengikuti ukuran yang diisi customer pada dashboard pelanggan.</p>
                        </div>
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-purple-50 text-purple-600 text-xs font-bold">
                            <?= count($ukuran_rows); ?> Data Ukuran
                        </span>
                    </div>

                    <?php if (empty($ukuran_rows)): ?>
                        <div class="p-8 text-center text-gray-400 text-xs">Customer ini belum mengisi data ukuran.</div>
                    <?php else: ?>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($ukuran_rows as $ukuran): ?>
                                <?php $waktu_ukuran = !empty($ukuran['updated_at']) ? $ukuran['updated_at'] : ($ukuran['created_at'] ?? ''); ?>
                                <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-5 space-y-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="font-bold text-gray-800"><?= admin_pelanggan_e($ukuran['nama_ukuran'] ?? ''); ?></h4>
                                            <p class="text-[11px] text-gray-400">Update: <?= admin_pelanggan_tanggal($waktu_ukuran, 'd M Y H:i'); ?></p>
                                        </div>
                                        <div class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-purple-600 flex items-center justify-center">
                                            <i class="fas fa-ruler-combined"></i>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <?php foreach ($ukuran_field_labels as $field => $label): ?>
                                            <?php if (($ukuran[$field] ?? '') !== ''): ?>
                                                <div class="bg-white p-3 rounded-xl border border-gray-100">
                                                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400"><?= admin_pelanggan_e($label); ?></p>
                                                    <p class="font-bold text-gray-800 mt-1"><?= admin_pelanggan_cm($ukuran[$field]); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if (!empty($ukuran['catatan'])): ?>
                                        <div class="bg-white p-3 rounded-xl border border-gray-100">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Catatan</p>
                                            <p class="text-xs text-gray-600 mt-1"><?= admin_pelanggan_e($ukuran['catatan']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-gray-800">Pesanan Terkait</h3>
                            <p class="text-xs text-gray-400">Dicocokkan melalui username, nama, atau nomor telepon customer.</p>
                        </div>
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-amber-50 text-primary text-xs font-bold">
                            <?= count($pesanan_rows); ?> Pesanan
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                                    <th class="p-4 pl-6">Jenis</th>
                                    <th class="p-4">Foto Kain</th>
                                    <th class="p-4">Detail Model</th>
                                    <th class="p-4">Target</th>
                                    <th class="p-4 text-right">Total</th>
                                    <th class="p-4 pr-6 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-gray-600">
                                <?php if (empty($pesanan_rows)): ?>
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Belum ada pesanan yang terhubung dengan customer ini.</td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($pesanan_rows as $pesanan): ?>
                                    <?php
                                    $status = $pesanan['status'] ?: 'Menunggu Antrian';
                                    $badge = admin_pelanggan_status_badge($status);
                                    ?>
                                    <tr class="hover:bg-gray-50/40 transition align-top">
                                        <td class="p-4 pl-6">
                                            <span class="font-semibold text-gray-800"><?= admin_pelanggan_e($pesanan['jenis_pakaian'] ?? ''); ?></span>
                                            <span class="block text-[11px] text-gray-400">#<?= (int) ($pesanan['id'] ?? 0); ?></span>
                                        </td>
                                        <td class="p-4">
                                            <?php if (!empty($pesanan['kain_foto'])): ?>
                                                <?php $foto_kain = basename((string) $pesanan['kain_foto']); ?>
                                                <a href="../../foto/<?= rawurlencode($foto_kain); ?>" target="_blank" rel="noopener noreferrer" title="Lihat foto kain">
                                                    <img src="../../foto/<?= rawurlencode($foto_kain); ?>" alt="Foto kain pesanan #<?= (int) ($pesanan['id'] ?? 0); ?>" class="h-16 w-20 rounded-lg border border-gray-200 bg-white object-cover">
                                                </a>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-xs max-w-sm"><?= admin_pelanggan_e($pesanan['detail_model'] ?? ''); ?></td>
                                        <td class="p-4 text-xs"><?= admin_pelanggan_tanggal($pesanan['tgl_selesai'] ?? ''); ?></td>
                                        <td class="p-4 text-right font-semibold text-gray-800"><?= admin_pelanggan_rupiah($pesanan['total_biaya'] ?? 0); ?></td>
                                        <td class="p-4 pr-6 text-center">
                                            <span class="px-3 py-1 inline-block <?= $badge; ?> text-[10px] font-bold rounded-full">
                                                <?= admin_pelanggan_e($status); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif (empty($pelanggan_rows)): ?>
        <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center text-gray-400 text-sm">
            Belum ada data customer untuk ditampilkan.
        </div>
    <?php else: ?>
        <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-center text-gray-400 text-sm">
            Customer yang dipilih tidak ditemukan.
        </div>
    <?php endif; ?>
</div>
