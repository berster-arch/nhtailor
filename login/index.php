<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        echo "<script>alert('Username dan password wajib diisi!'); window.location.href='index.php';</script>";
        exit();
    }

    $stmt = mysqli_prepare($koneksi, 'SELECT username, password, rule, status FROM login WHERE username = ? LIMIT 1');
    if ($stmt === false) {
        error_log('Login query preparation failed: ' . mysqli_error($koneksi));
        echo "<script>alert('Terjadi kesalahan sistem. Silakan coba lagi.'); window.location.href='index.php';</script>";
        exit();
    }

    mysqli_stmt_bind_param($stmt, 's', $username);
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Login query failed: ' . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        echo "<script>alert('Terjadi kesalahan sistem. Silakan coba lagi.'); window.location.href='index.php';</script>";
        exit();
    }

    mysqli_stmt_bind_result($stmt, $dbUsername, $dbPassword, $dbRule, $dbStatus);
    $accountFound = mysqli_stmt_fetch($stmt);
    $storedPassword = (string) $dbPassword;
    mysqli_stmt_close($stmt);

    if ($accountFound && $dbStatus === 'Y' && password_matches($password, $storedPassword)) {
        $passwordInfo = password_get_info($storedPassword);
        if (empty($passwordInfo['algo']) || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($newPasswordHash === false) {
                error_log('Password hash upgrade failed: password_hash returned false.');
            } else {
                $hashStatement = mysqli_prepare($koneksi, 'UPDATE login SET password = ? WHERE username = ? AND password = ?');
                if ($hashStatement === false) {
                    error_log('Password hash upgrade preparation failed: ' . mysqli_error($koneksi));
                } else {
                    mysqli_stmt_bind_param($hashStatement, 'sss', $newPasswordHash, $dbUsername, $storedPassword);
                    if (!mysqli_stmt_execute($hashStatement)) {
                        error_log('Password hash upgrade failed: ' . mysqli_stmt_error($hashStatement));
                    }
                    mysqli_stmt_close($hashStatement);
                }
            }
        }

        $akses = ($dbRule === 'superadmin') ? 'admin' : 'customer';
        $sessionName = ($akses === 'admin') ? 'NH_TAILOR_ADMIN' : 'NH_TAILOR_CUSTOMER';

        session_name($sessionName);
        session_set_cookie_params([
            'path' => '/',
            'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax',
        ]);
        session_start();
        session_regenerate_id(true);

        $_SESSION['username'] = $dbUsername;
        $_SESSION['rule'] = $dbRule;
        $_SESSION['akses'] = $akses;
        $_SESSION['login'] = true;

        header('Location: admin/index.php?akses=' . $akses);
        exit();
    }

    echo "<script>alert('Username atau Password Salah!'); window.location.href='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | NH Tailor</title>
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

        .login-layout {
            width: min(100%, 1000px);
            min-height: 620px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
            border: 1px solid rgba(75, 55, 37, .1);
            border-radius: 22px;
            background: var(--paper);
            box-shadow: 0 28px 80px rgba(48, 36, 24, .16);
        }

        .brand-panel {
            position: relative;
            min-height: 100%;
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
        .brand-copy .brand-eyebrow {
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
        .brand-copy p {
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
            padding: clamp(32px, 6vw, 68px);
        }

        .form-heading { margin-bottom: 34px; }
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
        .form-heading p {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .input-box { margin-bottom: 20px; }
        .input-box label {
            display: block;
            margin-bottom: 8px;
            color: #39342e;
            font-size: 13px;
            font-weight: 650;
        }
        .input-box input {
            width: 100%;
            min-height: 50px;
            padding: 0 15px;
            border: 1px solid var(--line);
            border-radius: 9px;
            outline: none;
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }
        .input-box input::placeholder { color: #aaa39a; }
        .input-box input:hover { border-color: #cbbba8; }
        .input-box input:focus {
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(154, 98, 52, .12);
        }

        .form-options {
            display: flex;
            justify-content: flex-end;
            margin: -4px 0 22px;
            font-size: 12px;
        }
        a { color: var(--accent-dark); text-decoration: none; transition: color .2s; }
        a:hover { color: #a66a35; text-decoration: underline; }
        a:focus-visible, button:focus-visible {
            outline: 3px solid rgba(154, 98, 52, .45);
            outline-offset: 3px;
        }

        .btn {
            width: 100%;
            min-height: 50px;
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
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 11px 22px rgba(117, 72, 33, .24);
            filter: brightness(1.05);
        }

        .signin {
            margin-top: 25px;
            padding-top: 22px;
            border-top: 1px solid #eee9e2;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }
        .signin p { margin: 0 0 12px; }
        .signin p:last-child { margin-bottom: 0; }
        .footer {
            margin-top: 28px;
            color: #a19a91;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 720px) {
            body { padding: 16px; }
            .login-layout {
                width: min(100%, 480px);
                min-height: 0;
                grid-template-columns: 1fr;
                border-radius: 18px;
            }
            .brand-panel {
                min-height: 190px;
                padding: 24px 28px;
            }
            .brand-mark-icon { width: 36px; height: 36px; font-size: 15px; }
            .brand-mark-name { font-size: 18px; }
            .brand-copy { margin: 32px 0 0; }
            .brand-copy .brand-eyebrow { margin-bottom: 8px; }
            .brand-copy h1 { font-size: 29px; }
            .brand-copy p { display: none; }
            .form-panel { padding: 32px 28px; }
            .form-heading { margin-bottom: 26px; }
            .form-heading h2 { font-size: 30px; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>

    <main class="login-layout">
        <section class="brand-panel" aria-label="Tentang NH Tailor">
            <a class="brand-mark" href="../">
                <span class="brand-mark-icon" aria-hidden="true">NH</span>
                <span class="brand-mark-name">NH Tailor</span>
            </a>
            <div class="brand-copy">
                <p class="brand-eyebrow">Ruang pelanggan</p>
                <h1>Elegansi di setiap jahitan.</h1>
                <p>Masuk untuk melihat pesanan dan melanjutkan perjalanan busana impian Anda bersama kami.</p>
            </div>
        </section>

        <section class="form-panel" aria-labelledby="login-title">
            <form method="post" action="">
                <div class="form-heading">
                    <p class="eyebrow">Selamat datang kembali</p>
                    <h2 id="login-title">Masuk akun</h2>
                    <p>Masukkan username dan password untuk melanjutkan.</p>
                </div>
                <div class="input-box">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" placeholder="Masukkan username" autocomplete="username" required>
                </div>

                <div class="input-box">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                </div>

                <div class="form-options">
                    <a href="forgot.php">Lupa Password?</a>
                </div>

                <button type="submit" name="submit" class="btn">Masuk</button>

                <div class="signin">
                    <p>Belum Punya Akun? <a href="register.php">Daftar Sekarang</a></p>
                    <p><a href="../">Kembali ke Web Utama</a></p>
                </div>

                <div class="footer">© 2026 NH Tailor</div>
            </form>
        </section>
    </main>

</body>
</html>
