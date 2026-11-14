<?php
require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar'])) {
    $nama = trim((string)($_POST['namalengkap'] ?? ''));
    $alamat = trim((string)($_POST['alamat'] ?? ''));
    $nomortelepon = trim((string)($_POST['nomortelepon'] ?? ''));
    $username = trim((string)($_POST['username'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $passwordPlain = (string)($_POST['password'] ?? '');
    $passwordKonfirm = (string)($_POST['password-konfirm'] ?? '');

    if ($nama === '' || $alamat === '' || $nomortelepon === '' || $username === '' || $email === '') {
        echo "<script>alert('Mohon lengkapi semua data yang diperlukan.');window.location='register.php';</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid.');window.location='register.php';</script>";
        exit;
    }

    if (strlen($username) > 50 || strlen($email) > 100 || strlen($nama) > 100 || strlen($nomortelepon) > 30) {
        echo "<script>alert('Panjang data melebihi batas yang diizinkan.');window.location='register.php';</script>";
        exit;
    }

    if (strlen($passwordPlain) < 8) {
        echo "<script>alert('Password minimal 8 karakter.');window.location='register.php';</script>";
        exit;
    }

    if ($passwordPlain !== $passwordKonfirm) {
        echo "<script>alert('Konfirmasi password tidak cocok');window.location='register.php';</script>";
        exit;
    }

    $checkStmt = mysqli_prepare($koneksi, 'SELECT id FROM login WHERE username = ? OR email = ? LIMIT 1');
    if ($checkStmt === false) {
        error_log('Registration duplicate check failed: ' . mysqli_error($koneksi));
        echo "<script>alert('Terjadi kesalahan sistem. Silakan coba lagi.');window.location='register.php';</script>";
        exit;
    }

    mysqli_stmt_bind_param($checkStmt, 'ss', $username, $email);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        echo "<script>alert('Username atau email sudah terdaftar.');window.location='register.php';</script>";
        exit;
    }

    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
    $insertStmt = mysqli_prepare($koneksi, 'INSERT INTO login (nama, alamat, nomortelepon, username, email, password, status, rule) VALUES (?, ?, ?, ?, ?, ?, "Y", "customer")');
    if ($insertStmt === false) {
        error_log('Registration insert preparation failed: ' . mysqli_error($koneksi));
        echo "<script>alert('Terjadi kesalahan sistem. Silakan coba lagi.');window.location='register.php';</script>";
        exit;
    }

    mysqli_stmt_bind_param($insertStmt, 'ssssss', $nama, $alamat, $nomortelepon, $username, $email, $passwordHash);

    if (mysqli_stmt_execute($insertStmt)) {
        echo "<script>alert('Registrasi berhasil');window.location='index.php';</script>";
        exit;
    }

    error_log('Registration insert failed: ' . mysqli_error($koneksi));
    echo "<script>alert('Registrasi gagal. Silakan coba lagi.');window.location='register.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun | NH Tailor</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #24211e;
            --muted: #77716a;
            --accent: #9a6234;
            --accent-dark: #754821;
            --line: #e9e3dc;
            --paper: #fffdfa;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 28px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(ellipse at 10% 10%, rgba(212, 163, 115, .18), transparent 35%),
                #f3efe9;
            color: var(--ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
        }

        .register-layout {
            width: min(100%, 1100px);
            min-height: 680px;
            display: grid;
            grid-template-columns: .85fr 1.15fr;
            overflow: hidden;
            border: 1px solid rgba(75, 55, 37, .1);
            border-radius: 22px;
            background: var(--paper);
            box-shadow: 0 28px 80px rgba(48, 36, 24, .16);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px;
            overflow: hidden;
            background:
                linear-gradient(180deg, rgba(24, 21, 18, .28), rgba(24, 21, 18, .78)),
                url("../foto/beranda.jpg") center / cover no-repeat,
                #473523;
            color: #fff;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            color: #fff;
            text-decoration: none;
        }

        .brand-mark-icon {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .55);
            border-radius: 50%;
            color: #f0c99e;
            font-family: Georgia, serif;
            font-size: 17px;
            letter-spacing: -1px;
        }

        .brand-mark-name {
            font-family: Georgia, "Times New Roman", serif;
            font-size: 21px;
            letter-spacing: .02em;
        }

        .brand-copy { max-width: 370px; margin-bottom: 34px; }
        .brand-copy .eyebrow {
            margin: 0 0 13px;
            color: #f0c99e;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
        }
        .brand-copy h1 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(32px, 4vw, 48px);
            font-weight: 400;
            line-height: 1.12;
        }
        .brand-copy p:last-child {
            max-width: 330px;
            margin: 17px 0 0;
            color: rgba(255, 255, 255, .82);
            font-size: 14px;
            line-height: 1.8;
        }

        .form-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(30px, 5vw, 58px);
        }

        .form-heading { margin-bottom: 26px; }
        .form-heading .eyebrow {
            margin: 0 0 10px;
            color: var(--accent);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .form-heading h2 {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: 34px;
            font-weight: 400;
            letter-spacing: -.03em;
        }
        .form-heading p:last-child {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 16px;
        }
        .input-box { min-width: 0; margin-bottom: 16px; }
        .input-box-full { grid-column: 1 / -1; }
        .input-box label {
            display: block;
            margin-bottom: 7px;
            color: #39342e;
            font-size: 13px;
            font-weight: 650;
        }
        .input-box input {
            width: 100%;
            min-height: 47px;
            padding: 0 13px;
            border: 1px solid var(--line);
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-box input::placeholder { color: #aaa39a; }
        .input-box input:hover { border-color: #cbbba8; }
        .input-box input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(154, 98, 52, .12);
        }

        .terms {
            margin: 2px 0 18px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
        }
        a { color: var(--accent-dark); text-decoration: none; transition: color .2s; }
        a:hover { color: #a66a35; text-decoration: underline; }
        a:focus-visible, button:focus-visible {
            outline: 3px solid rgba(154, 98, 52, .45);
            outline-offset: 3px;
        }

        .registerbtn {
            width: 100%;
            min-height: 49px;
            border: 0;
            border-radius: 9px;
            background: linear-gradient(135deg, #a87343, #84532e);
            box-shadow: 0 8px 18px rgba(117, 72, 33, .18);
            color: #fff;
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .03em;
            transition: transform .2s, box-shadow .2s, filter .2s;
        }
        .registerbtn:hover {
            transform: translateY(-1px);
            box-shadow: 0 11px 22px rgba(117, 72, 33, .24);
            filter: brightness(1.05);
        }

        .signin {
            margin-top: 22px;
            padding-top: 19px;
            border-top: 1px solid #eee9e2;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }
        .signin p { margin: 0; }
        .footer {
            margin-top: 20px;
            color: #a19a91;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 760px) {
            body { padding: 16px; }
            .register-layout {
                width: min(100%, 520px);
                min-height: 0;
                grid-template-columns: 1fr;
                border-radius: 18px;
            }
            .brand-panel { min-height: 190px; padding: 24px 28px; }
            .brand-mark-icon { width: 36px; height: 36px; font-size: 15px; }
            .brand-mark-name { font-size: 18px; }
            .brand-copy { margin: 30px 0 0; }
            .brand-copy .eyebrow { margin-bottom: 8px; }
            .brand-copy h1 { font-size: 29px; }
            .brand-copy p:last-child { display: none; }
            .form-panel { padding: 30px 28px; }
            .form-heading { margin-bottom: 22px; }
            .form-heading h2 { font-size: 30px; }
        }

        @media (max-width: 460px) {
            body { padding: 10px; }
            .form-grid { grid-template-columns: 1fr; }
            .input-box-full { grid-column: auto; }
            .form-panel { padding: 26px 22px; }
        }
    </style>
</head>
<body>
    <main class="register-layout">
        <section class="brand-panel" aria-label="Tentang NH Tailor">
            <a class="brand-mark" href="../">
                <span class="brand-mark-icon" aria-hidden="true">NH</span>
                <span class="brand-mark-name">NH Tailor</span>
            </a>
            <div class="brand-copy">
                <p class="eyebrow">Bergabung bersama kami</p>
                <h1>Busana impian dimulai dari sini.</h1>
                <p>Buat akun untuk memantau pesanan jahitan dan menyimpan informasi ukuran Anda.</p>
            </div>
        </section>

        <section class="form-panel" aria-labelledby="register-title">
            <form method="post" name="form1">
                <div class="form-heading">
                    <p class="eyebrow">Ruang pelanggan</p>
                    <h2 id="register-title">Daftar akun</h2>
                    <p>Lengkapi informasi berikut untuk membuat akun NH Tailor.</p>
                </div>

                <div class="form-grid">
                    <div class="input-box">
                        <label for="namalengkap">Nama Lengkap</label>
                        <input type="text" placeholder="Masukkan nama lengkap" name="namalengkap" id="namalengkap" maxlength="100" autocomplete="name" required>
                    </div>

                    <div class="input-box">
                        <label for="nomortelepon">Nomor Telepon</label>
                        <input type="tel" placeholder="Masukkan nomor telepon" name="nomortelepon" id="nomortelepon" maxlength="30" autocomplete="tel" required>
                    </div>

                    <div class="input-box input-box-full">
                        <label for="alamat">Alamat</label>
                        <input type="text" placeholder="Masukkan alamat lengkap" name="alamat" id="alamat" autocomplete="street-address" required>
                    </div>

                    <div class="input-box">
                        <label for="username">Username</label>
                        <input type="text" placeholder="Buat username" name="username" id="username" maxlength="50" autocomplete="username" required>
                    </div>

                    <div class="input-box">
                        <label for="email">E-mail</label>
                        <input type="email" placeholder="nama@email.com" name="email" id="email" maxlength="100" autocomplete="email" required>
                    </div>

                    <div class="input-box">
                        <label for="password">Password</label>
                        <input type="password" placeholder="Minimal 8 karakter" name="password" id="password" minlength="8" autocomplete="new-password" required>
                    </div>

                    <div class="input-box">
                        <label for="password-konfirm">Konfirmasi Password</label>
                        <input type="password" placeholder="Ulangi password" name="password-konfirm" id="password-konfirm" minlength="8" autocomplete="new-password" required>
                    </div>
                </div>

                <p class="terms">Dengan membuat akun, Anda menyetujui <a href="syarat.php">Syarat &amp; Ketentuan</a> kami.</p>
                <button type="submit" name="daftar" class="registerbtn">Buat Akun</button>

                <div class="signin">
                    <p>Sudah punya akun? <a href="index.php">Masuk sekarang</a></p>
                </div>

                <div class="footer">© 2026 NH Tailor</div>
            </form>
        </section>
    </main>
</body>
</html>
