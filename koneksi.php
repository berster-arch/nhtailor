<?php
function get_db_connection()
{
    static $koneksi = null;

    if ($koneksi !== null) {
        return $koneksi;
    }

    $connection = mysqli_init();
    if ($connection === false) {
        throw new RuntimeException("Gagal menyiapkan koneksi database NH Tailor.");
    }

    if (!mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 3)
        || !mysqli_options($connection, MYSQLI_OPT_READ_TIMEOUT, 3)) {
        throw new RuntimeException("Gagal mengatur batas waktu koneksi database NH Tailor.");
    }

    if (!@mysqli_real_connect($connection, "localhost", "root", "", "nh_tailor")) {
        throw new RuntimeException("Gagal menghubungkan ke database NH Tailor: " . mysqli_connect_error());
    }

    if (!mysqli_set_charset($connection, "utf8mb4")) {
        throw new RuntimeException(mysqli_error($connection));
    }

    $koneksi = $connection;
    return $koneksi;
}

function ensure_app_schema(mysqli $connection): void
{
    // Increase this version when adding or changing schema migrations.
    $schemaVersion = 1;
    $versionTableResult = mysqli_query($connection, "CREATE TABLE IF NOT EXISTS nh_schema_migrations (
        version INT NOT NULL PRIMARY KEY,
        applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($versionTableResult === false) {
        throw new RuntimeException('Schema migration tracking failed: ' . mysqli_error($connection));
    }

    $versionResult = mysqli_query($connection, 'SELECT MAX(version) AS version FROM nh_schema_migrations');
    if ($versionResult === false) {
        throw new RuntimeException('Schema version lookup failed: ' . mysqli_error($connection));
    }
    $versionRow = mysqli_fetch_assoc($versionResult);
    mysqli_free_result($versionResult);
    if ((int) ($versionRow['version'] ?? 0) >= $schemaVersion) {
        return;
    }

    $schema = [
        "CREATE TABLE IF NOT EXISTS login (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            alamat TEXT,
            nomortelepon VARCHAR(30) DEFAULT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            status ENUM('Y', 'N') NOT NULL DEFAULT 'Y',
            rule ENUM('superadmin', 'customer') NOT NULL DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS pesanan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_pelanggan VARCHAR(100) NOT NULL,
            no_hp VARCHAR(30) NOT NULL,
            username_customer VARCHAR(50) DEFAULT NULL,
            jenis_pakaian VARCHAR(100) NOT NULL,
            detail_model TEXT,
            total_biaya DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            dp DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            kain_foto VARCHAR(255) DEFAULT NULL,
            tgl_selesai DATE NOT NULL,
            status VARCHAR(100) NOT NULL DEFAULT 'Menunggu Antrian',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS galeri (
            id INT AUTO_INCREMENT PRIMARY KEY,
            foto VARCHAR(255) NOT NULL,
            keterangan TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS artikel (
            id_artikel INT AUTO_INCREMENT PRIMARY KEY,
            judul VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            tanggal_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            gambar VARCHAR(255) NOT NULL,
            isi LONGTEXT NOT NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS ukuran_customer (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            nama_ukuran VARCHAR(100) NOT NULL,
            lingkar_dada VARCHAR(20) NOT NULL,
            lingkar_pinggang VARCHAR(20) NOT NULL,
            lingkar_pinggul VARCHAR(20) NOT NULL,
            lebar_bahu VARCHAR(20) NOT NULL,
            panjang_lengan VARCHAR(20) NOT NULL,
            panjang_baju VARCHAR(20) NOT NULL,
            panjang_rok VARCHAR(20) NOT NULL,
            lebar_punggung VARCHAR(20) DEFAULT NULL,
            panjang_punggung VARCHAR(20) DEFAULT NULL,
            panjang_pinggang VARCHAR(20) DEFAULT NULL,
            panjang_panggul VARCHAR(20) DEFAULT NULL,
            panjang_muka VARCHAR(20) DEFAULT NULL,
            lebar_muka VARCHAR(20) DEFAULT NULL,
            lingkar_lengan VARCHAR(20) DEFAULT NULL,
            kerung_lengan VARCHAR(20) DEFAULT NULL,
            lingkar_pinggang_celana VARCHAR(20) DEFAULT NULL,
            lingkar_pesak VARCHAR(20) DEFAULT NULL,
            lingkar_paha VARCHAR(20) DEFAULT NULL,
            lingkar_betis_bawah VARCHAR(20) DEFAULT NULL,
            panjang_celana VARCHAR(20) DEFAULT NULL,
            lingkar_sabrina VARCHAR(20) DEFAULT NULL,
            tinggi_leher VARCHAR(20) DEFAULT NULL,
            catatan TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
    ];

    foreach ($schema as $query) {
        if (!mysqli_query($connection, $query)) {
            throw new RuntimeException('Schema migration failed: ' . mysqli_error($connection));
        }
    }

    foreach ([
        ['login', 'status', "ENUM('Y','N') NOT NULL DEFAULT 'Y' AFTER password"],
        ['pesanan', 'username_customer', 'VARCHAR(50) DEFAULT NULL AFTER no_hp'],
        ['pesanan', 'kain_foto', 'VARCHAR(255) DEFAULT NULL AFTER dp'],
        ['pesanan', 'status', "VARCHAR(100) NOT NULL DEFAULT 'Menunggu Antrian' AFTER tgl_selesai"],
        ['ukuran_customer', 'catatan', 'TEXT NULL'],
        ['ukuran_customer', 'lebar_punggung', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'panjang_punggung', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'panjang_pinggang', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'panjang_panggul', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'panjang_muka', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lebar_muka', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lingkar_lengan', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'kerung_lengan', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lingkar_pinggang_celana', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lingkar_pesak', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lingkar_paha', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lingkar_betis_bawah', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'panjang_celana', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'lingkar_sabrina', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'tinggi_leher', 'VARCHAR(20) DEFAULT NULL'],
        ['ukuran_customer', 'created_at', 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP'],
        ['ukuran_customer', 'updated_at', 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP'],
    ] as [$table, $column, $definition]) {
        $columnResult = mysqli_query($connection, "SHOW COLUMNS FROM `$table`");
        if ($columnResult === false) {
            throw new RuntimeException("Schema inspection failed for '$table.$column': " . mysqli_error($connection));
        }

        $columnExists = false;
        while ($columnInfo = mysqli_fetch_assoc($columnResult)) {
            if (($columnInfo['Field'] ?? null) === $column) {
                $columnExists = true;
                break;
            }
        }
        mysqli_free_result($columnResult);
        if (!$columnExists && !mysqli_query($connection, "ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
            throw new RuntimeException("Schema migration failed for '$table.$column': " . mysqli_error($connection));
        }
    }

    $passwordColumnResult = mysqli_query($connection, "SHOW COLUMNS FROM login LIKE 'password'");
    if ($passwordColumnResult === false) {
        throw new RuntimeException("Schema inspection failed for 'login.password': " . mysqli_error($connection));
    }
    $passwordColumn = mysqli_fetch_assoc($passwordColumnResult);
    mysqli_free_result($passwordColumnResult);
    if ($passwordColumn && strtolower((string) $passwordColumn['Type']) !== 'varchar(255)'
        && !mysqli_query($connection, 'ALTER TABLE login MODIFY COLUMN password VARCHAR(255) NOT NULL')) {
        throw new RuntimeException("Schema migration failed for 'login.password': " . mysqli_error($connection));
    }

    foreach (['login', 'pesanan', 'galeri', 'artikel', 'ukuran_customer'] as $table) {
        $result = mysqli_query($connection, "SELECT 1 FROM `$table` LIMIT 0");
        if ($result === false) {
            throw new RuntimeException("Database table '$table' is not readable: " . mysqli_error($connection));
        }
        mysqli_free_result($result);
    }

    $migrationResult = mysqli_query(
        $connection,
        'INSERT IGNORE INTO nh_schema_migrations (version) VALUES (' . $schemaVersion . ')'
    );
    if ($migrationResult === false) {
        throw new RuntimeException('Schema migration version could not be saved: ' . mysqli_error($connection));
    }
}

function password_matches(string $plainPassword, string $storedPassword): bool
{
    if ($storedPassword === '') {
        return false;
    }

    $passwordInfo = password_get_info($storedPassword);
    if (is_array($passwordInfo) && !empty($passwordInfo['algo'])) {
        return password_verify($plainPassword, $storedPassword);
    }

    if (strlen($storedPassword) === 50 && preg_match('/^\$2[aby]\$\d\d\$/', $storedPassword)) {
        $candidateHash = crypt($plainPassword, $storedPassword);
        return is_string($candidateHash)
            && hash_equals($storedPassword, substr($candidateHash, 0, strlen($storedPassword)));
    }

    return hash_equals(md5($plainPassword), $storedPassword);
}

try {
    $koneksi = get_db_connection();
    ensure_app_schema($koneksi);
} catch (Throwable $exception) {
    error_log("NH Tailor database initialization failed: " . $exception->getMessage());
    http_response_code(503);
    exit("Layanan data sedang tidak tersedia. Silakan coba kembali beberapa saat lagi.");
}
?>