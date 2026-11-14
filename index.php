<?php
$halaman = $_GET['halaman'] ?? 'home';
if (!is_string($halaman) || !in_array($halaman, ['home', 'artikel', 'galeri', 'login'], true)) {
    $halaman = 'home';
}

$pageTitles = [
    'home' => 'NH Tailor - Custom Wedding & Party Dress',
    'artikel' => 'Artikel & Tips Busana - NH Tailor',
    'galeri' => 'Galeri Karya - NH Tailor',
    'login' => 'Masuk - NH Tailor'
];
$pageDescriptions = [
    'home' => 'NH Tailor di Manado menghadirkan busana wedding, party wear, dan kebaya modern dengan jahitan premium sejak 2012.',
    'artikel' => 'Baca artikel dan tips seputar busana, kebaya, gaun pesta, dan perawatan pakaian dari NH Tailor.',
    'galeri' => 'Jelajahi karya busana custom NH Tailor dan konsultasikan model yang Anda sukai melalui WhatsApp.',
    'login' => 'Masuk ke NH Tailor.'
];

if (in_array($halaman, ['artikel', 'galeri'], true)) {
    require_once __DIR__ . '/koneksi.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitles[$halaman], ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescriptions[$halaman], ENT_QUOTES, 'UTF-8') ?>">
    <meta name="theme-color" content="#faf8f5">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet"></noscript>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"></noscript>
    
    <style>
        :root {
            --primary: #9a6234;
            --dark: #1a1a1a;
            --light: #faf8f5;
            --white: #ffffff;
            --transition: all 0.3s ease;
            --blue-hover: #0066cc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        html { scroll-behavior: smooth; }
        section[id] { scroll-margin-top: 90px; }

        body {
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* --- Header & Nav --- */
        header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 1rem 10%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 2000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        /* Sisi Kiri Header (Dropdown Menu + Logo dengan Ikon Gunting) */
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            text-decoration: none;
            z-index: 2100;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Styling khusus ikon gunting pada logo */
        .logo i {
            color: var(--primary);
            font-size: 1.5rem;
            transform: rotate(-45deg); /* Efek posisi miring artistik */
            display: inline-block;
        }

        .logo span { color: var(--primary); }

        /* Dropdown Navigasi Kiri */
        .nav-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-btn {
            background: transparent;
            color: var(--dark);
            border: 1px solid rgba(0,0,0,0.15);
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dropdown-btn:hover, .nav-dropdown:hover .dropdown-btn {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .nav-dropdown.open .dropdown-btn,
        .dropdown-btn:focus-visible {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: var(--white);
            min-width: 180px;
            box-shadow: 0px 8px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
            overflow: hidden;
            z-index: 2200;
            border: 1px solid rgba(0,0,0,0.05);
            margin-top: 5px;
        }

        .dropdown-menu a {
            color: var(--dark);
            padding: 12px 18px;
            text-decoration: none;
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .dropdown-menu a:hover {
            background-color: var(--light);
            color: var(--primary);
            padding-left: 22px;
        }

        .nav-dropdown.open .dropdown-menu {
            display: block;
        }

        /* Navigasi Kanan */
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }

        nav ul li { margin-left: 20px; }

        /* Tombol Masuk dengan Logo */
        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid var(--primary);
            padding: 8px 18px;
            color: var(--primary) !important;
            transition: var(--transition);
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            background: var(--blue-hover);
            border-color: var(--blue-hover);
            color: white !important;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid var(--primary);
            outline-offset: 4px;
        }

        /* --- Main Content --- */
        main { padding-top: 70px; }

        /* --- Hero Section --- */
        .hero {
            height: calc(100vh - 70px);
            min-height: 520px;
            position: relative;
            isolation: isolate;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            background: #333 url("foto/beranda.jpg") center / cover no-repeat;
        }

        .hero-video {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: -2;
            width: 100%;
            height: 100%;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        .hero-video.video-unavailable { display: none; }

        .hero::after {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); 
            z-index: -1;
        }

        .hero-content { position: relative; z-index: 1; padding: 0 20px; }

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 5vw, 3.5rem); 
            margin-bottom: 20px;
            font-style: italic;
        }

        .hero-content p {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 30px;
            font-weight: 300;
        }

        .btn-cta {
            background: var(--primary);
            color: var(--white);
            padding: 15px 35px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-block;
            border: 1px solid var(--primary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .btn-cta:hover {
            background: transparent;
            border-color: var(--white);
            color: var(--white);
        }

        .hero-socials {
            display: flex;
            justify-content: center;
            gap: 25px;
        }

        .hero-socials a {
            color: var(--white);
            font-size: 1.6rem;
            transition: var(--transition);
            opacity: 0.9;
        }

        .hero-socials a:hover {
            color: var(--primary);
            transform: translateY(-5px);
            opacity: 1;
        }

        .hero-socials a:focus-visible,
        .footer-socials a:focus-visible {
            outline-color: var(--white);
        }

        .content-wrapper { width: 100%; }

        .about-section { padding: 80px 10%; background-color: var(--white); }
        .about-container { display: flex; align-items: center; gap: 60px; }
        .about-image { flex: 1; }
        .about-image img { width: 100%; border-radius: 5px; box-shadow: 15px 15px 0px var(--primary); }
        .about-text { flex: 1; }
        .about-text h2 { font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 20px; }
        .about-text h2 span { color: var(--primary); }

        .services { padding: 80px 10%; text-align: center; background-color: var(--light); }
        .section-title { font-family: 'Playfair Display', serif; font-size: 2.3rem; margin-bottom: 50px; position: relative; padding-bottom: 15px; }
        .section-title::after { content: ''; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 50px; height: 2px; background: var(--primary); }
        .service-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .service-card { background: var(--white); padding: 40px; border: 1px solid #f0f0f0; transition: var(--transition); border-radius: 4px; }
        .service-card:hover { transform: translateY(-10px); border-color: var(--primary); box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .service-cta { display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 18px; padding: 9px 14px; border: 1px solid var(--primary); border-radius: 4px; color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: var(--transition); }
        .service-cta:hover { background: var(--primary); color: var(--white); }
        .gallery-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 32px; padding: 11px 18px; border: 1px solid var(--primary); border-radius: 4px; color: var(--primary); font-weight: 600; text-decoration: none; transition: var(--transition); }
        .gallery-link:hover { background: var(--primary); color: var(--white); }

        .portfolio-section { padding: 80px 10%; background: var(--white); text-align: center; }
        .portfolio-intro { max-width: 620px; margin: -25px auto 35px; color: #666; }
        .portfolio-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 22px; text-align: left; }
        .portfolio-card { overflow: hidden; border: 1px solid #eee8e1; border-radius: 5px; background: var(--light); transition: var(--transition); }
        .portfolio-card:hover { transform: translateY(-6px); box-shadow: 0 12px 28px rgba(0,0,0,.09); }
        .portfolio-image-link { position: relative; display: block; overflow: hidden; aspect-ratio: 4 / 5; background: #e9e3dc; }
        .portfolio-image-link img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .portfolio-card:hover .portfolio-image-link img { transform: scale(1.04); }
        .portfolio-image-link span { position: absolute; right: 12px; bottom: 12px; padding: 8px 11px; border-radius: 3px; background: rgba(26,26,26,.78); color: var(--white); font-size: .78rem; }
        .portfolio-card-body { padding: 17px; }
        .portfolio-card-body p { margin-bottom: 4px; color: var(--primary); font-size: .72rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
        .portfolio-card-body h3 { font-family: 'Playfair Display', serif; font-size: 1.15rem; }
        .portfolio-card-body .service-cta { margin-top: 12px; }

        .process-section { padding: 80px 10%; background: var(--light); text-align: center; }
        .process-intro { max-width: 620px; margin: -25px auto 38px; color: #666; }
        .process-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; text-align: left; }
        .process-step { position: relative; min-height: 220px; padding: 30px 26px; border: 1px solid #e8e1d8; border-radius: 5px; background: var(--white); }
        .process-step-number { display: grid; width: 42px; height: 42px; margin-bottom: 20px; place-items: center; border-radius: 50%; background: #f3e8dc; color: var(--primary); font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; }
        .process-step h3 { margin-bottom: 9px; font-family: 'Playfair Display', serif; font-size: 1.25rem; }
        .process-step p { color: #666; font-size: .9rem; }
        .process-cta { margin-top: 30px; }

        .consultation-section { padding: 80px 10%; background: var(--white); }
        .consultation-container { max-width: 720px; margin: 0 auto; }
        .consultation-container .section-title { text-align: center; }
        .consultation-intro { margin: -25px auto 30px; max-width: 560px; color: #666; text-align: center; }
        .consultation-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .consultation-field { display: flex; flex-direction: column; gap: 7px; }
        .consultation-field-full { grid-column: 1 / -1; }
        .consultation-field label { font-size: 0.9rem; font-weight: 500; }
        .consultation-field input,
        .consultation-field select,
        .consultation-field textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8d2cb;
            border-radius: 4px;
            background: var(--white);
            color: var(--dark);
            font: inherit;
        }
        .consultation-field textarea { min-height: 120px; resize: vertical; }
        .consultation-field input[type="file"] { padding: 9px; }
        .consultation-field input[type="file"]::file-selector-button {
            margin-right: 10px;
            padding: 7px 11px;
            border: 1px solid #d8d2cb;
            border-radius: 4px;
            background: var(--light);
            color: var(--dark);
            font: inherit;
            cursor: pointer;
        }
        .consultation-image-help,
        .consultation-image-status { margin: 0; color: #666; font-size: 0.82rem; }
        .consultation-image-status:empty { display: none; }
        .consultation-image-preview { display: grid; grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)); gap: 12px; }
        .consultation-image-item { display: flex; align-items: center; gap: 10px; min-width: 0; padding: 8px; border: 1px solid #e8e1d8; border-radius: 4px; }
        .consultation-image-item img { width: 56px; height: 56px; flex: 0 0 auto; border-radius: 3px; object-fit: cover; }
        .consultation-image-name { overflow-wrap: anywhere; font-size: 0.78rem; }
        .consultation-image-remove { flex: 0 0 auto; margin-left: auto; padding: 5px; border: 0; background: transparent; color: #8b2f2f; cursor: pointer; }
        .consultation-submit {
            grid-column: 1 / -1;
            justify-self: start;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 13px 22px;
            border: 1px solid #1d6843;
            border-radius: 4px;
            background: #1d6843;
            color: var(--white);
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        .consultation-submit:hover { background: #155536; border-color: #155536; }
        .consultation-note { grid-column: 1 / -1; margin-top: -8px; color: #666; font-size: 0.8rem; }
        .consultation-field input:focus-visible,
        .consultation-field select:focus-visible,
        .consultation-field textarea:focus-visible,
        .consultation-field input[type="file"]:focus-visible,
        .consultation-image-remove:focus-visible,
        .consultation-submit:focus-visible { outline: 3px solid var(--primary); outline-offset: 3px; }

        .faq-section { padding: 75px 10%; background: var(--light); }
        .faq-container { max-width: 820px; margin: 0 auto; }
        .faq-container .section-title { text-align: center; }
        .faq-list { display: grid; gap: 12px; }
        .faq-item { border: 1px solid #e8e1d8; border-radius: 5px; background: var(--white); }
        .faq-item summary { padding: 18px 20px; cursor: pointer; font-weight: 600; list-style-position: inside; }
        .faq-item summary::marker { color: var(--primary); }
        .faq-item summary:focus-visible { outline: 3px solid var(--primary); outline-offset: 3px; }
        .faq-item p { padding: 0 20px 18px; color: #555; }

        .floating-contact {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 1900;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 13px 18px;
            border-radius: 999px;
            background: #1d6843;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.2);
            color: var(--white);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }
        .floating-contact:hover { background: #155536; transform: translateY(-2px); }
        .back-to-top {
            position: fixed;
            right: 25px;
            bottom: 82px;
            z-index: 1900;
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 1px solid #ddd;
            border-radius: 50%;
            background: var(--white);
            color: var(--dark);
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }
        .back-to-top[hidden] { display: none; }
        .back-to-top:hover { color: var(--primary); }
        .floating-contact:focus-visible,
        .back-to-top:focus-visible { outline: 3px solid var(--primary); outline-offset: 3px; }

        .maps-section { padding: 80px 0 0 0; background: var(--white); text-align: center; }
        .map-container { width: 100%; height: 400px; border-top: 4px solid var(--primary); }

        footer { background: #111; color: #aaa; padding: 60px 10% 40px; text-align: center; }
        .footer-logo { color: var(--white); font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 15px; display: inline-flex; align-items: center; gap: 10px; }
        .footer-logo i { color: var(--primary); font-size: 1.6rem; transform: rotate(-45deg); }
        .footer-logo span { color: var(--primary); }
        .footer-socials { display: flex; justify-content: center; gap: 20px; margin: 20px 0; }
        .footer-socials a { color: #fff; text-decoration: none; font-size: 1.3rem; transition: 0.3s; }
        .footer-socials a:hover { color: var(--primary); }

        @media (max-width: 992px) {
            header { padding: 1rem 5%; }
            nav {
                position: static;
                width: auto;
                height: auto;
                background: transparent;
                box-shadow: none;
            }

            nav ul { flex-direction: row; justify-content: flex-end; height: auto; }
            nav ul li { margin: 0; text-align: center; width: auto; }
            
            nav ul li .btn-login {
                width: auto;
                justify-content: center;
            }

            .about-container { flex-direction: column; text-align: center; }
            .about-image img { box-shadow: 12px 12px 0px var(--primary); }
        }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2rem; }
            .map-container { height: 300px; }
            .dropdown-btn span { display: none; }
            .hero-video { display: none; }
            .portfolio-section { padding: 60px 5%; }
            .portfolio-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
            .portfolio-card-body { padding: 13px; }
            .process-section { padding: 60px 5%; }
            .process-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .consultation-form { grid-template-columns: 1fr; }
            .consultation-field-full,
            .consultation-submit,
            .consultation-note { grid-column: 1; }
        }

        @media (max-width: 480px) {
            header { padding: .75rem 4%; }
            .logo { font-size: 1.35rem; gap: 5px; }
            .logo i { font-size: 1.2rem; }
            .btn-login { padding: 8px 10px; }
            .portfolio-grid { grid-template-columns: 1fr; }
            .portfolio-image-link { aspect-ratio: 5 / 4; }
            .process-grid { grid-template-columns: 1fr; }
            .process-step { min-height: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }

            .hero-video { display: none; }
        }
    </style>
</head>
<body>

    <header>
        <div class="header-left">
            <!-- Dropdown Navigasi Kiri -->
            <div class="nav-dropdown">
                <button class="dropdown-btn" id="dropdown-toggle" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="dropdown-menu">
                    <i class="fas fa-bars" aria-hidden="true"></i> <span>Menu</span>
                </button>
                <div class="dropdown-menu" id="dropdown-menu">
                    <a href="./?halaman=home"><i class="fas fa-home"></i> Beranda</a>
                    <a href="./?halaman=artikel"><i class="fas fa-newspaper"></i> Artikel & Tips</a>
                    <a href="./?halaman=galeri"><i class="fas fa-images"></i> Galeri Karya</a>
                    <a href="./#layanan"><i class="fas fa-cut"></i> Layanan</a>
                    <a href="./#karya"><i class="fas fa-star"></i> Karya Pilihan</a>
                    <a href="./#proses"><i class="fas fa-list-check"></i> Cara Memesan</a>
                    <a href="./#tentangkami"><i class="fas fa-user-check"></i> Tentang Kami</a>
                    <a href="./#konsultasi"><i class="fas fa-comment-dots"></i> Konsultasi</a>
                    <a href="./#faq"><i class="fas fa-circle-question"></i> FAQ</a>
                    <a href="./#lokasi"><i class="fas fa-map-marker-alt"></i> Lokasi Studio</a>
                </div>
            </div>

            <!-- Logo NH Tailor dengan Ikon Gunting -->
            <a href="./?halaman=home" class="logo">
                <i class="fas fa-scissors"></i> NH <span>Tailor</span>
            </a>
        </div>

        <!-- Navigasi Kanan (Tombol Masuk dengan Logo User) -->
        <nav id="nav-menu">
            <ul>
                <li>
                    <a href="login/" class="btn-login">
                        <i class="fas fa-right-to-bracket"></i> Masuk
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <?php if ($halaman === 'home'): ?>
        <section class="hero">
            <video muted loop playsinline preload="none" poster="foto/beranda.jpg" class="hero-video" aria-hidden="true">
                <source data-src="foto/video-header.mp4" type="video/mp4">
            </video>
            <div class="hero-content">
                <h1>Elegansi di Setiap Jahitan</h1>
                <p>Mewujudkan busana impian Anda dengan kualitas jahitan premium sejak 2012.</p>
                
                <a href="#konsultasi" class="btn-cta">Konsultasi Sekarang</a>
                
                <div class="hero-socials">
                    <a href="https://www.instagram.com/nh_tailorshop?igsh=ZjNmeGpkb2h6Yzlz" target="_blank" rel="noopener noreferrer" aria-label="Instagram NH Tailor"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                    <a href="https://www.tiktok.com/@nhtailormanado_21" target="_blank" rel="noopener noreferrer" aria-label="TikTok NH Tailor"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
                    <a href="https://wa.me/628135118450" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp NH Tailor"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Dynamic Content -->
        <div class="content-wrapper">
            <?php 
            if ($halaman !== 'home') {
                include "halaman.php"; 
            } 
            ?>
        </div>

        <!-- Section Utama Home -->
        <?php if ($halaman === 'home'): ?>
        <section id="tentangkami" class="about-section">
            <div class="about-container">
                <div class="about-image">
                    <img src="foto/about-tailor.jpg" alt="NH Tailor Studio" loading="lazy" decoding="async">
                </div>
                <div class="about-text">
                    <h2>Dedikasi untuk <span>Kecantikan</span> Wanita</h2>
                    <p>NH Tailor (Rumah Jahit Novi Harefa) spesialis busana wanita kelas atas di Manado. Kami fokus pada detail jahitan tangan (handmade) dan kepuasan pelanggan.</p>
                </div>
            </div>
        </section>

        <section id="layanan" class="services">
            <h2 class="section-title">Layanan Kami</h2>
            <div class="service-grid">
                <div class="service-card">
                    <h3>Wedding Dress</h3>
                    <p>Desain eksklusif untuk hari paling bersejarah Anda.</p>
                    <a class="service-cta" href="#konsultasi" data-service="Wedding Dress">Konsultasikan <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card">
                    <h3>Party Wear</h3>
                    <p>Gaun pesta modis untuk berbagai momen formal.</p>
                    <a class="service-cta" href="#konsultasi" data-service="Party Wear">Konsultasikan <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="service-card">
                    <h3>Modern Kebaya</h3>
                    <p>Potongan elegan yang menonjolkan sisi tradisional modern.</p>
                    <a class="service-cta" href="#konsultasi" data-service="Modern Kebaya">Konsultasikan <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
            <a class="gallery-link" href="./?halaman=galeri">
                <i class="fas fa-images" aria-hidden="true"></i> Lihat Galeri Karya
            </a>
        </section>

        <section id="karya" class="portfolio-section">
            <h2 class="section-title">Karya Pilihan</h2>
            <p class="portfolio-intro">Temukan inspirasi dari koleksi busana yang dikerjakan di studio NH Tailor.</p>
            <div class="portfolio-grid">
                <article class="portfolio-card">
                    <a class="portfolio-image-link" href="./?halaman=galeri" aria-label="Jelajahi galeri gaun pengantin">
                        <img src="foto/photo_6138809740315182959_y.jpg" alt="Gaun pengantin putih dengan detail pita di bagian belakang" loading="lazy" decoding="async">
                        <span>Lihat koleksi <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                    <div class="portfolio-card-body">
                        <p>Wedding</p>
                        <h3>Gaun Pengantin</h3>
                        <a class="service-cta" href="#konsultasi" data-service="Wedding Dress">Konsultasikan model <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
                <article class="portfolio-card">
                    <a class="portfolio-image-link" href="./?halaman=galeri" aria-label="Jelajahi galeri kebaya modern">
                        <img src="foto/photo_6138809740315182964_y.jpg" alt="Kebaya modern berwarna dusty pink dengan detail bordir" loading="lazy" decoding="async">
                        <span>Lihat koleksi <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                    <div class="portfolio-card-body">
                        <p>Tradisional modern</p>
                        <h3>Kebaya Modern</h3>
                        <a class="service-cta" href="#konsultasi" data-service="Modern Kebaya">Konsultasikan model <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
                <article class="portfolio-card">
                    <a class="portfolio-image-link" href="./?halaman=galeri" aria-label="Jelajahi galeri gaun pesta">
                        <img src="foto/photo_6149710612129319690_y.jpg" alt="Koleksi gaun pesta dalam warna pastel dan merah muda" loading="lazy" decoding="async">
                        <span>Lihat koleksi <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                    <div class="portfolio-card-body">
                        <p>Acara spesial</p>
                        <h3>Gaun Pesta</h3>
                        <a class="service-cta" href="#konsultasi" data-service="Party Wear">Konsultasikan model <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
                <article class="portfolio-card">
                    <a class="portfolio-image-link" href="./?halaman=galeri" aria-label="Jelajahi galeri busana batik">
                        <img src="foto/photo_6221750708470484011_y.jpg" alt="Busana pasangan dengan motif batik dan sentuhan cape" loading="lazy" decoding="async">
                        <span>Lihat koleksi <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                    <div class="portfolio-card-body">
                        <p>Sentuhan Nusantara</p>
                        <h3>Busana Batik</h3>
                        <a class="service-cta" href="#konsultasi" data-service="Busana lainnya">Konsultasikan model <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </article>
            </div>
            <a class="gallery-link" href="./?halaman=galeri">
                <i class="fas fa-images" aria-hidden="true"></i> Jelajahi Semua Karya
            </a>
        </section>

        <section id="proses" class="process-section">
            <h2 class="section-title">Cara Memulai Pesanan</h2>
            <p class="process-intro">Mulai dari ide sederhana. Tim NH Tailor akan membantu membahas kebutuhan busana Anda.</p>
            <div class="process-grid">
                <article class="process-step">
                    <span class="process-step-number" aria-hidden="true">01</span>
                    <h3>Ceritakan kebutuhan</h3>
                    <p>Pilih jenis busana, ceritakan acara dan preferensi Anda, lalu sertakan foto inspirasi bila ada.</p>
                </article>
                <article class="process-step">
                    <span class="process-step-number" aria-hidden="true">02</span>
                    <h3>Diskusikan detail</h3>
                    <p>Bahas model, warna, kebutuhan ukuran, serta biaya dan estimasi waktu sesuai busana yang diinginkan.</p>
                </article>
                <article class="process-step">
                    <span class="process-step-number" aria-hidden="true">03</span>
                    <h3>Konfirmasi langkah berikutnya</h3>
                    <p>Setelah detail dibicarakan, konfirmasikan jadwal dan proses selanjutnya langsung dengan NH Tailor.</p>
                </article>
            </div>
            <a class="gallery-link process-cta" href="#konsultasi">
                <i class="fas fa-comment-dots" aria-hidden="true"></i> Mulai Konsultasi
            </a>
        </section>

        <section id="konsultasi" class="consultation-section">
            <div class="consultation-container">
                <h2 class="section-title">Rencanakan Busana Anda</h2>
                <p class="consultation-intro">Ceritakan kebutuhan Anda. Kami akan menyiapkan pesan konsultasi untuk diteruskan langsung ke WhatsApp NH Tailor.</p>
                <form class="consultation-form" id="consultation-form">
                    <div class="consultation-field">
                        <label for="consultation-name">Nama</label>
                        <input id="consultation-name" name="name" type="text" autocomplete="name" maxlength="80" required>
                    </div>
                    <div class="consultation-field">
                        <label for="consultation-service">Jenis busana</label>
                        <select id="consultation-service" name="service" required>
                            <option value="">Pilih jenis busana</option>
                            <option>Wedding Dress</option>
                            <option>Party Wear</option>
                            <option>Modern Kebaya</option>
                            <option>Busana lainnya</option>
                        </select>
                    </div>
                    <div class="consultation-field">
                        <label for="consultation-event-date">Tanggal acara <span>(opsional)</span></label>
                        <input id="consultation-event-date" name="event_date" type="date" min="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="consultation-field consultation-field-full">
                        <label for="consultation-details">Ceritakan kebutuhan Anda <span>(opsional)</span></label>
                        <textarea id="consultation-details" name="details" maxlength="500" placeholder="Misalnya: warna, acara, atau inspirasi desain"></textarea>
                    </div>
                    <div class="consultation-field consultation-field-full">
                        <label for="consultation-images">Gambar referensi <span>(opsional)</span></label>
                        <input id="consultation-images" name="reference_images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="consultation-image-help">Pilih hingga 5 gambar JPG, PNG, atau WebP (maksimal 10 MB per gambar). Gambar tidak disimpan di situs.</p>
                        <p id="consultation-image-status" class="consultation-image-status" role="status" aria-live="polite"></p>
                        <div id="consultation-image-preview" class="consultation-image-preview" aria-label="Pratinjau gambar referensi"></div>
                    </div>
                    <button class="consultation-submit" type="submit">
                        <i class="fab fa-whatsapp" aria-hidden="true"></i> Lanjutkan ke WhatsApp
                    </button>
                    <p class="consultation-note">Informasi ini tidak disimpan di situs. Pada perangkat yang mendukung, pilih WhatsApp dari menu berbagi gambar; jika tidak, lampirkan gambar secara manual setelah WhatsApp terbuka. Tanggal acara hanya membantu konsultasi; ketersediaan jadwal dikonfirmasi langsung melalui WhatsApp.</p>
                </form>
            </div>
        </section>

        <section id="faq" class="faq-section">
            <div class="faq-container">
                <h2 class="section-title">Pertanyaan yang Sering Ditanyakan</h2>
                <div class="faq-list">
                    <details class="faq-item">
                        <summary>Jenis busana apa yang bisa dikonsultasikan?</summary>
                        <p>Anda bisa berkonsultasi tentang wedding dress, party wear, kebaya modern, atau busana lain yang ingin dibuat. Pilih “Busana lainnya” pada formulir konsultasi.</p>
                    </details>
                    <details class="faq-item">
                        <summary>Bagaimana cara memulai konsultasi?</summary>
                        <p>Isi nama dan jenis busana pada formulir, tambahkan kebutuhan jika ada, lalu lanjutkan ke WhatsApp. Pesan akan terbuka agar Anda bisa meninjau dan mengirimkannya.</p>
                    </details>
                    <details class="faq-item">
                        <summary>Bisakah saya menyampaikan warna atau inspirasi desain?</summary>
                        <p>Bisa. Tuliskan warna, acara, atau inspirasi desain pada kolom kebutuhan sebelum melanjutkan ke WhatsApp.</p>
                    </details>
                    <details class="faq-item">
                        <summary>Bagaimana mengetahui biaya dan waktu pengerjaan?</summary>
                        <p>Biaya dan waktu dapat dibahas saat konsultasi setelah kebutuhan busana Anda diketahui. Sertakan detail kebutuhan agar pembicaraan lebih terarah.</p>
                    </details>
                </div>
            </div>
        </section>

        <section id="lokasi" class="maps-section">
            <h2 class="section-title">Lokasi Studio</h2>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.4870535354974!2d124.85698377404414!3d1.4804369611500276!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32877501b439c3e9%3A0xc36761009ef3878b!2sNH%20Tailor%20(Rumah%20Jahit%20Nurhayati)!5e0!3m2!1sid!2sid!4v1715560000000!5m2!1sid!2sid" 
                    title="Peta lokasi NH Tailor di Manado" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            <div style="padding: 15px; background: #fff;">
                <a href="https://maps.app.goo.gl/mGPMnV4FhfkBA8oa7" target="_blank" rel="noopener noreferrer" style="color: var(--primary); text-decoration: none; font-weight: 600;">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i> Buka di Google Maps
                </a>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="footer-logo">
            <i class="fas fa-scissors"></i> NH <span>Tailor</span>
        </div>
        <div class="footer-socials">
            <a href="https://wa.me/628135118450" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp NH Tailor"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
            <a href="https://www.instagram.com/nh_tailorshop?igsh=ZjNmeGpkb2h6Yzlz" target="_blank" rel="noopener noreferrer" aria-label="Instagram NH Tailor"><i class="fab fa-instagram" aria-hidden="true"></i></a>
            <a href="https://www.tiktok.com/@nhtailormanado_21" target="_blank" rel="noopener noreferrer" aria-label="TikTok NH Tailor"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
        </div>
        <div style="font-size: 0.8rem; opacity: 0.6;">
            &copy; <?= date('Y') ?> NH Tailor Shop. All Rights Reserved.<br>
        </div>
    </footer>

    <a class="floating-contact" href="https://wa.me/628135118450?text=Halo%20NH%20Tailor%2C%20saya%20ingin%20bertanya%20tentang%20layanan." target="_blank" rel="noopener noreferrer" aria-label="Hubungi NH Tailor melalui WhatsApp">
        <i class="fab fa-whatsapp" aria-hidden="true"></i><span>Chat WhatsApp</span>
    </a>
    <button class="back-to-top" id="back-to-top" type="button" aria-label="Kembali ke atas" title="Kembali ke atas" hidden>
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>

    <!-- Script Menu Mobile -->
    <script>
        (() => {
        const dropdownToggle = document.getElementById('dropdown-toggle');
        const dropdown = dropdownToggle.closest('.nav-dropdown');
        const heroVideo = document.querySelector('.hero-video');
        const consultationForm = document.getElementById('consultation-form');
        const consultationImageInput = document.getElementById('consultation-images');
        const consultationImagePreview = document.getElementById('consultation-image-preview');
        const consultationImageStatus = document.getElementById('consultation-image-status');
        const backToTop = document.getElementById('back-to-top');
        const canPlayHeroVideo = window.matchMedia('(min-width: 769px)').matches
            && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (heroVideo && canPlayHeroVideo) {
            const videoSource = heroVideo.querySelector('source');
            videoSource.src = videoSource.dataset.src;
            heroVideo.addEventListener('error', () => heroVideo.classList.add('video-unavailable'), { once: true });
            heroVideo.load();
            heroVideo.play().catch(error => {
                heroVideo.classList.add('video-unavailable');
                if (error.name !== 'NotAllowedError') {
                    console.warn('Video hero tidak tersedia; menggunakan gambar latar.');
                }
            });
        }

        if (consultationForm) {
            const maxConsultationImages = 5;
            const maxConsultationImageSize = 10 * 1024 * 1024;
            let consultationImages = [];
            let imagePreviewUrls = [];

            const renderConsultationImages = () => {
                imagePreviewUrls.forEach(url => URL.revokeObjectURL(url));
                imagePreviewUrls = [];
                consultationImagePreview.replaceChildren();

                consultationImages.forEach((file, index) => {
                    const item = document.createElement('div');
                    item.className = 'consultation-image-item';

                    const image = document.createElement('img');
                    imagePreviewUrls.push(URL.createObjectURL(file));
                    image.src = imagePreviewUrls[imagePreviewUrls.length - 1];
                    image.alt = `Pratinjau gambar referensi ${index + 1}`;

                    const fileName = document.createElement('span');
                    fileName.className = 'consultation-image-name';
                    fileName.textContent = file.name;

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'consultation-image-remove';
                    removeButton.setAttribute('aria-label', `Hapus gambar ${file.name}`);
                    const removeIcon = document.createElement('i');
                    removeIcon.className = 'fas fa-times';
                    removeIcon.setAttribute('aria-hidden', 'true');
                    removeButton.append(removeIcon);
                    removeButton.addEventListener('click', () => {
                        consultationImages.splice(index, 1);
                        consultationImageStatus.textContent = consultationImages.length
                            ? `${consultationImages.length} gambar dipilih.`
                            : '';
                        renderConsultationImages();
                    });

                    item.append(image, fileName, removeButton);
                    consultationImagePreview.append(item);
                });
            };

            consultationImageInput.addEventListener('change', () => {
                const selectedImages = Array.from(consultationImageInput.files);
                const validImages = [];
                let validationMessage = '';

                for (const file of selectedImages) {
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                        validationMessage = 'Pilih gambar berformat JPG, PNG, atau WebP.';
                        break;
                    }
                    if (file.size > maxConsultationImageSize) {
                        validationMessage = 'Ukuran setiap gambar maksimal 10 MB.';
                        break;
                    }
                    if (consultationImages.length + validImages.length >= maxConsultationImages) {
                        validationMessage = 'Anda dapat memilih maksimal 5 gambar referensi.';
                        break;
                    }
                    validImages.push(file);
                }

                consultationImages.push(...validImages);
                consultationImageInput.value = '';
                consultationImageStatus.textContent = validationMessage
                    || (consultationImages.length ? `${consultationImages.length} gambar dipilih.` : '');
                renderConsultationImages();
            });

            document.querySelectorAll('.service-cta').forEach(link => {
                link.addEventListener('click', () => {
                    const serviceSelect = consultationForm.elements.namedItem('service');
                    serviceSelect.value = link.dataset.service;
                });
            });

            consultationForm.addEventListener('submit', async event => {
                event.preventDefault();
                if (!consultationForm.reportValidity()) return;

                const formData = new FormData(consultationForm);
                const eventDate = formData.get('event_date').toString();
                const message = [
                    'Halo NH Tailor, saya ingin konsultasi busana.',
                    `Nama: ${formData.get('name').toString().trim()}`,
                    `Jenis busana: ${formData.get('service').toString()}`,
                    eventDate ? `Tanggal acara: ${eventDate}` : '',
                    formData.get('details').toString().trim()
                        ? `Kebutuhan: ${formData.get('details').toString().trim()}`
                        : '',
                    consultationImages.length
                        ? `Saya memiliki ${consultationImages.length} gambar referensi untuk dibagikan.`
                        : ''
                ].filter(Boolean).join('\n');

                const whatsappUrl = `https://wa.me/628135118450?text=${encodeURIComponent(message)}`;
                if (!consultationImages.length) {
                    window.location.href = whatsappUrl;
                    return;
                }

                let canShareReferenceImages = false;
                if (navigator.share && navigator.canShare) {
                    try {
                        canShareReferenceImages = navigator.canShare({ files: consultationImages });
                    } catch (error) {
                        console.error('Dukungan berbagi gambar gagal diperiksa:', error);
                    }
                }

                const openWhatsAppForManualImage = () => {
                    const whatsappLink = document.createElement('a');
                    whatsappLink.href = whatsappUrl;
                    whatsappLink.target = '_blank';
                    whatsappLink.rel = 'noopener noreferrer';
                    document.body.append(whatsappLink);
                    whatsappLink.click();
                    whatsappLink.remove();
                };

                if (canShareReferenceImages) {
                    try {
                        await navigator.share({
                            title: 'Konsultasi NH Tailor',
                            text: message,
                            files: consultationImages
                        });
                        consultationImageStatus.textContent = 'Berbagi selesai. Pastikan pesan dan gambar sudah terkirim melalui WhatsApp.';
                    } catch (error) {
                        if (error.name === 'AbortError') {
                            consultationImageStatus.textContent = 'Berbagi dibatalkan; gambar belum dikirim.';
                            return;
                        }
                        console.error('Gambar referensi gagal dibagikan:', error);
                        consultationImageStatus.textContent = 'Gambar tidak dapat dibagikan otomatis. WhatsApp dibuka agar Anda dapat melampirkannya secara manual.';
                        openWhatsAppForManualImage();
                    }
                    return;
                }

                consultationImageStatus.textContent = 'Browser ini tidak mendukung berbagi gambar otomatis. WhatsApp dibuka; silakan lampirkan gambar yang dipilih secara manual.';
                openWhatsAppForManualImage();
            });
        }

        const updateBackToTop = () => {
            backToTop.hidden = window.scrollY < 500;
        };

        window.addEventListener('scroll', updateBackToTop, { passive: true });
        updateBackToTop();
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'auto' });
        });

        dropdownToggle.addEventListener('click', () => {
            const isOpen = dropdown.classList.toggle('open');
            dropdownToggle.setAttribute('aria-expanded', String(isOpen));
        });

        dropdown.addEventListener('focusout', event => {
            if (!dropdown.contains(event.relatedTarget)) {
                dropdown.classList.remove('open');
                dropdownToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.querySelectorAll('nav a, .dropdown-menu a').forEach(link => {
            link.addEventListener('click', () => {
                dropdown.classList.remove('open');
                dropdownToggle.setAttribute('aria-expanded', 'false');
            });
        });

        document.addEventListener('click', event => {
            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
                dropdownToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && dropdown.classList.contains('open')) {
                dropdown.classList.remove('open');
                dropdownToggle.setAttribute('aria-expanded', 'false');
                dropdownToggle.focus();
            }
        });
        })();
    </script>

</body>
</html>