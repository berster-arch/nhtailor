<?php
require_once __DIR__ . '/../guard.php';

$usernameLogin = trim((string) ($username ?? ''));
$profileMessage = '';
$profileMessageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profil'])) {
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $alamat = trim((string) ($_POST['alamat'] ?? ''));
    $nomorTelepon = trim((string) ($_POST['nomortelepon'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $passwordBaru = (string) ($_POST['password'] ?? '');

    if ($nama === '' || $alamat === '' || $nomorTelepon === '' || $email === '') {
        $profileMessage = 'Nama, alamat, nomor telepon, dan email wajib diisi.';
        $profileMessageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileMessage = 'Format email tidak valid.';
        $profileMessageType = 'error';
    } elseif ($passwordBaru !== '' && strlen($passwordBaru) < 8) {
        $profileMessage = 'Password baru minimal 8 karakter.';
        $profileMessageType = 'error';
    } else {
        $duplicateStmt = mysqli_prepare($koneksi, 'SELECT id FROM login WHERE (username = ? OR email = ?) AND username <> ? LIMIT 1');
        if ($duplicateStmt === false) {
            $profileMessage = 'Gagal memvalidasi data akun.';
            $profileMessageType = 'error';
        } else {
            mysqli_stmt_bind_param($duplicateStmt, 'sss', $usernameLogin, $email, $usernameLogin);
            mysqli_stmt_execute($duplicateStmt);
            mysqli_stmt_store_result($duplicateStmt);

            if (mysqli_stmt_num_rows($duplicateStmt) > 0) {
                $profileMessage = 'Username atau email sudah digunakan akun lain.';
                $profileMessageType = 'error';
            } else {
                $sql = 'UPDATE login SET nama = ?, alamat = ?, nomortelepon = ?, email = ?';
                $params = [$nama, $alamat, $nomorTelepon, $email];
                $types = 'ssss';

                if ($passwordBaru !== '') {
                    $sql .= ', password = ?';
                    $params[] = password_hash($passwordBaru, PASSWORD_DEFAULT);
                    $types .= 's';
                }

                $sql .= ' WHERE username = ? LIMIT 1';
                $params[] = $usernameLogin;
                $types .= 's';

                $updateStmt = mysqli_prepare($koneksi, $sql);
                if ($updateStmt === false) {
                    $profileMessage = 'Gagal memperbarui data diri.';
                    $profileMessageType = 'error';
                } else {
                    mysqli_stmt_bind_param($updateStmt, $types, ...$params);
                    if (mysqli_stmt_execute($updateStmt)) {
                        $profileMessage = 'Data diri berhasil diperbarui.';
                        $profileMessageType = 'success';
                    } else {
                        $profileMessage = 'Gagal menyimpan perubahan data diri.';
                        $profileMessageType = 'error';
                    }
                    mysqli_stmt_close($updateStmt);
                }
            }

            mysqli_stmt_close($duplicateStmt);
        }
    }
}

$usernameSafe = mysqli_real_escape_string($koneksi, $usernameLogin);
$akunQuery = mysqli_query($koneksi, "SELECT nama, alamat, nomortelepon, username, email, status, rule FROM login WHERE username = '$usernameSafe' LIMIT 1");
$akun = $akunQuery ? mysqli_fetch_assoc($akunQuery) : [];
?>

<div class="w-full max-w-5xl space-y-6">
    <div>
        <h2 class="font-luxury text-3xl text-gray-800">Data Diri</h2>
        <p class="text-gray-500 text-sm">Perbarui informasi akun Anda agar data pemesanan tetap akurat.</p>
    </div>

    <?php if ($profileMessage !== ''): ?>
        <div class="rounded-2xl border px-4 py-3 text-sm font-semibold <?= $profileMessageType === 'error' ? 'bg-red-100 text-red-700 border-red-200' : 'bg-green-100 text-green-700 border-green-200'; ?>">
            <?= htmlspecialchars($profileMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_2fr] gap-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex items-center gap-4">
                <div class="h-14 w-14 bg-primary rounded-full flex items-center justify-center text-white font-bold text-lg">
                    <?= strtoupper(substr((string) ($akun['nama'] ?? $usernameLogin), 0, 2)); ?>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800"><?= htmlspecialchars((string) ($akun['nama'] ?? $usernameLogin), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="text-xs text-gray-400"><?= htmlspecialchars((string) ($akun['rule'] ?? 'customer'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 gap-5 text-sm">
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Username</p>
                    <p class="font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) ($akun['username'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Email</p>
                    <p class="font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) ($akun['email'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Nomor Telepon</p>
                    <p class="font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) ($akun['nomortelepon'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Status Akun</p>
                    <p class="font-semibold text-gray-800 mt-1"><?= (($akun['status'] ?? '') == 'Y') ? 'Aktif' : 'Belum diverifikasi'; ?></p>
                </div>
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Alamat</p>
                    <p class="font-semibold text-gray-800 mt-1"><?= htmlspecialchars((string) ($akun['alamat'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-bold text-gray-800 mb-5">Edit Profil</h3>
            <form method="POST" action="" class="space-y-4">
                <?= csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nama Lengkap</label>
                        <input type="text" name="nama" required value="<?= htmlspecialchars((string) ($akun['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Nomor Telepon</label>
                        <input type="text" name="nomortelepon" required value="<?= htmlspecialchars((string) ($akun['nomortelepon'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Alamat</label>
                    <textarea name="alamat" rows="3" required class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none"><?= htmlspecialchars((string) ($akun['alamat'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars((string) ($akun['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Password Baru (opsional)</label>
                    <input type="password" name="password" minlength="8" placeholder="Kosongkan bila tidak ingin mengganti password (minimal 8 karakter)" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
                </div>

                <button type="submit" name="update_profil" class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-600 transition">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
</div>
