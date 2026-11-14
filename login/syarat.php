<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat & Ketentuan | NH Tailor</title>
    <style>
        body { 
            margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; 
            background: #f4f4f4; background-image: url('foto/images.jpg'); 
            background-size: cover; background-position: center;
            display: flex; justify-content: center; align-items: center; min-height: 100vh; 
        }

        .terms-box { 
            width: 90%; max-width: 600px; padding: 40px; 
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); 
            border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin: 20px;
        }

        h2 { font-family: serif; color: #333; text-align: center; margin-top: 0; }
        
        .content { 
            height: 350px; overflow-y: auto; text-align: left; 
            padding-right: 15px; margin: 20px 0; color: #555; font-size: 14px; line-height: 1.6;
        }

        /* Styling Scrollbar */
        .content::-webkit-scrollbar { width: 6px; }
        .content::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .content::-webkit-scrollbar-thumb { background: #d4a373; border-radius: 10px; }

        h3 { font-size: 16px; color: #333; margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px; }

        .btn { 
            display: block; width: 100%; padding: 12px; text-align: center;
            border: none; background: #d4a373; color: white; border-radius: 8px; 
            cursor: pointer; font-weight: bold; font-size: 14px; text-decoration: none;
            transition: all 0.3s ease; box-sizing: border-box;
        }

        /* EFEK HOVER BIRU */
        .btn:hover {
            background: #2980b9;
            box-shadow: 0 4px 15px rgba(41, 128, 185, 0.3);
            transform: translateY(-2px);
        }

        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #888; }
    </style>
</head>
<body>

    <div class="terms-box">
        <h2>Syarat & Ketentuan</h2>
        
        <div class="content">
            <p>Selamat datang di <b>NH Tailor</b>. Dengan menggunakan layanan kami, Anda setuju untuk mematuhi syarat berikut:</p>

            <h3>1. Pemesanan & Pengukuran</h3>
            <p>Pelanggan wajib memberikan ukuran yang akurat atau melakukan fitting langsung di outlet kami. Kesalahan ukuran akibat data yang diberikan sendiri oleh pelanggan bukan tanggung jawab kami.</p>

            <h3>2. Pembayaran DP</h3>
            <p>Setiap pesanan wajib menyertakan uang muka (DP) minimal sebesar 50% dari total biaya. Pesanan baru akan mulai dikerjakan setelah DP diterima.</p>

            <h3>3. Waktu Pengerjaan</h3>
            <p>Estimasi waktu pengerjaan disesuaikan dengan kerumitan model. Keterlambatan akibat kendala teknis akan kami informasikan segera kepada pelanggan.</p>

            <h3>4. Pengambilan Pesanan</h3>
            <p>Pesanan yang telah selesai harus diambil maksimal 30 hari setelah konfirmasi selesai. NH Tailor tidak bertanggung jawab atas kerusakan atau kehilangan barang yang tidak diambil lebih dari jangka waktu tersebut.</p>

            <h3>5. Pembatalan</h3>
            <p>Pembatalan pesanan yang sudah dalam proses pemotongan kain tidak dapat mengembalikan uang muka (DP).</p>
        </div>

        <a href="register.php" class="btn">Saya Mengerti & Kembali</a>

        <div class="footer">
            © 2026 NH Tailor
        </div>
    </div>

</body>
</html>