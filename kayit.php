<?php
// kayit.php
include 'baglan.php';
$mesaj = "";
if (isset($_POST['kayit_ol'])) {
    $ad_soyad = mysqli_real_escape_string($baglanti, $_POST['ad_soyad']);
    $email = mysqli_real_escape_string($baglanti, $_POST['email']);
    $telefon = mysqli_real_escape_string($baglanti, $_POST['telefon']);
    $sifre = hash('sha256', $_POST['sifre']); // SHA256 Şifreleme
    $kontrol = mysqli_query($baglanti, "SELECT * FROM kullanicilar WHERE email='$email'");
    if (mysqli_num_rows($kontrol) > 0) {
        $mesaj = "Bu email zaten kayıtlı!";
    } else {
        $ekle = "INSERT INTO kullanicilar (ad_soyad, email, telefon, sifre) VALUES ('$ad_soyad', '$email', '$telefon', '$sifre')";
        if (mysqli_query($baglanti, $ekle)) {
            $mesaj = "Kayıt başarılı! Giriş yapabilirsiniz.";
        } else {
            $mesaj = "Kayıt hatası!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol - Halı Saha Sistemi</title>
    <link rel="stylesheet" href="stil.css">
    <script src="script.js"></script>
</head>
<body class="auth-page">
    <div class="form-container">
        <h2>Yeni Kayıt</h2>
        <p style="color:green;"><?php echo $mesaj; ?></p>
        <form action="" method="POST" onsubmit="return formDogrula()">
            <div class="form-group">
                <label>Ad Soyad:</label>
                <input type="text" name="ad_soyad" id="ad_soyad" required>
            </div>
            <div class="form-group">
                <label>E-posta:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label>Telefon:</label>
                <input type="text" name="telefon" placeholder="0555 123 4567">
            </div>
            <div class="form-group">
                <label>Şifre:</label>
                <input type="password" name="sifre" id="sifre" required>
            </div>
            <button type="submit" name="kayit_ol" class="btn-primary">Kayıt Ol</button>
        </form>
        <p>Zaten üye misiniz? <a href="giris.php">Giriş Yap</a></p>
    </div>
</body>
</html>
