
<?php

require_once __DIR__ . '/../config/config.php';

/**
 * Mengirim pesan WhatsApp melalui Fonnte.
 * Fungsi mengembalikan status dan respons API.
 */
function kirimWhatsApp(string $nomor, string $pesan): array
{
    if (FONNTE_TOKEN === '') {
        return [
            'success' => false,
            'message' => 'Token Fonnte belum diatur.'
        ];
    }

    // Normalisasi nomor Indonesia menjadi format 62.
    $nomor = preg_replace('/[\s\-()+]/', '', $nomor);

    if (str_starts_with($nomor, '0')) {
        $nomor = '62' . substr($nomor, 1);
    } elseif (str_starts_with($nomor, '+62')) {
        $nomor = substr($nomor, 1);
    }

    if (!preg_match('/^62[0-9]{8,13}$/', $nomor)) {
        return [
            'success' => false,
            'message' => 'Format nomor WhatsApp tidak valid.'
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'message' => 'Ekstensi PHP cURL belum aktif.'
        ];
    }

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'target' => $nomor,
            'message' => $pesan
        ],
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . FONNTE_TOKEN
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($response === false) {
        error_log('Fonnte cURL: ' . $error);

        return [
            'success' => false,
            'message' => 'Gagal menghubungi layanan WhatsApp.'
        ];
    }

    $result = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300 ||
        !is_array($result) || !($result['status'] ?? false)) {
        error_log('Fonnte HTTP ' . $httpCode . ': ' . $response);

        return [
            'success' => false,
            'message' => 'Pengiriman WhatsApp gagal. Periksa konfigurasi Fonnte.'
        ];
    }

    return [
        'success' => true,
        'message' => 'Permintaan pengiriman WhatsApp berhasil.',
        'response' => $result
    ];
}
