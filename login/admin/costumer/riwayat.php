<?php
require_once __DIR__ . '/../guard.php';

$usernameSafe = mysqli_real_escape_string($koneksi, (string) $username);
$akun_query = mysqli_query($koneksi, "SELECT nama, nomortelepon FROM login WHERE username = '$usernameSafe' LIMIT 1");
$akun = $akun_query ? mysqli_fetch_assoc($akun_query) : null;
$nama_customer = $akun['nama'] ?? null;
$telepon_customer = $akun['nomortelepon'] ?? null;

$query_pesanan = "SELECT * FROM pesanan WHERE username_customer = '$usernameSafe'";

if ($akun && $nama_customer !== null && $telepon_customer !== null && $nama_customer !== '' && $telepon_customer !== '') {
    $query_pesanan .= " OR ((username_customer IS NULL OR username_customer = '')
        AND nama_pelanggan = '" . mysqli_real_escape_string($koneksi, (string) $nama_customer) . "'
        AND no_hp = '" . mysqli_real_escape_string($koneksi, (string) $telepon_customer) . "')";
}

$query_pesanan .= " ORDER BY id DESC";
$pesanan_query = mysqli_query($koneksi, $query_pesanan);

function rupiah_customer($angka) {
    return "Rp " . number_format((int) $angka, 0, ',', '.');
}
?>

<div class="w-full space-y-6">
    <div>
        <h2 class="font-luxury text-3xl text-gray-800">Riwayat Pesanan</h2>
        <p class="text-gray-500 text-sm">Daftar pesanan yang terhubung dengan akun Anda.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                        <th class="p-4 pl-6">No</th>
                        <th class="p-4">Jenis Pakaian</th>
                        <th class="p-4">Detail Model</th>
                        <th class="p-4">Target Selesai</th>
                        <th class="p-4 text-right">Total</th>
                        <th class="p-4 text-right">DP Dibayar</th>
                        <th class="p-4 text-right">Sisa Pembayaran</th>
                        <th class="p-4 pr-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-gray-600">
                    <?php if (mysqli_num_rows($pesanan_query) == 0): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400 text-xs">Belum ada riwayat pesanan yang cocok dengan akun Anda.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while ($row = mysqli_fetch_assoc($pesanan_query)): ?>
                        <?php
                        $status = $row['status'] ?: 'Menunggu Antrian';
                        $total_biaya = (float) ($row['total_biaya'] ?? 0);
                        $dp = (float) ($row['dp'] ?? 0);
                        $sisa_pembayaran = max($total_biaya - $dp, 0);
                        $badge = "bg-gray-100 text-gray-600";
                        if (stripos($status, "Pemotongan Kain") !== false) $badge = "bg-yellow-100 text-yellow-700";
                        if (stripos($status, "Proses Menjahit") !== false) $badge = "bg-blue-100 text-blue-700";
                        if (stripos($status, "Fitting / Pas") !== false) $badge = "bg-purple-100 text-purple-700";
                        if (stripos($status, "Selesai") !== false) $badge = "bg-green-100 text-green-700";
                        ?>
                        <tr class="hover:bg-gray-50/40 transition">
                            <td class="p-4 pl-6 font-semibold text-gray-400">#<?= (int) $row['id']; ?></td>
                            <td class="p-4 font-semibold text-gray-800"><?= htmlspecialchars($row['jenis_pakaian']); ?></td>
                            <td class="p-4 text-xs max-w-xs"><?= htmlspecialchars($row['detail_model'] ?: '-'); ?></td>
                            <td class="p-4 text-xs"><?= htmlspecialchars(date('d M Y', strtotime($row['tgl_selesai'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="p-4 text-right font-semibold text-gray-800"><?= rupiah_customer($total_biaya); ?></td>
                            <td class="p-4 text-right font-semibold text-primary"><?= rupiah_customer($dp); ?></td>
                            <td class="p-4 text-right font-semibold text-gray-800"><?= rupiah_customer($sisa_pembayaran); ?></td>
                            <td class="p-4 pr-6 text-center">
                                <span class="px-3 py-1 inline-block <?= $badge; ?> text-[10px] font-bold rounded-full">
                                    <?= htmlspecialchars($status); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
