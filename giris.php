<?php
// giris.php
include 'baglan.php';

$mesaj = "";

if (isset($_POST['giris_yap'])) {
    $email = mysqli_real_escape_string($baglanti, $_POST['email']);
    $sifre = hash('sha256', $_POST['sifre']); // SHA256 Şifreleme

    $sorgu = "SELECT * FROM kullanicilar WHERE email='$email' AND sifre='$sifre'";
    $sonuc = mysqli_query($baglanti, $sorgu);

    if (mysqli_num_rows($sonuc) > 0) {
        $kullanici = mysqli_fetch_assoc($sonuc);
        $_SESSION['kullanici_id'] = $kullanici['id'];
        $_SESSION['ad_soyad'] = $kullanici['ad_soyad'];
        $_SESSION['rol'] = $kullanici['rol'];
        header("Location: index.php");
    } else {
        $mesaj = "Hatalı email veya şifre!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap - Halı Saha Sistemi</title>
    <link rel="stylesheet" href="stil.css">
    <script src="script.js"></script>
</head>
<body class="auth-page">
    <div class="form-container">
        <h2>Halı Saha Sistemi Giriş</h2>
        <p style="color: red;"><?php echo $mesaj; ?></p>
        <form action="" method="POST" onsubmit="return formDogrula()">
            <div class="form-group">
                <label>E-posta:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label>Şifre:</label>
                <input type="password" name="sifre" id="sifre" required>
            </div>
            <button type="submit" name="giris_yap" class="btn-primary">Giriş Yap</button>
        </form>
        <p>Hesabınız yok mu? <a href="kayit.php">Kayıt Ol</a></p>
    </div>
</body>
</html>
