<?php
require_once __DIR__ . '/../guard.php';

if (!function_exists('keuangan_e')) {
    function keuangan_e($value) {
        if ($value === null || $value === '') {
            return '-';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('keuangan_rupiah')) {
    function keuangan_rupiah($angka) {
        return 'Rp ' . number_format((int) $angka, 0, ',', '.');
    }
}

if (!function_exists('keuangan_tanggal')) {
    function keuangan_tanggal($tanggal) {
        if (empty($tanggal) || $tanggal == '0000-00-00') {
            return '-';
        }

        $waktu = strtotime($tanggal);
        if (!$waktu) {
            return '-';
        }

        return date('d M Y', $waktu);
    }
}

$query_ringkasan = mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS total_transaksi,
        COALESCE(SUM(total_biaya), 0) AS total_pendapatan,
        COALESCE(SUM(dp), 0) AS total_dp,
        COALESCE(SUM(GREATEST(total_biaya - dp, 0)), 0) AS total_sisa
    FROM pesanan
");
$ringkasan = $query_ringkasan ? (mysqli_fetch_assoc($query_ringkasan) ?: []) : [];

$query_lunas = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total
    FROM pesanan
    WHERE dp >= total_biaya
");
$lunas = $query_lunas ? (mysqli_fetch_assoc($query_lunas) ?: ['total' => 0]) : ['total' => 0];

$query_pesanan = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY id DESC");
?>

<div class="w-full space-y-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="font-luxury text-3xl text-gray-800">Laporan Keuangan</h2>
            <p class="text-gray-500 text-sm">Ringkasan nilai pesanan, pembayaran yang diterima, dan sisa tagihan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nilai Pesanan</span>
                <h3 class="text-2xl font-extrabold text-gray-800"><?= keuangan_rupiah($ringkasan['total_pendapatan'] ?? 0); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-600 text-xl">
                <i class="fas fa-wallet"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Terbayar</span>
                <h3 class="text-2xl font-extrabold text-primary"><?= keuangan_rupiah($ringkasan['total_dp'] ?? 0); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-primary text-xl">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sisa Tagihan</span>
                <h3 class="text-2xl font-extrabold text-red-500"><?= keuangan_rupiah($ringkasan['total_sisa'] ?? 0); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center text-red-500 text-xl">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Transaksi Lunas</span>
                <h3 class="text-2xl font-extrabold text-gray-800"><?= (int) ($lunas['total'] ?? 0); ?> / <?= (int) ($ringkasan['total_transaksi'] ?? 0); ?></h3>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 text-xl">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-50">
            <h4 class="font-bold text-gray-800">Detail Pembayaran Pesanan</h4>
            <p class="text-xs text-gray-400">Daftar total biaya, DP, sisa pembayaran, dan status pembayaran pesanan.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4 pl-6">No</th>
                        <th class="p-4">Pelanggan</th>
                        <th class="p-4">Jenis Pakaian</th>
                        <th class="p-4">Target</th>
                        <th class="p-4 text-right">Total Biaya</th>
                        <th class="p-4 text-right">DP</th>
                        <th class="p-4 text-right">Sisa</th>
                        <th class="p-4 text-center">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-600">
                    <?php if (!$query_pesanan || mysqli_num_rows($query_pesanan) == 0): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400 text-xs">Belum ada data keuangan pesanan.</td>
                        </tr>
                    <?php endif; ?>

                    <?php if ($query_pesanan): ?>
                        <?php while ($row = mysqli_fetch_assoc($query_pesanan)): ?>
                            <?php
                            $total_biaya = (float) ($row['total_biaya'] ?? 0);
                            $dp = (float) ($row['dp'] ?? 0);
                            $sisa = max($total_biaya - $dp, 0);
                            $sudah_lunas = $sisa <= 0;
                            $badge = $sudah_lunas ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700';
                            ?>
                            <tr class="hover:bg-gray-50/40 transition align-top">
                                <td class="p-4 pl-6 font-semibold text-gray-400">#<?= (int) $row['id']; ?></td>
                                <td class="p-4">
                                    <span class="font-semibold text-gray-800"><?= keuangan_e($row['nama_pelanggan']); ?></span>
                                    <span class="block text-[11px] text-gray-400"><?= keuangan_e($row['no_hp']); ?></span>
                                </td>
                                <td class="p-4 text-xs font-medium text-gray-500"><?= keuangan_e($row['jenis_pakaian']); ?></td>
                                <td class="p-4 text-xs"><?= keuangan_tanggal($row['tgl_selesai'] ?? ''); ?></td>
                                <td class="p-4 text-right font-semibold text-gray-800"><?= keuangan_rupiah($total_biaya); ?></td>
                                <td class="p-4 text-right text-primary font-semibold"><?= keuangan_rupiah($dp); ?></td>
                                <td class="p-4 text-right font-semibold <?= $sudah_lunas ? 'text-green-600' : 'text-red-500'; ?>"><?= keuangan_rupiah($sisa); ?></td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 inline-block <?= $badge; ?> text-[10px] font-bold rounded-full">
                                        <?= $sudah_lunas ? 'Lunas' : 'Belum Lunas'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
