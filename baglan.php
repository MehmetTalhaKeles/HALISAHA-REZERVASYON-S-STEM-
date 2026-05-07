<?php
// baglan.php - Veritabanı Bağlantısı
$host = "localhost";
$user = "root";
$pass = "";
$db   = "halisaha_otomasyon";

$baglanti = mysqli_connect($host, $user, $pass, $db);

if (!$baglanti) {
    die("Bağlantı hatası: " . mysqli_connect_error());
}

// Türkçe karakter desteği
mysqli_set_charset($baglanti, "utf8");

session_start();
?>
