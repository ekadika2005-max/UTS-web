<?php
// koneksi.php - Konfigurasi database SQLite dan fungsi helper CLI
$dbPath = __DIR__ . '/database.sqlite';

try {
    $db = new PDO("sqlite:" . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Aktifkan dukungan Foreign Key di SQLite
    $db->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("\n[FATAL ERROR] Gagal terhubung ke database SQLite: " . $e->getMessage() . "\n");
}

// Fungsi pembaca input terminal yang aman
if (!function_exists('inputCLI')) {
    function inputCLI($prompt) {
        echo $prompt;
        if (defined('STDIN')) {
            $line = fgets(STDIN);
        } else {
            $line = fgets(fopen('php://stdin', 'r'));
        }
        return trim($line);
    }
}

// Format mata uang Rupiah
if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// Cetak garis pemisah
if (!function_exists('garis')) {
    function garis($panjang = 88) {
        echo str_repeat("=", $panjang) . "\n";
    }
}

if (!function_exists('garisTipis')) {
    function garisTipis($panjang = 88) {
        echo str_repeat("-", $panjang) . "\n";
    }
}
?>

