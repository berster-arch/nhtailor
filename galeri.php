<?php
    if (!defined('NH_GALLERY_EMBEDDED')) {
        header('Location: ./?halaman=galeri');
        exit;
    }

    require_once __DIR__ . '/koneksi.php';
    $data = false;
    $galleryError = false;
    try {
        $data = mysqli_query($koneksi, "SELECT * FROM galeri ORDER BY id DESC");
    } catch (mysqli_sql_exception $exception) {
        error_log("Failed to load gallery: " . $exception->getMessage());
        http_response_code(500);
        $galleryError = true;
    }
?>

<style>
        .gallery-section { 
            padding: 40px 10%; 
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 35px;
        }
        .section-title h2 { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--dark); }
        .section-title .line { width: 40px; height: 1.5px; background: var(--primary); margin: 10px auto; }
        .section-title p { font-size: 0.85rem; color: #888; font-weight: 300; }

        .gallery-tools {
            max-width: 560px;
            margin: 0 auto 24px;
        }

        .gallery-search-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .gallery-search {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8d2cb;
            border-radius: 4px;
            background: var(--white);
            color: var(--dark);
            font: inherit;
        }

        .gallery-search:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 2px;
        }

        .gallery-result-count {
            min-height: 1.5em;
            margin-top: 8px;
            color: #666;
            font-size: 0.8rem;
        }

        .gallery-empty-state {
            grid-column: 1 / -1;
            padding: 28px 16px;
            border: 1px dashed #d8d2cb;
            border-radius: 4px;
            color: #666;
            text-align: center;
        }

        /* --- GALLERY GRID --- */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--white);
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            transition: var(--transition);
            border: 1px solid #f1f1f1;
        }
        .card:hover { transform: translateY(-6px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }

        .card img {
            width: 100%;
            height: 320px;
            object-fit: cover;
            display: block;
        }

        .gallery-preview {
            display: block;
            width: 100%;
            border: 0;
            padding: 0;
            background: transparent;
            cursor: zoom-in;
        }

        .gallery-preview:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: -3px;
        }

        .gallery-lightbox {
            position: fixed;
            width: min(92vw, 1000px);
            max-width: none;
            max-height: 90vh;
            padding: 20px;
            margin: auto;
            border: 0;
            border-radius: 8px;
            background: #171717;
            color: var(--white);
            overflow: auto;
        }

        .gallery-lightbox::backdrop { background: rgba(0, 0, 0, 0.82); }
        .gallery-lightbox img { display: block; max-width: 100%; max-height: 75vh; margin: 0 auto; object-fit: contain; }
        .gallery-lightbox figcaption { padding: 12px 42px 0 0; text-align: center; }
        .gallery-lightbox form { position: absolute; top: 10px; right: 10px; }
        .gallery-lightbox-close { width: 38px; height: 38px; border: 1px solid rgba(255,255,255,.6); border-radius: 50%; background: #171717; color: white; font-size: 1.4rem; cursor: pointer; }

        .card-body {
            padding: 15px;
            text-align: center;
        }

        .gallery-inquiry {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 12px;
            padding: 9px 14px;
            border: 1px solid #26734d;
            border-radius: 4px;
            color: #1d6843;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .gallery-inquiry:hover {
            background: #1d6843;
            color: var(--white);
        }

        .gallery-inquiry:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 3px;
        }

        .card-body h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: var(--dark);
            margin-bottom: 4px;
            font-weight: 600;
        }

        .card-body p {
            font-size: 0.65rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 500;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .gallery-section { padding: 30px 5%; }
            .card img { height: 280px; }
            .section-title h2 { font-size: 1.6rem; }
        }
</style>

    <div class="gallery-section">
        <div class="section-title">
            <h2>Galeri Karya</h2>
            <div class="line"></div>
            <p>Sentuhan karya tangan profesional NH Tailor</p>
        </div>

        <div class="gallery-tools">
            <label class="gallery-search-label" for="gallery-search">Cari karya berdasarkan nama</label>
            <input class="gallery-search" id="gallery-search" type="search" autocomplete="off" placeholder="Contoh: kebaya, pesta, batik">
            <p class="gallery-result-count" id="gallery-result-count" aria-live="polite"></p>
        </div>

        <div class="gallery-grid">
            <?php if ($galleryError): ?>
                <p class="gallery-empty-state">Galeri belum dapat dimuat. Silakan coba kembali nanti.</p>
            <?php else: ?>
                <?php $galleryCount = mysqli_num_rows($data); ?>
                <?php
                if ($galleryCount > 0) {
                    while ($d = mysqli_fetch_array($data)) {
                    $photoFile = basename((string) $d['foto']);
                    $photoTitle = trim((string) $d['keterangan']);
                    $photoTitle = $photoTitle !== '' ? $photoTitle : 'Karya NH Tailor';
                    $photoUrl = 'foto/' . rawurlencode($photoFile);
                    $escapedPhotoUrl = htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8');
                    $escapedPhotoTitle = htmlspecialchars($photoTitle, ENT_QUOTES, 'UTF-8');
                    $inquiryText = rawurlencode('Halo NH Tailor, saya tertarik dengan model ' . $photoTitle . '. Bisa konsultasi?');
                    $inquiryUrl = htmlspecialchars('https://wa.me/6281370803255?text=' . $inquiryText, ENT_QUOTES, 'UTF-8');
            ?>
                <div class="card" data-gallery-title="<?php echo $escapedPhotoTitle; ?>">
                    <button class="gallery-preview" type="button" data-image="<?php echo $escapedPhotoUrl; ?>" data-title="<?php echo $escapedPhotoTitle; ?>" aria-label="Lihat foto: <?php echo $escapedPhotoTitle; ?>">
                        <img src="<?php echo $escapedPhotoUrl; ?>" alt="" loading="lazy" decoding="async">
                    </button>
                    <div class="card-body">
                        <h3><?php echo $escapedPhotoTitle; ?></h3>
                        <p>Premium Collection</p>
                        <a class="gallery-inquiry" href="<?php echo $inquiryUrl; ?>" target="_blank" rel="noopener noreferrer" aria-label="Tanya melalui WhatsApp tentang <?php echo $escapedPhotoTitle; ?>">
                            <i class="fab fa-whatsapp" aria-hidden="true"></i> Tanya model ini
                        </a>
                    </div>
                </div>
                    <?php
                    }
                } else {
                    echo '<p class="gallery-empty-state">Belum ada foto koleksi saat ini.</p>';
                }
                ?>
            <?php endif; ?>
            <p class="gallery-empty-state" id="gallery-no-results" hidden>Tidak ada karya yang cocok. Coba kata kunci lain.</p>
        </div>
    </div>

    <dialog class="gallery-lightbox" aria-label="Pratinjau foto karya">
        <form method="dialog">
            <button class="gallery-lightbox-close" type="submit" aria-label="Tutup pratinjau">&times;</button>
        </form>
        <figure>
            <img src="" alt="">
            <figcaption></figcaption>
        </figure>
    </dialog>

    <script>
        (() => {
            const lightbox = document.querySelector('.gallery-lightbox');
            if (!lightbox || typeof lightbox.showModal !== 'function') return;

            const image = lightbox.querySelector('img');
            const caption = lightbox.querySelector('figcaption');
            let lastPreview;

            document.querySelectorAll('.gallery-preview').forEach(preview => {
                preview.addEventListener('click', () => {
                    lastPreview = preview;
                    image.src = preview.dataset.image;
                    image.alt = preview.dataset.title;
                    caption.textContent = preview.dataset.title;
                    lightbox.showModal();
                });
            });

            lightbox.addEventListener('click', event => {
                if (event.target === lightbox) lightbox.close();
            });

            lightbox.addEventListener('cancel', event => {
                event.preventDefault();
                lightbox.close();
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && lightbox.open) {
                    event.preventDefault();
                    lightbox.close();
                }
            });

            lightbox.addEventListener('close', () => {
                image.removeAttribute('src');
                image.alt = '';
                caption.textContent = '';
                if (lastPreview) lastPreview.focus();
            });
        })();

        (() => {
            const search = document.getElementById('gallery-search');
            const resultCount = document.getElementById('gallery-result-count');
            const emptyState = document.getElementById('gallery-no-results');
            const cards = Array.from(document.querySelectorAll('.gallery-grid .card'));
            if (!search || !resultCount || !emptyState) return;

            const updateResults = () => {
                const query = search.value.trim().toLocaleLowerCase('id');
                let visibleCount = 0;

                cards.forEach(card => {
                    const title = card.dataset.galleryTitle.toLocaleLowerCase('id');
                    const matches = title.includes(query);
                    card.hidden = !matches;
                    if (matches) visibleCount += 1;
                });

                emptyState.hidden = visibleCount > 0 || cards.length === 0;
                resultCount.textContent = `Menampilkan ${visibleCount} dari ${cards.length} karya`;
            };

            search.addEventListener('input', updateResults);
            updateResults();
        })();
    </script>