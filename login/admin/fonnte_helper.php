<?php
include_once __DIR__ . '/fonnte_config.php';

if (!function_exists('fonnte_normalize_phone')) {
    function fonnte_normalize_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', (string) $phone);
        if ($phone === '') {
            return '';
        }

        if (substr($phone, 0, 1) === '0') {
            return FONNTE_COUNTRY_CODE . substr($phone, 1);
        }

        return $phone;
    }
}

if (!function_exists('fonnte_rupiah')) {
    function fonnte_rupiah($angka) {
        return 'Rp ' . number_format((int) preg_replace('/[^0-9]/', '', (string) $angka), 0, ',', '.');
    }
}

if (!function_exists('fonnte_tanggal')) {
    function fonnte_tanggal($tanggal) {
        if (empty($tanggal) || $tanggal == '0000-00-00') {
            return '-';
        }

        $waktu = strtotime($tanggal);
        return $waktu ? date('d M Y', $waktu) : '-';
    }
}

if (!function_exists('fonnte_send_message')) {
    function fonnte_send_message($target, $message) {
        $token = trim((string) FONNTE_TOKEN);
        $target = fonnte_normalize_phone($target);
        $message = trim((string) $message);

        if ($token === '') {
            return ['success' => false, 'skipped' => true, 'message' => 'Token Fonnte belum diisi.'];
        }

        if ($target === '' || $message === '') {
            return ['success' => false, 'skipped' => true, 'message' => 'Nomor tujuan atau pesan kosong.'];
        }

        if (!function_exists('curl_init')) {
            return ['success' => false, 'skipped' => true, 'message' => 'Ekstensi cURL PHP belum aktif.'];
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
                'countryCode' => FONNTE_COUNTRY_CODE,
                'delay' => '1',
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $token,
            ],
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($curl_error) {
            return ['success' => false, 'skipped' => false, 'message' => $curl_error];
        }

        $decoded = json_decode((string) $response, true);
        if (is_array($decoded) && !empty($decoded['status'])) {
            return ['success' => true, 'skipped' => false, 'message' => $decoded['detail'] ?? 'Pesan masuk antrean Fonnte.'];
        }

        return [
            'success' => false,
            'skipped' => false,
            'message' => is_array($decoded) ? ($decoded['reason'] ?? $decoded['detail'] ?? 'Fonnte gagal mengirim pesan.') : 'HTTP ' . $http_code . ': ' . $response,
        ];
    }
}

if (!function_exists('fonnte_notify_order_created')) {
    function fonnte_notify_order_created($pesanan) {
        $total = (int) preg_replace('/[^0-9]/', '', (string) ($pesanan['total_biaya'] ?? 0));
        $dp = (int) preg_replace('/[^0-9]/', '', (string) ($pesanan['dp'] ?? 0));
        $sisa = max($total - $dp, 0);

        $message = "Halo " . ($pesanan['nama_pelanggan'] ?? 'Customer') . ",\n\n"
            . "Pesanan Anda di NH Tailor berhasil dicatat.\n"
            . "Jenis: " . ($pesanan['jenis_pakaian'] ?? '-') . "\n"
            . "Total: " . fonnte_rupiah($total) . "\n"
            . "DP: " . fonnte_rupiah($dp) . "\n"
            . "Sisa: " . fonnte_rupiah($sisa) . "\n"
            . "Target selesai: " . fonnte_tanggal($pesanan['tgl_selesai'] ?? '') . "\n\n"
            . "Terima kasih.";

        return fonnte_send_message($pesanan['no_hp'] ?? '', $message);
    }
}

if (!function_exists('fonnte_notify_status_updated')) {
    function fonnte_notify_status_updated($pesanan, $status) {
        $message = "Halo " . ($pesanan['nama_pelanggan'] ?? 'Customer') . ",\n\n"
            . "Progres pesanan " . ($pesanan['jenis_pakaian'] ?? 'Anda') . " sudah diperbarui.\n"
            . "Status terbaru: " . ($status ?: 'Menunggu Antrian') . "\n"
            . "Target selesai: " . fonnte_tanggal($pesanan['tgl_selesai'] ?? '') . "\n\n"
            . "Terima kasih.";

        return fonnte_send_message($pesanan['no_hp'] ?? '', $message);
    }
}

if (!function_exists('fonnte_notify_payment_updated')) {
    function fonnte_notify_payment_updated($pesanan, $judul = 'Pembayaran pesanan diperbarui') {
        $total = (int) preg_replace('/[^0-9]/', '', (string) ($pesanan['total_biaya'] ?? 0));
        $dp = (int) preg_replace('/[^0-9]/', '', (string) ($pesanan['dp'] ?? 0));
        $sisa = max($total - $dp, 0);
        $status = $sisa <= 0 ? 'Lunas' : 'Belum Lunas';

        $message = "Halo " . ($pesanan['nama_pelanggan'] ?? 'Customer') . ",\n\n"
            . $judul . ".\n"
            . "Jenis: " . ($pesanan['jenis_pakaian'] ?? '-') . "\n"
            . "Total: " . fonnte_rupiah($total) . "\n"
            . "Terbayar: " . fonnte_rupiah($dp) . "\n"
            . "Sisa: " . fonnte_rupiah($sisa) . "\n"
            . "Status: " . $status . "\n\n"
            . "Terima kasih.";

        return fonnte_send_message($pesanan['no_hp'] ?? '', $message);
    }
}
