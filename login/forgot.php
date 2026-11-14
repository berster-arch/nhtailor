<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | NH Tailor</title>
    <style>
        body { 
            margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; 
            background: #f4f4f4; background-image: url('../foto/images.jpg'); 
            background-size: cover; background-position: center;
            display: flex; justify-content: center; align-items: center; height: 100vh; 
        }

        .forgot-box { 
            width: 350px; padding: 40px; background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px); border-radius: 15px; 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3); text-align: center; 
        }

        h2 { font-family: serif; color: #333; margin-bottom: 10px; }
        p { font-size: 13px; color: #666; margin-bottom: 20px; }

        .input-box { margin-bottom: 20px; text-align: left; }
        .input-box label { font-size: 12px; font-weight: bold; color: #333; display: block; margin-bottom: 5px; text-transform: uppercase; }
        .input-box input { 
            width: 100%; padding: 12px; border: 1px solid #ddd; 
            border-radius: 6px; box-sizing: border-box; outline: none;
        }

        /* Tombol Reset */
        .btn { 
            width: 100%; padding: 12px; border: none; 
            background: #d4a373; color: white; border-radius: 6px; 
            cursor: pointer; font-weight: bold; font-size: 14px;
            transition: all 0.3s ease;
        }

        /* Efek Hover Biru */
        .btn:hover {
            background: #2980b9;
            box-shadow: 0 4px 15px rgba(41, 128, 185, 0.3);
        }

        .back-link { margin-top: 20px; font-size: 13px; }
        a { color: #d4a373; text-decoration: none; transition: 0.3s; }
        a:hover { color: #2980b9; }
    </style>
</head>
<body>

    <div class="forgot-box">
        <h2>Pemulihan Akun</h2>
        <p>Reset password otomatis belum tersedia. Silakan hubungi admin NH Tailor untuk bantuan memulihkan akun Anda.</p>
        <div class="back-link">
            <a href="index.php">← Kembali ke Login</a>
        </div>
    </div>

</body>
</html>