<?php
require_once __DIR__ . '/koneksi.php';

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};

$isDetail = isset($_GET['slug']);
$artikel = null;
$daftarArtikel = [];
$loadError = false;

try {
    if ($isDetail) {
        $slug = is_string($_GET['slug']) ? $_GET['slug'] : '';
        $statement = mysqli_prepare($koneksi, "SELECT judul, tanggal_created, gambar, isi FROM artikel WHERE slug = ?");
        mysqli_stmt_bind_param($statement, "s", $slug);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        $artikel = mysqli_fetch_assoc($result) ?: null;
        mysqli_stmt_close($statement);
    } else {
        $result = mysqli_query($koneksi, "SELECT judul, slug, gambar, isi FROM artikel ORDER BY id_artikel DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $daftarArtikel[] = $row;
        }
        mysqli_free_result($result);
    }
} catch (mysqli_sql_exception $exception) {
    error_log("Failed to load articles: " . $exception->getMessage());
    http_response_code(500);
    $loadError = true;
}
?>
<?php if ($loadError) { ?>
    <section style="padding: 100px 10% 60px;">
        <p>Artikel belum dapat dimuat. Silakan coba kembali nanti.</p>
    </section>
<?php } elseif ($isDetail && $artikel === null) { ?>
    <section style="padding: 100px 10% 60px;">
        <p>Artikel tidak ditemukan.</p>
        <a href="./?halaman=artikel" style="color: var(--primary); text-decoration: none;">Kembali ke Daftar Artikel</a>
    </section>
<?php } elseif ($isDetail) {
    $timestamp = strtotime((string) $artikel['tanggal_created']);
    $tanggalArtikel = $timestamp === false ? '' : date('d M Y', $timestamp);
?>
    <section style="padding: 100px 10% 60px; background: var(--white);">
        <a href="./?halaman=artikel" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Artikel
        </a>
        <h1 style="font-family: 'Playfair Display', serif; font-size: 2.5rem; margin: 20px 0 10px;"><?= $escape($artikel['judul']); ?></h1>
        <p style="color: #777; font-size: 0.85rem; margin-bottom: 25px;">
            <i class="far fa-calendar-alt"></i> <?= $escape($tanggalArtikel); ?>
        </p>
        <img src="foto/<?= $escape($artikel['gambar']); ?>" alt="<?= $escape($artikel['judul']); ?>" style="width: 100%; max-height: 450px; object-fit: cover; border-radius: 8px; margin-bottom: 30px;">
        <div style="line-height: 1.8; color: #333; font-size: 1rem;">
            <?= nl2br($escape($artikel['isi'])); ?>
        </div>
    </section>
<?php } else { ?>
    <section style="padding: 100px 10% 60px;">
        <h2 class="section-title" style="text-align: center;">Artikel & Tips Busana</h2>
        <div class="service-grid">
            <?php if ($daftarArtikel === []) { ?>
                <p>Belum ada artikel.</p>
            <?php } else {
                foreach ($daftarArtikel as $row) {
            ?>
                <div class="service-card" style="text-align: left; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <img src="foto/<?= $escape($row['gambar']); ?>" alt="<?= $escape($row['judul']); ?>" loading="lazy" decoding="async" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px; margin-bottom: 15px;">
                        <h3 style="font-size: 1.2rem; margin-bottom: 10px;"><?= $escape($row['judul']); ?></h3>
                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">
                            <?= $escape(substr(strip_tags((string) $row['isi']), 0, 100)); ?>...
                        </p>
                    </div>
                    <a href="./?halaman=artikel&amp;slug=<?= rawurlencode((string) $row['slug']); ?>" style="color: var(--primary); text-decoration: none; font-weight: 600; font-size: 0.9rem;">
                        Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php
                }
            } ?>
        </div>
    </section>
<?php } ?>
