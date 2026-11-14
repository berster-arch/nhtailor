<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form menggunakan atribut 'name'
    $namalengkap = $_POST['namalengkap'];
    $alamat = $_POST['alamat'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['psw'];
    $password_repeat = $_POST['psw-konfirm'];

    // Validasi sederhana
    if ($password !== $password_repeat) {
        echo "Password tidak cocok!";
        exit;
    }

    echo "Data diterima!<br>";
    echo "Email: " . $email;
}
?>