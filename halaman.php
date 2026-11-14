<?php
$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'home';

switch ($halaman) {
    case 'galeri':
        define('NH_GALLERY_EMBEDDED', true);
        include('galeri.php');
        break;
    
    case 'artikel':
            include "artikel.php";
            break;

    case 'login':
        include ('login/index.php');
        break;
}

?>