<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

$akses_param = $_GET['akses'] ?? 'admin';
$akses = is_string($akses_param) ? $akses_param : 'admin';
$daftar_session = [
    'admin' => 'NH_TAILOR_ADMIN',
    'customer' => 'NH_TAILOR_CUSTOMER'
];

if (!array_key_exists($akses, $daftar_session)) {
    $akses = 'admin';
}

session_name($daftar_session[$akses]);
session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();
session_unset();
session_destroy();

$params = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 42000,
    'path' => $params['path'],
    'domain' => $params['domain'],
    'secure' => $params['secure'],
    'httponly' => $params['httponly'],
    'samesite' => $params['samesite'] ?? 'Lax',
]);

header("Location: ../index.php");
exit;
?>
