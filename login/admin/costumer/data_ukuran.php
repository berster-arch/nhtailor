<?php
require_once __DIR__ . '/../guard.php';

$pesan = '';
$usernameLogin = trim((string) ($username ?? ''));

function ukuran_fields(): array {
    return [
        'lebar_punggung' => ['label' => 'Lebar Punggung', 'required' => false],
        'panjang_punggung' => ['label' => 'Panjang Punggung', 'required' => false],
        'panjang_pinggang' => ['label' => 'Panjang Pinggang', 'required' => false],
        'panjang_panggul' => ['label' => 'Panjang Panggul', 'required' => false],
        'lingkar_dada' => ['label' => 'Lingkar Dada', 'required' => true],
        'lingkar_pinggang' => ['label' => 'Lingkar Pinggang', 'required' => true],
        'lingkar_pinggul' => ['label' => 'Lingkar Panggul', 'required' => true],
        'lebar_bahu' => ['label' => 'Lebar Bahu', 'required' => true],
        'panjang_muka' => ['label' => 'Panjang Muka', 'required' => false],
        'panjang_baju' => ['label' => 'Panjang Baju', 'required' => true],
        'lebar_muka' => ['label' => 'Lebar Muka', 'required' => false],
        'panjang_lengan' => ['label' => 'Panjang Lengan', 'required' => true],
        'lingkar_lengan' => ['label' => 'Lingkar Lengan', 'required' => false],
        'kerung_lengan' => ['label' => 'Kerung Lengan', 'required' => false],
        'lingkar_pinggang_celana' => ['label' => 'Lingkar Pinggang Celana', 'required' => false],
        'lingkar_pesak' => ['label' => 'Lingkar Pesak', 'required' => false],
        'lingkar_paha' => ['label' => 'Lingkar Paha', 'required' => false],
        'lingkar_betis_bawah' => ['label' => 'Lingkar Betis Bawah', 'required' => false],
        'panjang_celana' => ['label' => 'Panjang Celana', 'required' => false],
        'lingkar_sabrina' => ['label' => 'Lingkar Sabrina', 'required' => false],
        'tinggi_leher' => ['label' => 'Tinggi Leher', 'required' => false],
        'panjang_rok' => ['label' => 'Panjang Rok', 'required' => true],
    ];
}

function read_ukuran_input($key) {
    $value = $_POST[$key] ?? '';
    return is_scalar($value) ? trim((string) $value) : '';
}

function ukuran_input_valid(array $values, array $fields): bool {
    foreach ($fields as $key => $field) {
        $value = $values[$key];
        if ($field['required'] && $value === '') {
            return false;
        }
        if ($value !== '' && (!is_numeric($value) || (float) $value <= 0 || (float) $value > 300)) {
            return false;
        }
    }

    return true;
}

function ukuran_bind_and_execute(mysqli_stmt $stmt, array $values, string $types): bool {
    $parameters = [$stmt, $types];
    foreach ($values as $index => &$value) {
        $parameters[] = &$value;
    }

    return call_user_func_array('mysqli_stmt_bind_param', $parameters) && mysqli_stmt_execute($stmt);
}

$fieldUkuran = ukuran_fields();

if (isset($_POST['simpan_ukuran'])) {
    $namaUkuran = read_ukuran_input('nama_ukuran');
    $ukuranValues = [];
    foreach ($fieldUkuran as $key => $field) {
        $ukuranValues[$key] = read_ukuran_input($key);
    }
    $catatan = read_ukuran_input('catatan');

    if ($namaUkuran === '' || !ukuran_input_valid($ukuranValues, $fieldUkuran)) {
        $pesan = 'Nama ukuran dan semua ukuran wajib harus diisi. Ukuran harus berupa angka antara 0 sampai 300 cm.';
    } else {
        $columns = array_keys($fieldUkuran);
        $sql = 'INSERT INTO ukuran_customer (username, nama_ukuran, ' . implode(', ', $columns) . ', catatan) VALUES (' . implode(', ', array_fill(0, count($columns) + 3, '?')) . ')';
        $stmt = mysqli_prepare($koneksi, $sql);
        if ($stmt === false) {
            error_log('Measurement insert preparation failed: ' . mysqli_error($koneksi));
            $pesan = 'Gagal menyiapkan data ukuran.';
        } else {
            $values = array_merge([$usernameLogin, $namaUkuran], array_values($ukuranValues), [$catatan]);
            if (ukuran_bind_and_execute($stmt, $values, str_repeat('s', count($values)))) {
                $pesan = 'Data ukuran berhasil ditambahkan.';
            } else {
                error_log('Measurement insert failed: ' . mysqli_stmt_error($stmt));
                $pesan = 'Gagal menambahkan data ukuran. Silakan coba kembali.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_POST['update_ukuran'])) {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $namaUkuran = read_ukuran_input('nama_ukuran');
    $ukuranValues = [];
    foreach ($fieldUkuran as $key => $field) {
        $ukuranValues[$key] = read_ukuran_input($key);
    }
    $catatan = read_ukuran_input('catatan');

    if (!$id || $id < 1 || $namaUkuran === '' || !ukuran_input_valid($ukuranValues, $fieldUkuran)) {
        $pesan = 'Data ukuran tidak valid. Pastikan nama dan ukuran wajib terisi serta ukuran berupa angka antara 0 sampai 300 cm.';
    } else {
        $setters = array_map(static function ($column) {
            return "$column = ?";
        }, array_merge(['nama_ukuran'], array_keys($fieldUkuran), ['catatan']));
        $sql = 'UPDATE ukuran_customer SET ' . implode(', ', $setters) . ' WHERE id = ? AND username = ?';
        $stmt = mysqli_prepare($koneksi, $sql);
        if ($stmt === false) {
            error_log('Measurement update preparation failed: ' . mysqli_error($koneksi));
            $pesan = 'Gagal menyiapkan pembaruan data ukuran.';
        } else {
            $values = array_merge([$namaUkuran], array_values($ukuranValues), [$catatan, $id, $usernameLogin]);
            $types = str_repeat('s', count($values) - 2) . 'is';
            if (ukuran_bind_and_execute($stmt, $values, $types)) {
                echo "<script>alert('Data ukuran berhasil diperbarui.'); window.location.href='" . panel_url('data_ukuran') . "';</script>";
                mysqli_stmt_close($stmt);
                exit;
            } else {
                error_log('Measurement update failed: ' . mysqli_stmt_error($stmt));
                $pesan = 'Gagal memperbarui data ukuran. Silakan coba kembali.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_POST['hapus_ukuran'])) {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM ukuran_customer WHERE id = ? AND username = ?');
    if ($stmt === false) {
        $pesan = 'Gagal menyiapkan penghapusan data ukuran.';
    } else {
        mysqli_stmt_bind_param($stmt, 'is', $id, $usernameLogin);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Data ukuran berhasil dihapus.'); window.location.href='" . panel_url('data_ukuran') . "';</script>";
            exit;
        } else {
            $pesan = 'Gagal menghapus data ukuran: ' . htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8');
        }
        mysqli_stmt_close($stmt);
    }
}

$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = is_scalar($_GET['edit_id']) ? filter_var($_GET['edit_id'], FILTER_VALIDATE_INT) : false;
    if ($editId) {
        $editStatement = mysqli_prepare($koneksi, 'SELECT * FROM ukuran_customer WHERE id = ? AND username = ? LIMIT 1');
        if ($editStatement !== false) {
            mysqli_stmt_bind_param($editStatement, 'is', $editId, $usernameLogin);
            mysqli_stmt_execute($editStatement);
            $editResult = mysqli_stmt_get_result($editStatement);
            $editData = $editResult ? mysqli_fetch_assoc($editResult) : null;
            mysqli_stmt_close($editStatement);
        }
    }
}

$listStatement = mysqli_prepare($koneksi, 'SELECT * FROM ukuran_customer WHERE username = ? ORDER BY id DESC');
if ($listStatement !== false) {
    mysqli_stmt_bind_param($listStatement, 's', $usernameLogin);
    mysqli_stmt_execute($listStatement);
    $dataUkuran = mysqli_stmt_get_result($listStatement);
    mysqli_stmt_close($listStatement);
} else {
    error_log('Measurement list query preparation failed: ' . mysqli_error($koneksi));
    $dataUkuran = false;
}
?>

<div class="w-full space-y-8">
    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h2 class="font-luxury text-3xl text-gray-800">Data Ukuran</h2>
            <p class="text-gray-500 text-sm">Kelola ukuran badan Anda sendiri untuk kebutuhan pesanan jahitan.</p>
        </div>
        <?php if ($editData): ?>
            <a href="<?= panel_url('data_ukuran'); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-gray-700 transition">
                <i class="fas fa-plus"></i>
                Tambah Baru
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($pesan)): ?>
        <div class="p-4 rounded-2xl <?= stripos($pesan, 'Gagal') === false ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200'; ?> border text-sm font-semibold">
            <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-1"><?= $editData ? 'Edit Ukuran' : 'Tambah Ukuran'; ?></h3>
            <p class="text-xs text-gray-400 mb-5">Isi angka dalam satuan cm.</p>

            <form method="POST" action="" class="space-y-4">
                <?= csrf_field(); ?>
                <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?= (int) $editData['id']; ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Nama Ukuran</label>
                    <input type="text" name="nama_ukuran" required placeholder="Contoh: Kebaya Wisuda"
                           value="<?= htmlspecialchars((string) ($editData['nama_ukuran'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                           class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <?php foreach ($fieldUkuran as $key => $field): ?>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1"><?= htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?><?= $field['required'] ? ' *' : ''; ?></label>
                            <input type="number" step="0.1" min="0.1" max="300" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" <?= $field['required'] ? 'required' : ''; ?> value="<?= htmlspecialchars((string) ($editData[$key] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg outline-none focus:border-primary text-sm">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Catatan</label>
                    <textarea name="catatan" rows="3" placeholder="Contoh: model lengan longgar, rok A-line" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition text-sm"><?= htmlspecialchars((string) ($editData['catatan'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <button type="submit" name="<?= $editData ? 'update_ukuran' : 'simpan_ukuran'; ?>" class="w-full px-5 py-3 bg-primary text-white rounded-xl font-bold text-sm shadow-sm hover:bg-amber-600 transition">
                    <?= $editData ? 'Simpan Perubahan' : 'Tambah Data Ukuran'; ?>
                </button>
            </form>
        </div>

        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50">
                <h3 class="font-bold text-gray-800">Daftar Ukuran Saya</h3>
                <p class="text-xs text-gray-400">Semua data di bawah hanya terlihat untuk akun Anda.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-gray-400 text-[11px] font-bold uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4 pl-6">Nama</th>
                            <th class="p-4">Rincian Ukuran</th>
                            <th class="p-4 pr-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-600">
                        <?php if ($dataUkuran === false || mysqli_num_rows($dataUkuran) == 0): ?>
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-400 text-xs">Belum ada data ukuran. Silakan isi form di samping.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($dataUkuran)): ?>
                                <tr class="hover:bg-gray-50/40 transition align-top">
                                    <td class="p-4 pl-6">
                                        <span class="font-semibold text-gray-800"><?= htmlspecialchars((string) $row['nama_ukuran'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php if (!empty($row['catatan'])): ?>
                                            <span class="block text-[11px] text-gray-400 mt-1"><?= htmlspecialchars((string) $row['catatan'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-2 text-xs">
                                            <?php foreach ($fieldUkuran as $key => $field): ?>
                                                <?php if (($row[$key] ?? '') !== ''): ?>
                                                    <span><span class="text-gray-400"><?= htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?>:</span> <span class="font-semibold"><?= htmlspecialchars((string) $row[$key], ENT_QUOTES, 'UTF-8'); ?> cm</span></span>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="p-4 pr-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="<?= panel_url('data_ukuran', ['edit_id' => (int) $row['id']]); ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition" title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <form method="POST" action="" onsubmit="return confirm('Hapus data ukuran ini?');">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?= (int) $row['id']; ?>">
                                                <button type="submit" name="hapus_ukuran" class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition" title="Hapus">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
