<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$akses_param = $_GET['akses'] ?? '';
$akses = is_string($akses_param) ? $akses_param : '';
$daftar_session = [
    'admin' => 'NH_TAILOR_ADMIN',
    'customer' => 'NH_TAILOR_CUSTOMER'
];

if (!array_key_exists($akses, $daftar_session)) {
    if (isset($_COOKIE[$daftar_session['admin']]) && !isset($_COOKIE[$daftar_session['customer']])) {
        $akses = 'admin';
    } elseif (isset($_COOKIE[$daftar_session['customer']]) && !isset($_COOKIE[$daftar_session['admin']])) {
        $akses = 'customer';
    } else {
        $akses = 'admin';
    }
}

session_name($daftar_session[$akses]);
session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/../../koneksi.php';

// CEK SESSION LOGIN
if (!isset($_SESSION['username'], $_SESSION['rule'])
    || !is_string($_SESSION['username'])
    || !is_string($_SESSION['rule'])) {
    header("Location: ../index.php");
    exit;
}

$rule = $_SESSION['rule'];
$username = $_SESSION['username'];

$accountStatement = mysqli_prepare($koneksi, 'SELECT status, rule FROM login WHERE username = ? LIMIT 1');
if ($accountStatement === false) {
    error_log('Admin session validation failed: ' . mysqli_error($koneksi));
    http_response_code(503);
    exit('Sesi tidak dapat divalidasi. Silakan coba kembali.');
}
mysqli_stmt_bind_param($accountStatement, 's', $username);
if (!mysqli_stmt_execute($accountStatement)) {
    error_log('Admin session validation query failed: ' . mysqli_stmt_error($accountStatement));
    mysqli_stmt_close($accountStatement);
    http_response_code(503);
    exit('Sesi tidak dapat divalidasi. Silakan coba kembali.');
}
mysqli_stmt_bind_result($accountStatement, $accountStatus, $accountRule);
$accountIsActive = mysqli_stmt_fetch($accountStatement) && $accountStatus === 'Y' && $accountRule === $rule;
mysqli_stmt_close($accountStatement);

if (!$accountIsActive) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$halaman_param = $_GET['halaman'] ?? '';
$halaman = is_string($halaman_param) ? $halaman_param : '';

if (($akses == 'admin' && $rule != 'superadmin') || ($akses == 'customer' && $rule == 'superadmin')) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

define('NH_ADMIN_AUTHORIZED', true);

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars((string) $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        http_response_code(403);
        exit('Permintaan tidak valid atau sesi formulir telah kedaluwarsa. Muat ulang halaman dan coba kembali.');
    }
}

function panel_url($halaman = '', $params = []) {
    global $akses;

    $query = ['akses' => $akses];
    if ($halaman !== '') {
        $query['halaman'] = $halaman;
    }

    return 'index.php?' . http_build_query(array_merge($query, $params));
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NH Tailor | <?= htmlspecialchars(ucfirst((string) $rule), ENT_QUOTES, 'UTF-8'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap');

        :root {
            --primary: #d4a373;
            --dark: #1a1a1a;
            --light: #faf8f5;
            --white: #ffffff;
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light);
            color: var(--dark);
        }

        .font-luxury {
            font-family: 'Playfair Display', serif;
        }

        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        
        .sidebar {
            background-color: var(--dark);
            transition: var(--transition);
        }

        .nav-link {
            color: #a0a0a0;
            transition: var(--transition);
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--white);
            background-color: rgba(212, 163, 115, 0.1);
            border-left: 4px solid var(--primary);
        }

        .card-stat {
            background: var(--white);
            border: 1px solid rgba(212, 163, 115, 0.1);
            transition: var(--transition);
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        #dropdown-profile {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        #dropdown-profile.show {
            max-height: 200px;
        }

        .admin-sidebar { position: relative; z-index: 40; }
        .sidebar-backdrop { display: none; }

        @media (max-width: 767px) {
            .admin-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(18rem, 86vw);
                min-height: 100vh;
                overflow-y: auto;
                transform: translateX(-105%);
                transition: transform 0.25s ease;
                box-shadow: 12px 0 32px rgba(0, 0, 0, 0.2);
            }

            .admin-sidebar.open { transform: translateX(0); }
            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                z-index: 30;
                border: 0;
                background: rgba(17, 17, 17, 0.55);
            }
            .sidebar-backdrop.open { display: block; }
            .admin-main { width: 100%; padding: 1.25rem; }
            .admin-page-header { gap: 1rem; margin-bottom: 1.5rem; }
            .admin-page-header h1 { font-size: 1.35rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-sidebar { transition: none; }
        }
    </style>
</head>
<body class="flex min-h-screen">

    <!-- SIDEBAR NAVIGATION -->
    <aside class="sidebar admin-sidebar w-64 min-h-screen p-6 flex-shrink-0" id="admin-sidebar" aria-label="Navigasi dashboard">
        <div class="mb-10 text-2xl font-luxury font-bold text-white tracking-wider">
            NH <span class="text-primary">Tailor</span>
        </div>
        
        <nav class="space-y-2">
            <a href="<?= panel_url(); ?>" class="nav-link <?= ($halaman == '') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                <i class="fas fa-th-large"></i> <span>Beranda</span>
            </a>

            <?php if ($rule == 'superadmin'): ?>
                <a href="<?= panel_url('buat_pesanan'); ?>" class="nav-link <?= ($halaman == 'buat_pesanan') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-pen-nib"></i> <span>Input Pesanan</span>
                </a>
                <a href="<?= panel_url('view_galeri'); ?>" class="nav-link <?= ($halaman == 'view_galeri') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-camera"></i> <span>Galeri</span>
                </a>
                <a href="<?= panel_url('progres_kerja'); ?>" class="nav-link <?= ($halaman == 'progres_kerja') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-cut"></i> <span>Progres Kerja</span>
                </a>
                <a href="<?= panel_url('data_pelanggan'); ?>" class="nav-link <?= ($halaman == 'data_pelanggan') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-users"></i> <span>Pelanggan</span>
                </a>
                <a href="<?= panel_url('daftar_member'); ?>" class="nav-link <?= ($halaman == 'daftar_member') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-user-plus"></i> <span>Daftarkan Member</span>
                </a>
                <a href="<?= panel_url('keuangan'); ?>" class="nav-link <?= ($halaman == 'keuangan') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-coins"></i> <span>Keuangan</span>
                </a>

            <?php else: ?>
                <div class="relative">
                    <button onclick="toggleDropdown()" class="w-full nav-link flex items-center justify-between p-3 rounded-r-lg focus:outline-none">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-user-circle"></i> 
                            <span>Profil Saya</span>
                        </div>
                        <i id="arrow-icon" class="fas fa-chevron-down text-[10px] transition-transform duration-300"></i>
                    </button>
    
                    <div id="dropdown-profile" class="bg-black/20 mt-1 rounded-lg ml-4">
                        <a href="<?= panel_url('data_diri'); ?>" class="nav-link <?= ($halaman == 'data_diri') ? 'active' : '' ?> flex items-center space-x-3 p-3 text-sm hover:text-white">
                            <i class="fas fa-id-card text-xs"></i> <span>Data Diri</span>
                        </a>
                        <a href="<?= panel_url('data_ukuran'); ?>" class="nav-link <?= ($halaman == 'data_ukuran') ? 'active' : '' ?> flex items-center space-x-3 p-3 text-sm hover:text-white">
                            <i class="fas fa-ruler-combined text-xs"></i> <span>Data Ukuran</span>
                        </a>
                    </div>
                </div>

                <a href="<?= panel_url('riwayat'); ?>" class="nav-link <?= ($halaman == 'riwayat') ? 'active' : '' ?> flex items-center space-x-3 p-3 rounded-r-lg">
                    <i class="fas fa-history"></i> <span>Riwayat Pesanan</span>
                </a>
            <?php endif; ?>

            <div class="pt-10 border-t border-gray-800 mt-10">
                <a href="logout.php?akses=<?= urlencode($akses); ?>" class="text-gray-500 hover:text-red-400 p-3 flex items-center space-x-3 text-sm transition-colors">
                    <i class="fas fa-power-off"></i> <span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>
    <button class="sidebar-backdrop" id="sidebar-backdrop" type="button" aria-label="Tutup navigasi"></button>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="admin-main flex-1 p-6 lg:p-8 overflow-y-auto">
        <header class="admin-page-header flex justify-end items-center mb-5">
            <div class="flex items-center gap-3">
                <button id="admin-menu-toggle" type="button" class="md:hidden inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm" aria-expanded="false" aria-controls="admin-sidebar">
                    <i class="fas fa-bars" aria-hidden="true"></i> Menu
                </button>
                <div class="flex items-center gap-3 rounded-full border border-gray-200 bg-white py-1.5 pl-1.5 pr-4 shadow-sm">
                    <div class="h-9 w-9 bg-primary rounded-full flex items-center justify-center text-white font-bold text-sm">
                        <?= htmlspecialchars(strtoupper(substr((string) $username, 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <span class="max-w-40 truncate text-sm font-semibold text-gray-700"><?= htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
        </header>

        <div class="content">
            <?php 
            if (!empty($halaman)) {
                include "halaman.php"; 
            } else {
                if ($rule == 'superadmin'): ?>
                    <?php include "superadmin/dashboard.php"; ?>

                <?php else: ?>
                    <!-- TAMPILAN BERANDA USER BIASA -->
                    <?php include "costumer/dashboard.php"; ?>
                <?php endif;
            }
            ?>
        </div>
    </main>

    <script>
        window.addEventListener('pageshow', event => {
            if (event.persisted) {
                window.location.reload();
            }
        });

        const adminMenuToggle = document.getElementById('admin-menu-toggle');
        const adminSidebar = document.getElementById('admin-sidebar');
        const sidebarBackdrop = document.getElementById('sidebar-backdrop');

        function setAdminMenuOpen(isOpen) {
            adminSidebar.classList.toggle('open', isOpen);
            sidebarBackdrop.classList.toggle('open', isOpen);
            sidebarBackdrop.hidden = !isOpen;
            adminMenuToggle.setAttribute('aria-expanded', String(isOpen));
            document.body.classList.toggle('overflow-hidden', isOpen);
        }

        adminMenuToggle.addEventListener('click', () => {
            setAdminMenuOpen(!adminSidebar.classList.contains('open'));
        });

        sidebarBackdrop.addEventListener('click', () => setAdminMenuOpen(false));
        adminSidebar.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => setAdminMenuOpen(false));
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && adminSidebar.classList.contains('open')) {
                setAdminMenuOpen(false);
                adminMenuToggle.focus();
            }
        });

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdown-profile');
            const arrow = document.getElementById('arrow-icon');
            dropdown.classList.toggle('show');
            if (dropdown.classList.contains('show')) {
                arrow.style.transform = 'rotate(180deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdown-profile');
            const button = dropdown ? dropdown.previousElementSibling : null;
            if (button && !button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
                const arrow = document.getElementById('arrow-icon');
                if(arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });
    </script>
</body>
</html>
