<?php
require_once __DIR__ . '/../guard.php';

require_once __DIR__ . '/../../../koneksi.php';
include_once __DIR__ . '/../fonnte_helper.php';
require_once __DIR__ . '/order_upload_helper.php';

$pesan = "";
$customer_query = mysqli_query($koneksi, "SELECT username, nama, nomortelepon FROM login WHERE rule='customer' AND status='Y' ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_pesanan'])) {
    $nama_pelanggan = trim((string)($_POST['nama_pelanggan'] ?? ''));
    $no_hp = trim((string)($_POST['no_hp'] ?? ''));
    $username_customer = trim((string)($_POST['username_customer'] ?? ''));
    $jenis_pakaian = trim((string)($_POST['jenis_pakaian'] ?? ''));
    $detail_model = trim((string)($_POST['detail_model'] ?? ''));
    $total_biaya_input = trim((string) ($_POST['total_biaya'] ?? ''));
    $dp_input = trim((string) ($_POST['dp'] ?? '0'));
    $total_biaya = is_numeric($total_biaya_input) ? (float) $total_biaya_input : -1;
    $dp = is_numeric($dp_input) ? (float) $dp_input : -1;
    $tgl_selesai = trim((string)($_POST['tgl_selesai'] ?? ''));
    $tanggal_selesai = DateTime::createFromFormat('!Y-m-d', $tgl_selesai);
    $tanggal_errors = DateTime::getLastErrors();

    if ($nama_pelanggan === '' || $no_hp === '' || $jenis_pakaian === '' || $tgl_selesai === '') {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                    Mohon lengkapi data pelanggan, jenis pakaian, dan target selesai.
                  </div>";
    } elseif (!$tanggal_selesai
        || ($tanggal_errors !== false && ($tanggal_errors['warning_count'] > 0 || $tanggal_errors['error_count'] > 0))
        || $tanggal_selesai->format('Y-m-d') !== $tgl_selesai) {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                    Format tanggal target selesai tidak valid.
                  </div>";
    } elseif (!is_finite($total_biaya) || !is_finite($dp)
        || $total_biaya <= 0 || $total_biaya > 9999999999999
        || $dp < 0 || $dp > $total_biaya) {
        $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                    Total biaya harus lebih dari 0 dan DP harus berada di antara 0 sampai total biaya.
                  </div>";
    } else {
        $customer_valid = true;
        $kain_foto = null;
        if ($username_customer !== '') {
            $customer_statement = mysqli_prepare($koneksi, "SELECT nama, nomortelepon FROM login WHERE username = ? AND rule = 'customer' AND status = 'Y' LIMIT 1");
            if ($customer_statement === false) {
                error_log('Order customer validation preparation failed: ' . mysqli_error($koneksi));
                $customer_valid = false;
                $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                            Gagal memvalidasi akun pelanggan. Silakan coba kembali.
                          </div>";
            } else {
                mysqli_stmt_bind_param($customer_statement, 's', $username_customer);
                if (!mysqli_stmt_execute($customer_statement)) {
                    error_log('Order customer validation failed: ' . mysqli_stmt_error($customer_statement));
                    $customer_valid = false;
                    $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                                Gagal memvalidasi akun pelanggan. Silakan coba kembali.
                              </div>";
                } else {
                    mysqli_stmt_bind_result($customer_statement, $nama_akun, $telepon_akun);
                    $customer_valid = mysqli_stmt_fetch($customer_statement)
                        && $nama_pelanggan === (string) $nama_akun
                        && $no_hp === (string) $telepon_akun;
                }
                mysqli_stmt_close($customer_statement);
                if (!$customer_valid && $pesan === '') {
                    $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                                Pastikan nama dan nomor WhatsApp sesuai dengan akun customer yang dipilih.
                              </div>";
                }
            }
        }

        $upload_result = $customer_valid
            ? order_save_fabric_photo($_FILES['kain_foto'] ?? ['error' => UPLOAD_ERR_NO_FILE])
            : ['filename' => null, 'error' => null];
        $kain_foto = $upload_result['filename'];
        if ($upload_result['error'] !== null) {
            $customer_valid = false;
            $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>" .
                htmlspecialchars($upload_result['error'], ENT_QUOTES, 'UTF-8') .
                "</div>";
        }

        $statement = $customer_valid
            ? mysqli_prepare($koneksi, 'INSERT INTO pesanan (nama_pelanggan, no_hp, username_customer, jenis_pakaian, detail_model, total_biaya, dp, kain_foto, tgl_selesai, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "Menunggu Antrian")')
            : false;

        if ($statement === false) {
            if ($customer_valid) {
                error_log('Order insert preparation failed: ' . mysqli_error($koneksi));
                $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                            Gagal menyiapkan pesanan. Silakan coba kembali.
                          </div>";
            }
        } else {
            mysqli_stmt_bind_param($statement, 'sssssddss', $nama_pelanggan, $no_hp, $username_customer, $jenis_pakaian, $detail_model, $total_biaya, $dp, $kain_foto, $tgl_selesai);

            if (mysqli_stmt_execute($statement)) {
                $fonnte = fonnte_notify_order_created([
                    'nama_pelanggan' => $nama_pelanggan,
                    'no_hp' => $no_hp,
                    'jenis_pakaian' => $jenis_pakaian,
                    'total_biaya' => number_format($total_biaya, 0, ',', '.'),
                    'dp' => number_format($dp, 0, ',', '.'),
                    'tgl_selesai' => $tgl_selesai,
                ]);
                $info_fonnte = $fonnte['success'] ?? false
                    ? " Notifikasi WhatsApp berhasil dikirim."
                    : " Notifikasi WhatsApp belum terkirim: " . htmlspecialchars($fonnte['message'] ?? 'Fonnte tidak merespons.', ENT_QUOTES, 'UTF-8');

                $pesan = "<div class='mb-6 p-4 bg-green-100 text-green-700 rounded-2xl flex items-center border border-green-200'>
                            <i class='fas fa-check-circle mr-2'></i> Pesanan berhasil disimpan!$info_fonnte
                          </div>";
            } else {
                error_log('Order insert failed: ' . mysqli_stmt_error($statement));
                if ($kain_foto !== null) {
                    order_remove_fabric_photo($kain_foto);
                }
                $pesan = "<div class='mb-6 p-4 bg-red-100 text-red-700 rounded-2xl border border-red-200'>
                            Pesanan gagal disimpan. Silakan periksa data dan coba kembali.
                          </div>";
            }

            mysqli_stmt_close($statement);
        }
        if ($statement === false && $kain_foto !== null) {
            order_remove_fabric_photo($kain_foto);
        }
    }
}
?>

<div class="w-full">
    <?= $pesan; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field(); ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Kolom Kiri: Informasi Pelanggan & Estimasi Waktu -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Identitas Pelanggan -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                        <i class="fas fa-user-circle mr-2 text-primary"></i> Pelanggan
                    </h3>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Lengkap</label>
                        <input type="text" id="order-customer-name" name="nama_pelanggan" maxlength="100" required 
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">WhatsApp</label>
                        <input type="text" id="order-customer-phone" name="no_hp" maxlength="30" required placeholder="08..."
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Akun Customer</label>
                        <select id="order-customer-account" name="username_customer"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                            <option value="">Tidak terhubung akun</option>
                            <?php while ($customer = mysqli_fetch_assoc($customer_query)): ?>
                                <option value="<?= htmlspecialchars($customer['username'], ENT_QUOTES, 'UTF-8'); ?>" data-customer-name="<?= htmlspecialchars($customer['nama'], ENT_QUOTES, 'UTF-8'); ?>" data-customer-phone="<?= htmlspecialchars($customer['nomortelepon'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?= htmlspecialchars($customer['nama']); ?> - <?= htmlspecialchars($customer['nomortelepon']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <!-- Estimasi Penyelesaian -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                    <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                        <i class="fas fa-clock mr-2 text-primary"></i> Estimasi
                    </h3>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Target Selesai</label>
                        <input type="date" name="tgl_selesai" required 
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Rincian Jahitan & Pembayaran -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
                <h3 class="font-bold text-gray-700 flex items-center text-sm border-b pb-2 uppercase tracking-wider">
                    <i class="fas fa-cut mr-2 text-primary"></i> Detail Jahitan & Biaya
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Jenis Pakaian</label>
                        <select name="jenis_pakaian" required 
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                            <option value="Wedding Dress">Wedding Dress</option>
                            <option value="Party Wear">Party Wear</option>
                            <option value="Kebaya Modern">Kebaya Modern</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Total Biaya (Rp)</label>
                        <input type="number" name="total_biaya" min="1" max="9999999999999" step="1" required 
                               class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm font-bold text-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">DP / Uang Muka (Rp)</label>
                    <input type="number" name="dp" min="0" max="9999999999999" step="1" required 
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Detail Model</label>
                    <textarea name="detail_model" rows="5" placeholder="Tuliskan spesifikasi detail model pakaian..."
                              class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm"></textarea>
                </div>

                <div>
                    <label for="order-fabric-photo" class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Foto Kain Customer (Opsional)</label>
                    <input id="order-fabric-photo" type="file" name="kain_foto" accept="image/jpeg,image/png,image/webp"
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-2 file:text-xs file:font-bold file:text-white">
                    <p class="mt-1 text-xs text-gray-400">Format JPG, PNG, atau WEBP; maksimal 5 MB.</p>
                </div>

                <!-- Tombol Navigasi & Submit -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="reset" class="px-6 py-2.5 text-gray-400 font-bold text-sm hover:text-gray-600 transition">
                        Batal
                    </button>
                    <button type="submit" name="simpan_pesanan" class="px-8 py-2.5 bg-primary text-white rounded-xl font-bold text-sm shadow-lg hover:bg-blue-600 hover:shadow-blue-100 hover:scale-105 transition-all outline-none">
                        Simpan Pesanan
                    </button>
                </div>
            </div>
            
        </div>
    </form>
</div>

<script>
    const customerAccount = document.getElementById('order-customer-account');
    customerAccount.addEventListener('change', () => {
        const selectedAccount = customerAccount.selectedOptions[0];
        if (customerAccount.value !== '') {
            document.getElementById('order-customer-name').value = selectedAccount.dataset.customerName || '';
            document.getElementById('order-customer-phone').value = selectedAccount.dataset.customerPhone || '';
        }
    });
</script>
