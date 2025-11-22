<?php
// Hataları gizlemek yerine geliştirme aşamasında açalım ki sorunu görelim
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- SİTE AYARLARI ---
$site = 'http://localhost/kuaforV2/';
$targetFolder = $_SERVER['DOCUMENT_ROOT'] . '/dernek/admin/resimler/';

// --- VERİTABANI AYARLARI ---
$host = "localhost";
$vt_adi = "kuaforV2";
$kullanici_adi = "root";
$sifre = "";
$charset = "utf8mb4";

$dsn = "mysql:host={$host};dbname={$vt_adi};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // DİKKAT: Burada değişken adını $db yerine $conn yaptık
    $conn = new PDO($dsn, $kullanici_adi, $sifre, $options);
} catch (PDOException $exception) {
    die("Bağlantı hatası: " . $exception->getMessage());
}
?>