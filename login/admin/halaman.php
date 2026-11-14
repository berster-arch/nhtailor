<?php
require_once __DIR__ . '/guard.php';

$halaman = $_GET['halaman'];
$halaman_superadmin = ['buat_pesanan', 'progres_kerja', 'data_pelanggan', 'daftar_member', 'keuangan', 'tambah_foto', 'view_galeri', 'hapus', 'edit'];
$halaman_customer = ['data_diri', 'data_ukuran', 'riwayat'];

if (($rule != 'superadmin' && in_array($halaman, $halaman_superadmin)) || ($rule == 'superadmin' && in_array($halaman, $halaman_customer))) {
    echo "<div class='bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-gray-500 text-sm'>Halaman tidak sesuai dengan hak akses akun Anda.</div>";
    return;
}

switch ($halaman) {
    case 'buat_pesanan':
        if ($rule == 'superadmin') {
            include('superadmin/formpesanan.php');
        }
        break;
    
    case 'progres_kerja':
        if ($rule == 'superadmin') {
            include('superadmin/view_progress.php');
        }
        break;

    case 'keuangan':
        if ($rule == 'superadmin') {
            include('superadmin/keuangan.php');
        }
        break;

    case 'data_pelanggan':
        if ($rule == 'superadmin') {
            include('superadmin/data_pelanggan.php');
        }
        break;

    case 'daftar_member':
        if ($rule == 'superadmin') {
            include('superadmin/daftar_member.php');
        }
        break;

    case 'data_diri':
        if ($rule != 'superadmin') {
            include('costumer/data_diri.php');
        }
        break;

    case 'data_ukuran':
        if ($rule != 'superadmin') {
            include('costumer/data_ukuran.php');
        }
        break;

    case 'riwayat':
        if ($rule != 'superadmin') {
            include('costumer/riwayat.php');
        }
        break;
    
    case 'tambah_foto':
        if ($rule == 'superadmin') {
            include('galeri/tambah_foto.php');
        }
        break;

    case 'view_galeri':
        if ($rule == 'superadmin') {
            include('galeri/view_galeri.php');
        }
        break;
    
    case 'hapus':
        if ($rule == 'superadmin') {
            include('galeri/hapus_foto.php');
        }
        break;

     case 'edit':
        if ($rule == 'superadmin') {
            include('galeri/edit_foto.php');
        }
        break;

    default:
        echo "<div class='bg-white p-8 rounded-2xl border border-gray-100 shadow-sm text-gray-500 text-sm'>Halaman tidak ditemukan atau tidak sesuai dengan hak akses akun Anda.</div>";
        break;
}

?>
