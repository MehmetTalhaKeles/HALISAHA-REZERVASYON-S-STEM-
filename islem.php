<?php
// islem.php - Tüm AJAX CRUD İşlemleri
include 'baglan.php';

$islem = $_GET['islem'] ?? '';

// ==================== DASHBOARD ====================
if ($islem == 'dashboard') {
    $r1 = mysqli_query($baglanti, "SELECT COUNT(*) as c FROM sahalar");
    $saha_sayi = mysqli_fetch_assoc($r1)['c'];
    $r2 = mysqli_query($baglanti, "SELECT COUNT(*) as c FROM rezervasyonlar");
    $rez_sayi = mysqli_fetch_assoc($r2)['c'];
    $r3 = mysqli_query($baglanti, "SELECT COUNT(*) as c FROM kullanicilar WHERE rol='musteri'");
    $musteri_sayi = mysqli_fetch_assoc($r3)['c'];
    $r4 = mysqli_query($baglanti, "SELECT COUNT(*) as c FROM personel");
    $personel_sayi = mysqli_fetch_assoc($r4)['c'];
    $r5 = mysqli_query($baglanti, "SELECT COUNT(*) as c FROM odemeler WHERE odeme_durumu='Ödendi'");
    $odeme_sayi = mysqli_fetch_assoc($r5)['c'];
    $r6 = mysqli_query($baglanti, "SELECT COUNT(*) as c FROM kampanyalar WHERE durum='Aktif'");
    $kamp_sayi = mysqli_fetch_assoc($r6)['c'];

    echo "<div class='dash-cards'>
        <div class='dash-card'><div class='sayi'>$saha_sayi</div><div class='baslik'>Saha</div></div>
        <div class='dash-card'><div class='sayi'>$rez_sayi</div><div class='baslik'>Rezervasyon</div></div>
        <div class='dash-card'><div class='sayi'>$musteri_sayi</div><div class='baslik'>Müşteri</div></div>
        <div class='dash-card'><div class='sayi'>$personel_sayi</div><div class='baslik'>Personel</div></div>
        <div class='dash-card'><div class='sayi'>$odeme_sayi</div><div class='baslik'>Ödeme (Ödendi)</div></div>
        <div class='dash-card'><div class='sayi'>$kamp_sayi</div><div class='baslik'>Aktif Kampanya</div></div>
    </div>";
}

// ==================== REZERVASYON ====================
if ($islem == 'rezervasyon_ekle') {
    $k_id = $_SESSION['kullanici_id'];
    $s_id = mysqli_real_escape_string($baglanti, $_POST['saha_id']);
    $tarih = mysqli_real_escape_string($baglanti, $_POST['tarih']);
    $saat = mysqli_real_escape_string($baglanti, $_POST['saat']);
    if (empty($tarih) || empty($saat)) { echo "Tarih ve saat seçin!"; exit; }
    // Çift rezervasyon kontrolü
    $kontrol = mysqli_query($baglanti, "SELECT id FROM rezervasyonlar WHERE saha_id='$s_id' AND rezervasyon_tarihi='$tarih' AND rezervasyon_saati='$saat' AND durum != 'Reddedildi'");
    if (mysqli_num_rows($kontrol) > 0) { echo "Bu saha için seçilen tarih ve saat dolu! Lütfen başka bir saat seçin."; exit; }
    $sorgu = "INSERT INTO rezervasyonlar (kullanici_id, saha_id, rezervasyon_tarihi, rezervasyon_saati, durum) VALUES ('$k_id','$s_id','$tarih','$saat','Beklemede')";
    echo mysqli_query($baglanti, $sorgu) ? "Rezervasyon talebi gönderildi. Onay bekleniyor." : "Hata: ".mysqli_error($baglanti);
}

if ($islem == 'rezervasyon_listele') {
    $k_id = $_SESSION['kullanici_id'];
    $sonuc = mysqli_query($baglanti, "SELECT r.*, s.saha_adi FROM rezervasyonlar r JOIN sahalar s ON r.saha_id=s.id WHERE r.kullanici_id='$k_id' ORDER BY r.id DESC");
    echo "<table class='data-table'><tr><th>Saha</th><th>Tarih</th><th>Saat</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        $durum_renk = $r['durum'] == 'Beklemede' ? 'color:#e67e22;font-weight:bold;' : ($r['durum'] == 'Onaylandı' ? 'color:#27ae60;font-weight:bold;' : 'color:#e74c3c;');
        $sil_btn = $r['durum'] == 'Beklemede' ? "<button onclick='rezervasyonSil({$r['id']})' class='btn-danger-sm'>İptal Et</button>" : "-";
        echo "<tr><td>{$r['saha_adi']}</td><td>{$r['rezervasyon_tarihi']}</td><td>{$r['rezervasyon_saati']}</td><td style='$durum_renk'>{$r['durum']}</td>
        <td>$sil_btn</td></tr>";
    }
    echo "</table>";
}

if ($islem == 'rezervasyon_sil') {
    $id = intval($_GET['id']);
    $k_id = $_SESSION['kullanici_id'];
    $rol = $_SESSION['rol'];
    // Admin tüm rezervasyonları silebilir; müşteri sadece kendi beklemedekini silebilir
    if ($rol == 'admin') {
        $where = "id=$id";
    } else {
        $where = "id=$id AND kullanici_id='$k_id' AND durum='Beklemede'";
    }
    echo mysqli_query($baglanti, "DELETE FROM rezervasyonlar WHERE $where") ? "Silindi." : "Hata veya yetki yok!";
}

if ($islem == 'admin_rezervasyon_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT r.*, s.saha_adi, k.ad_soyad FROM rezervasyonlar r JOIN sahalar s ON r.saha_id=s.id JOIN kullanicilar k ON r.kullanici_id=k.id ORDER BY FIELD(r.durum,'Beklemede','Onaylandı','Reddedildi','İptal'), r.id DESC");
    echo "<table class='data-table'><tr><th>Müşteri</th><th>Saha</th><th>Tarih</th><th>Saat</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        $islem_btn = "<button onclick='rezervasyonSil({$r['id']})' class='btn-danger-sm'>Sil</button>";
        if ($r['durum'] == 'Beklemede') {
            $islem_btn = "<button onclick='rezervasyonOnayla({$r['id']})' class='btn-success' style='padding:4px 10px;font-size:12px;margin-right:5px;'>Onayla</button>"
                       . "<button onclick='rezervasyonReddet({$r['id']})' class='btn-danger-sm'>Reddet</button>";
        }
        $durum_renk = $r['durum'] == 'Beklemede' ? 'color:#e67e22;font-weight:bold;' : ($r['durum'] == 'Onaylandı' ? 'color:#27ae60;font-weight:bold;' : 'color:#e74c3c;');
        echo "<tr><td>{$r['ad_soyad']}</td><td>{$r['saha_adi']}</td><td>{$r['rezervasyon_tarihi']}</td><td>{$r['rezervasyon_saati']}</td><td style='$durum_renk'>{$r['durum']}</td>
        <td>$islem_btn</td></tr>";
    }
    echo "</table>";
}

// Rezervasyon Onayla
if ($islem == 'rezervasyon_onayla') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "UPDATE rezervasyonlar SET durum='Onaylandı' WHERE id=$id") ? "Rezervasyon onaylandı." : "Hata!";
}

// Rezervasyon Reddet
if ($islem == 'rezervasyon_reddet') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "UPDATE rezervasyonlar SET durum='Reddedildi' WHERE id=$id") ? "Rezervasyon reddedildi." : "Hata!";
}

// ==================== SAHA ====================
if ($islem == 'saha_ekle') {
    $adi = mysqli_real_escape_string($baglanti, $_POST['saha_adi']);
    $konum = mysqli_real_escape_string($baglanti, $_POST['konum']);
    $tur = mysqli_real_escape_string($baglanti, $_POST['tur']);
    $kap = intval($_POST['kapasite']);
    $ucret = floatval($_POST['saatlik_ucret']);
    $sorgu = "INSERT INTO sahalar (saha_adi,konum,tur,kapasite,saatlik_ucret) VALUES ('$adi','$konum','$tur',$kap,$ucret)";
    echo mysqli_query($baglanti, $sorgu) ? "Saha eklendi." : "Hata!";
}

if ($islem == 'saha_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM sahalar ORDER BY id DESC");
    echo "<table class='data-table'><tr><th>ID</th><th>Ad</th><th>Konum</th><th>Tür</th><th>Kapasite</th><th>Ücret</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        $rid     = $r['id'];
        $adi_js  = addslashes($r['saha_adi']);
        $konum_js = addslashes($r['konum']);
        $rtur    = addslashes($r['tur']);
        $rkap    = $r['kapasite'];
        $rucret  = $r['saatlik_ucret'];
        echo "<tr>
          <td>{$r['id']}</td><td>{$r['saha_adi']}</td><td>{$r['konum']}</td>
          <td>{$r['tur']}</td><td>{$r['kapasite']}</td><td>{$r['saatlik_ucret']} TL</td><td>{$r['durum']}</td>
          <td>
            <button onclick='sahaDuzenle($rid,\"$adi_js\",\"$konum_js\",\"$rtur\",$rkap,$rucret)' class='btn-edit-sm'>✏️ Düzenle</button>
            <button onclick='sahaSil($rid)' class='btn-danger-sm'>Sil</button>
          </td>
        </tr>";
    }
    echo "</table>";
}

if ($islem == 'saha_guncelle') {
    $id    = intval($_POST['id']);
    $adi   = mysqli_real_escape_string($baglanti, $_POST['saha_adi']);
    $konum = mysqli_real_escape_string($baglanti, $_POST['konum']);
    $tur   = mysqli_real_escape_string($baglanti, $_POST['tur']);
    $kap   = intval($_POST['kapasite']);
    $ucret = floatval($_POST['saatlik_ucret']);
    $sorgu = "UPDATE sahalar SET saha_adi='$adi', konum='$konum', tur='$tur', kapasite=$kap, saatlik_ucret=$ucret WHERE id=$id";
    echo mysqli_query($baglanti, $sorgu) ? "Saha başarıyla güncellendi." : "Hata: ".mysqli_error($baglanti);
}

if ($islem == 'saha_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM sahalar WHERE id=$id") ? "Silindi." : "Hata!";
}

// ==================== ZAMAN DİLİMİ ====================
if ($islem == 'zaman_dilimi_ekle') {
    $sid = intval($_POST['saha_id']);
    $bas = mysqli_real_escape_string($baglanti, $_POST['baslangic_saati']);
    $bit = mysqli_real_escape_string($baglanti, $_POST['bitis_saati']);
    $gun = mysqli_real_escape_string($baglanti, $_POST['gun']);
    $sorgu = "INSERT INTO zaman_dilimleri (saha_id,baslangic_saati,bitis_saati,gun) VALUES ($sid,'$bas','$bit','$gun')";
    echo mysqli_query($baglanti, $sorgu) ? "Zaman dilimi eklendi." : "Hata!";
}

if ($islem == 'zaman_dilimi_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT z.*, s.saha_adi FROM zaman_dilimleri z JOIN sahalar s ON z.saha_id=s.id ORDER BY z.id DESC");
    echo "<table class='data-table'><tr><th>Saha</th><th>Başlangıç</th><th>Bitiş</th><th>Gün</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['saha_adi']}</td><td>{$r['baslangic_saati']}</td><td>{$r['bitis_saati']}</td><td>{$r['gun']}</td>
        <td><button onclick='zamanDilimiSil({$r['id']})' class='btn-danger-sm'>Sil</button></td></tr>";
    }
    echo "</table>";
}

if ($islem == 'zaman_dilimi_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM zaman_dilimleri WHERE id=$id") ? "Silindi." : "Hata!";
}

// ==================== PERSONEL ====================
if ($islem == 'personel_ekle') {
    $ad = mysqli_real_escape_string($baglanti, $_POST['ad_soyad']);
    $poz = mysqli_real_escape_string($baglanti, $_POST['pozisyon']);
    $tel = mysqli_real_escape_string($baglanti, $_POST['telefon']);
    $maas = floatval($_POST['maas']);
    $tarih = mysqli_real_escape_string($baglanti, $_POST['ise_baslama_tarihi']);
    $sorgu = "INSERT INTO personel (ad_soyad,pozisyon,telefon,maas,ise_baslama_tarihi) VALUES ('$ad','$poz','$tel',$maas,'$tarih')";
    echo mysqli_query($baglanti, $sorgu) ? "Personel eklendi." : "Hata!";
}

if ($islem == 'personel_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM personel ORDER BY id DESC");
    echo "<table class='data-table'><tr><th>Ad Soyad</th><th>Pozisyon</th><th>Telefon</th><th>Maaş</th><th>İşe Başlama</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['ad_soyad']}</td><td>{$r['pozisyon']}</td><td>{$r['telefon']}</td><td>{$r['maas']} TL</td><td>{$r['ise_baslama_tarihi']}</td><td>{$r['durum']}</td>
        <td><button onclick='personelSil({$r['id']})' class='btn-danger-sm'>Sil</button></td></tr>";
    }
    echo "</table>";
}

if ($islem == 'personel_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM personel WHERE id=$id") ? "Silindi." : "Hata!";
}

// ==================== PERSONEL ÇALIŞMA ====================
if ($islem == 'personel_calisma_ekle') {
    $pid = intval($_POST['personel_id']);
    $sid = intval($_POST['saha_id']);
    $gun = mysqli_real_escape_string($baglanti, $_POST['calisma_gunu']);
    $bas = mysqli_real_escape_string($baglanti, $_POST['baslangic_saati']);
    $bit = mysqli_real_escape_string($baglanti, $_POST['bitis_saati']);
    $sorgu = "INSERT INTO personel_calisma (personel_id,saha_id,calisma_gunu,baslangic_saati,bitis_saati) VALUES ($pid,$sid,'$gun','$bas','$bit')";
    echo mysqli_query($baglanti, $sorgu) ? "Çalışma kaydı eklendi." : "Hata!";
}

if ($islem == 'personel_calisma_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT pc.*, p.ad_soyad, s.saha_adi FROM personel_calisma pc JOIN personel p ON pc.personel_id=p.id LEFT JOIN sahalar s ON pc.saha_id=s.id ORDER BY pc.id DESC");
    echo "<table class='data-table'><tr><th>Personel</th><th>Saha</th><th>Gün</th><th>Başlangıç</th><th>Bitiş</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['ad_soyad']}</td><td>{$r['saha_adi']}</td><td>{$r['calisma_gunu']}</td><td>{$r['baslangic_saati']}</td><td>{$r['bitis_saati']}</td>
        <td><button onclick='personelCalismaSil({$r['id']})' class='btn-danger-sm'>Sil</button></td></tr>";
    }
    echo "</table>";
}

if ($islem == 'personel_calisma_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM personel_calisma WHERE id=$id") ? "Silindi." : "Hata!";
}

// ==================== HİZMET ====================
if ($islem == 'hizmet_ekle') {
    $adi = mysqli_real_escape_string($baglanti, $_POST['hizmet_adi']);
    $acik = mysqli_real_escape_string($baglanti, $_POST['aciklama']);
    $fiyat = floatval($_POST['fiyat']);
    $sorgu = "INSERT INTO hizmetler (hizmet_adi,aciklama,fiyat) VALUES ('$adi','$acik',$fiyat)";
    echo mysqli_query($baglanti, $sorgu) ? "Hizmet eklendi." : "Hata!";
}

if ($islem == 'hizmet_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM hizmetler ORDER BY id DESC");
    echo "<table class='data-table'><tr><th>Hizmet</th><th>Açıklama</th><th>Fiyat</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['hizmet_adi']}</td><td>{$r['aciklama']}</td><td>{$r['fiyat']} TL</td><td>{$r['durum']}</td>
        <td><button onclick='hizmetSil({$r['id']})' class='btn-danger-sm'>Sil</button></td></tr>";
    }
    echo "</table>";
}

if ($islem == 'hizmet_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM hizmetler WHERE id=$id") ? "Silindi." : "Hata!";
}

if ($islem == 'musteri_hizmet_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM hizmetler WHERE durum='Aktif'");
    echo "<div class='card-grid'>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<div class='info-card'><h4>{$r['hizmet_adi']}</h4><p>{$r['aciklama']}</p><span class='badge'>{$r['fiyat']} TL</span></div>";
    }
    echo "</div>";
}

// ==================== MAÇ ORGANİZASYONU ====================
if ($islem == 'mac_ekle') {
    $baslik = mysqli_real_escape_string($baglanti, $_POST['baslik']);
    $sid = intval($_POST['saha_id']);
    $tarih = mysqli_real_escape_string($baglanti, $_POST['organizasyon_tarihi']);
    $saat = mysqli_real_escape_string($baglanti, $_POST['baslangic_saati']);
    $tur = mysqli_real_escape_string($baglanti, $_POST['tur']);
    $katilimci = intval($_POST['katilimci_sayisi']);
    $acik = mysqli_real_escape_string($baglanti, $_POST['aciklama']);
    $sorgu = "INSERT INTO mac_organizasyonu (baslik,saha_id,organizasyon_tarihi,baslangic_saati,tur,katilimci_sayisi,aciklama) VALUES ('$baslik',$sid,'$tarih','$saat','$tur',$katilimci,'$acik')";
    echo mysqli_query($baglanti, $sorgu) ? "Maç organizasyonu eklendi." : "Hata!";
}

if ($islem == 'mac_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT m.*, s.saha_adi FROM mac_organizasyonu m LEFT JOIN sahalar s ON m.saha_id=s.id ORDER BY m.id DESC");
    echo "<table class='data-table'><tr><th>Başlık</th><th>Saha</th><th>Tarih</th><th>Saat</th><th>Tür</th><th>Katılımcı</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['baslik']}</td><td>{$r['saha_adi']}</td><td>{$r['organizasyon_tarihi']}</td><td>{$r['baslangic_saati']}</td><td>{$r['tur']}</td><td>{$r['katilimci_sayisi']}</td><td>{$r['durum']}</td>
        <td><button onclick='macSil({$r['id']})' class='btn-danger-sm'>Sil</button></td></tr>";
    }
    echo "</table>";
}

if ($islem == 'mac_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM mac_organizasyonu WHERE id=$id") ? "Silindi." : "Hata!";
}

if ($islem == 'musteri_mac_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT m.*, s.saha_adi FROM mac_organizasyonu m LEFT JOIN sahalar s ON m.saha_id=s.id WHERE m.durum='Planlandı' ORDER BY m.organizasyon_tarihi");
    echo "<div class='card-grid'>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<div class='info-card'><h4>{$r['baslik']}</h4><p>Saha: {$r['saha_adi']}</p><p>Tarih: {$r['organizasyon_tarihi']} {$r['baslangic_saati']}</p><p>Tür: {$r['tur']}</p><p>Katılımcı: {$r['katilimci_sayisi']}</p><span class='badge'>{$r['durum']}</span></div>";
    }
    echo "</div>";
}

// ==================== KAMPANYA ====================
if ($islem == 'kampanya_ekle') {
    $adi = mysqli_real_escape_string($baglanti, $_POST['kampanya_adi']);
    $acik = mysqli_real_escape_string($baglanti, $_POST['aciklama']);
    $oran = intval($_POST['indirim_orani']);
    $bas = mysqli_real_escape_string($baglanti, $_POST['baslangic_tarihi']);
    $bit = mysqli_real_escape_string($baglanti, $_POST['bitis_tarihi']);
    $sorgu = "INSERT INTO kampanyalar (kampanya_adi,aciklama,indirim_orani,baslangic_tarihi,bitis_tarihi) VALUES ('$adi','$acik',$oran,'$bas','$bit')";
    echo mysqli_query($baglanti, $sorgu) ? "Kampanya eklendi." : "Hata!";
}

if ($islem == 'kampanya_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM kampanyalar ORDER BY id DESC");
    echo "<table class='data-table'><tr><th>Kampanya</th><th>Açıklama</th><th>İndirim</th><th>Başlangıç</th><th>Bitiş</th><th>Durum</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        $kid    = $r['id'];
        $kadi_js  = addslashes($r['kampanya_adi']);
        $kacik_js = addslashes($r['aciklama']);
        $koran  = $r['indirim_orani'];
        $kbas   = $r['baslangic_tarihi'];
        $kbit   = $r['bitis_tarihi'];
        echo "<tr>
          <td>{$r['kampanya_adi']}</td><td>{$r['aciklama']}</td><td>%{$r['indirim_orani']}</td>
          <td>{$r['baslangic_tarihi']}</td><td>{$r['bitis_tarihi']}</td><td>{$r['durum']}</td>
          <td>
            <button onclick='kampanyaDuzenle($kid,\"$kadi_js\",\"$kacik_js\",$koran,\"$kbas\",\"$kbit\")' class='btn-edit-sm'>✏️ Düzenle</button>
            <button onclick='kampanyaSil($kid)' class='btn-danger-sm'>Sil</button>
          </td>
        </tr>";
    }
    echo "</table>";
}

if ($islem == 'kampanya_guncelle') {
    $id   = intval($_POST['id']);
    $adi  = mysqli_real_escape_string($baglanti, $_POST['kampanya_adi']);
    $acik = mysqli_real_escape_string($baglanti, $_POST['aciklama']);
    $oran = intval($_POST['indirim_orani']);
    $bas  = mysqli_real_escape_string($baglanti, $_POST['baslangic_tarihi']);
    $bit  = mysqli_real_escape_string($baglanti, $_POST['bitis_tarihi']);
    $sorgu = "UPDATE kampanyalar SET kampanya_adi='$adi', aciklama='$acik', indirim_orani=$oran, baslangic_tarihi='$bas', bitis_tarihi='$bit' WHERE id=$id";
    echo mysqli_query($baglanti, $sorgu) ? "Kampanya başarıyla güncellendi." : "Hata!";
}

if ($islem == 'kampanya_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM kampanyalar WHERE id=$id") ? "Silindi." : "Hata!";
}

if ($islem == 'musteri_kampanya_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM kampanyalar WHERE durum='Aktif'");
    echo "<div class='card-grid'>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<div class='info-card'><h4>{$r['kampanya_adi']}</h4><p>{$r['aciklama']}</p><p>{$r['baslangic_tarihi']} - {$r['bitis_tarihi']}</p><span class='badge indirim'>%{$r['indirim_orani']} İndirim</span></div>";
    }
    echo "</div>";
}

// ==================== ÖDEME ====================
if ($islem == 'odeme_ekle') {
    $rid = intval($_POST['rezervasyon_id']);
    $tutar = floatval($_POST['tutar']);
    $yontem = mysqli_real_escape_string($baglanti, $_POST['odeme_yontemi']);
    $durum = mysqli_real_escape_string($baglanti, $_POST['odeme_durumu']);
    $sorgu = "INSERT INTO odemeler (rezervasyon_id,tutar,odeme_yontemi,odeme_durumu) VALUES ($rid,$tutar,'$yontem','$durum')";
    echo mysqli_query($baglanti, $sorgu) ? "Ödeme kaydedildi." : "Hata!";
}

if ($islem == 'odeme_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT o.*, k.ad_soyad, s.saha_adi FROM odemeler o JOIN rezervasyonlar r ON o.rezervasyon_id=r.id JOIN kullanicilar k ON r.kullanici_id=k.id JOIN sahalar s ON r.saha_id=s.id ORDER BY o.id DESC");
    echo "<table class='data-table'><tr><th>Müşteri</th><th>Saha</th><th>Tutar</th><th>Yöntem</th><th>Durum</th><th>Tarih</th><th>İşlem</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['ad_soyad']}</td><td>{$r['saha_adi']}</td><td>{$r['tutar']} TL</td><td>{$r['odeme_yontemi']}</td><td>{$r['odeme_durumu']}</td><td>{$r['odeme_tarihi']}</td>
        <td><button onclick='odemeSil({$r['id']})' class='btn-danger-sm'>Sil</button></td></tr>";
    }
    echo "</table>";
}

if ($islem == 'odeme_sil') {
    $id = intval($_GET['id']);
    echo mysqli_query($baglanti, "DELETE FROM odemeler WHERE id=$id") ? "Silindi." : "Hata!";
}

if ($islem == 'musteri_odeme_listele') {
    $k_id = $_SESSION['kullanici_id'];
    $sonuc = mysqli_query($baglanti, "SELECT o.*, s.saha_adi, r.rezervasyon_tarihi FROM odemeler o JOIN rezervasyonlar r ON o.rezervasyon_id=r.id JOIN sahalar s ON r.saha_id=s.id WHERE r.kullanici_id='$k_id' ORDER BY o.id DESC");
    echo "<table class='data-table'><tr><th>Saha</th><th>Rez. Tarihi</th><th>Tutar</th><th>Yöntem</th><th>Durum</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['saha_adi']}</td><td>{$r['rezervasyon_tarihi']}</td><td>{$r['tutar']} TL</td><td>{$r['odeme_yontemi']}</td><td>{$r['odeme_durumu']}</td></tr>";
    }
    echo "</table>";
}

// ==================== MÜŞTERİ LİSTESİ (Admin) ====================
if ($islem == 'musteri_listele') {
    $sonuc = mysqli_query($baglanti, "SELECT * FROM kullanicilar WHERE rol='musteri' ORDER BY id DESC");
    echo "<table class='data-table'><tr><th>ID</th><th>Ad Soyad</th><th>Email</th><th>Telefon</th><th>Kayıt Tarihi</th></tr>";
    while ($r = mysqli_fetch_assoc($sonuc)) {
        echo "<tr><td>{$r['id']}</td><td>{$r['ad_soyad']}</td><td>{$r['email']}</td><td>{$r['telefon']}</td><td>{$r['kayit_tarihi']}</td></tr>";
    }
    echo "</table>";
}

// ==================== ZAN DİLİMLERİ (AJAX - Dinamik Saat) ====================
if ($islem == 'zaman_dilimleri_getir') {
    $saha_id = intval($_GET['saha_id']);
    $sonuc = mysqli_query($baglanti, "SELECT DISTINCT CONCAT(baslangic_saati,' - ',bitis_saati) AS aralik FROM zaman_dilimleri WHERE saha_id=$saha_id ORDER BY baslangic_saati");
    if (mysqli_num_rows($sonuc) > 0) {
        while ($r = mysqli_fetch_assoc($sonuc)) {
            echo "<option value='{$r['aralik']}'>{$r['aralik']}</option>";
        }
    } else {
        // Kayıt yoksa varsayılan saatleri göster
        $saatler = ['16:00 - 17:00','17:00 - 18:00','18:00 - 19:00','19:00 - 20:00','20:00 - 21:00','21:00 - 22:00'];
        foreach ($saatler as $s) echo "<option value='$s'>$s</option>";
    }
}
?>
