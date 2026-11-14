<?php
require_once __DIR__ . '/../guard.php';

$pesan = '';
$berhasil = false;
$nama = '';
$alamat = '';
$nomortelepon = '';
$usernameMember = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar_member'])) {
    $nama = trim((string) ($_POST['nama'] ?? ''));
    $alamat = trim((string) ($_POST['alamat'] ?? ''));
    $nomortelepon = trim((string) ($_POST['nomortelepon'] ?? ''));
    $usernameMember = trim((string) ($_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $konfirmasiPassword = (string) ($_POST['konfirmasi_password'] ?? '');

    if ($nama === '' || $alamat === '' || $nomortelepon === '' || $usernameMember === '' || $email === '') {
        $pesan = 'Mohon lengkapi semua data member.';
    } elseif (
        strlen($nama) > 100 || strlen($alamat) > 65535 || strlen($nomortelepon) > 30
        || strlen($usernameMember) > 50 || strlen($email) > 100
    ) {
        $pesan = 'Panjang data melebihi batas yang diizinkan.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $pesan = 'Password minimal 8 karakter.';
    } elseif ($password !== $konfirmasiPassword) {
        $pesan = 'Konfirmasi password tidak cocok.';
    } else {
        $checkStatement = mysqli_prepare($koneksi, 'SELECT id FROM login WHERE username = ? OR email = ? LIMIT 1');
        if ($checkStatement === false) {
            error_log('Admin member duplicate check preparation failed: ' . mysqli_error($koneksi));
            $pesan = 'Gagal memeriksa data member. Silakan coba kembali.';
        } else {
            mysqli_stmt_bind_param($checkStatement, 'ss', $usernameMember, $email);
            if (!mysqli_stmt_execute($checkStatement)) {
                error_log('Admin member duplicate check failed: ' . mysqli_stmt_error($checkStatement));
                $pesan = 'Gagal memeriksa data member. Silakan coba kembali.';
            } else {
                mysqli_stmt_store_result($checkStatement);
                if (mysqli_stmt_num_rows($checkStatement) > 0) {
                    $pesan = 'Username atau email sudah terdaftar.';
                }
            }
            mysqli_stmt_close($checkStatement);
        }

        if ($pesan === '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($passwordHash === false) {
                error_log('Admin member password hashing failed.');
                $pesan = 'Gagal mengamankan password. Silakan coba kembali.';
            } else {
                $insertStatement = mysqli_prepare(
                    $koneksi,
                    'INSERT INTO login (nama, alamat, nomortelepon, username, email, password, status, rule) VALUES (?, ?, ?, ?, ?, ?, "Y", "customer")'
                );
                if ($insertStatement === false) {
                    error_log('Admin member insert preparation failed: ' . mysqli_error($koneksi));
                    $pesan = 'Gagal menyiapkan pendaftaran member.';
                } else {
                    mysqli_stmt_bind_param($insertStatement, 'ssssss', $nama, $alamat, $nomortelepon, $usernameMember, $email, $passwordHash);
                    if (mysqli_stmt_execute($insertStatement)) {
                        $berhasil = true;
                        $pesan = 'Member berhasil didaftarkan dan dapat login menggunakan username serta password yang dibuat.';
                        $nama = $alamat = $nomortelepon = $usernameMember = $email = '';
                    } elseif (mysqli_stmt_errno($insertStatement) === 1062) {
                        $pesan = 'Username atau email sudah terdaftar.';
                    } else {
                        error_log('Admin member insert failed: ' . mysqli_stmt_error($insertStatement));
                        $pesan = 'Pendaftaran member gagal. Silakan periksa data dan coba kembali.';
                    }
                    mysqli_stmt_close($insertStatement);
                }
            }
        }
    }
}
?>

<div class="w-full max-w-4xl space-y-6">
    <div>
        <h2 class="font-luxury text-3xl text-gray-800">Daftarkan Member</h2>
        <p class="text-gray-500 text-sm">Buat akun pelanggan baru yang dapat digunakan untuk login ke dashboard member.</p>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="rounded-xl border p-4 text-sm font-semibold <?= $berhasil ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'; ?>" role="status">
            <?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= panel_url('daftar_member'); ?>" class="space-y-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <?= csrf_field(); ?>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label for="member-nama" class="mb-1 block text-xs font-bold uppercase text-gray-500">Nama Lengkap</label>
                <input id="member-nama" type="text" name="nama" maxlength="100" required value="<?= htmlspecialchars($nama, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label for="member-telepon" class="mb-1 block text-xs font-bold uppercase text-gray-500">Nomor Telepon</label>
                <input id="member-telepon" type="text" name="nomortelepon" maxlength="30" required value="<?= htmlspecialchars($nomortelepon, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary">
            </div>
            <div class="md:col-span-2">
                <label for="member-alamat" class="mb-1 block text-xs font-bold uppercase text-gray-500">Alamat</label>
                <textarea id="member-alamat" name="alamat" rows="3" required class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary"><?= htmlspecialchars($alamat, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <div>
                <label for="member-username" class="mb-1 block text-xs font-bold uppercase text-gray-500">Username</label>
                <input id="member-username" type="text" name="username" maxlength="50" required value="<?= htmlspecialchars($usernameMember, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label for="member-email" class="mb-1 block text-xs font-bold uppercase text-gray-500">Email</label>
                <input id="member-email" type="email" name="email" maxlength="100" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary">
            </div>
            <div>
                <label for="member-password" class="mb-1 block text-xs font-bold uppercase text-gray-500">Password</label>
                <input id="member-password" type="password" name="password" minlength="8" required autocomplete="new-password" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary">
                <p class="mt-1 text-xs text-gray-400">Minimal 8 karakter.</p>
            </div>
            <div>
                <label for="member-konfirmasi-password" class="mb-1 block text-xs font-bold uppercase text-gray-500">Konfirmasi Password</label>
                <input id="member-konfirmasi-password" type="password" name="konfirmasi_password" minlength="8" required autocomplete="new-password" class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm outline-none focus:border-primary">
            </div>
        </div>
        <button type="submit" name="daftar_member" class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-amber-600">
            <i class="fas fa-user-plus" aria-hidden="true"></i> Daftarkan Member
        </button>
    </form>
</div>
