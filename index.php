<?php
// index.php - Ana Panel (Sidebar Layout)
include 'baglan.php';
if (!isset($_SESSION['kullanici_id'])) { header("Location: giris.php"); exit(); }
$id = $_SESSION['kullanici_id'];
$rol = $_SESSION['rol'];

// Saha listesi (form selectleri için)
$sahalar_sorgu = mysqli_query($baglanti, "SELECT * FROM sahalar WHERE durum='Aktif'");
$sahalar_array = [];
while ($s = mysqli_fetch_assoc($sahalar_sorgu)) { $sahalar_array[] = $s; }

// Personel listesi (form selectleri için - admin)
$personel_sorgu = mysqli_query($baglanti, "SELECT * FROM personel");
$personel_array = [];
while ($p = mysqli_fetch_assoc($personel_sorgu)) { $personel_array[] = $p; }

// Rezervasyon listesi (ödeme formu için - admin)
$rez_sorgu = mysqli_query($baglanti, "SELECT r.id, k.ad_soyad, s.saha_adi, r.rezervasyon_tarihi FROM rezervasyonlar r JOIN kullanicilar k ON r.kullanici_id=k.id JOIN sahalar s ON r.saha_id=s.id ORDER BY r.id DESC");
$rez_array = [];
while ($rz = mysqli_fetch_assoc($rez_sorgu)) { $rez_array[] = $rz; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Panel - Halı Saha Sistemi</title>
    <link rel="stylesheet" href="stil.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</head>
<body>
    <!-- NAVBAR -->
    <header class="navbar">
        <h1>⚽ Halı Saha Sistemi</h1>
        <div class="user-info">
            <span>Hoş geldin, <?php echo $_SESSION['ad_soyad']; ?> (<?php echo $rol; ?>)</span>
            <a href="cikis.php" class="btn-danger">Çıkış Yap</a>
        </div>
    </header>

    <div class="main-wrapper">
        <!-- SIDEBAR -->
        <div class="sidebar">
<?php if ($rol == 'musteri'): ?>
            <span class="menu-baslik">Menü</span>
            <a href="#" onclick="bolumGoster('rez_yap', this)" class="active">🏟️ Rezervasyon Yap</a>
            <a href="#" onclick="bolumGoster('rez_listesi', this)">📋 Rezervasyonlarım</a>
            <a href="#" onclick="bolumGoster('odeme_goruntule', this)">💳 Ödemelerim</a>
            <a href="#" onclick="bolumGoster('kampanya_goruntule', this)">🎯 Kampanyalar</a>
            <a href="#" onclick="bolumGoster('hizmet_goruntule', this)">⚽ Hizmetler</a>
            <a href="#" onclick="bolumGoster('mac_goruntule', this)">🏆 Maç Organizasyonu</a>
<?php else: ?>
            <span class="menu-baslik">Yönetim</span>
            <a href="#" onclick="bolumGoster('dashboard', this)" class="active">📊 Özet Panel</a>
            <a href="#" onclick="bolumGoster('admin_sahalar', this)">🏟️ Sahalar</a>
            <a href="#" onclick="bolumGoster('admin_zaman', this)">📅 Zaman Dilimleri</a>
            <a href="#" onclick="bolumGoster('admin_musteriler', this)">👥 Müşteriler</a>
            <a href="#" onclick="bolumGoster('admin_rez', this)">📋 Rezervasyonlar</a>
            <a href="#" onclick="bolumGoster('admin_odeme', this)">💳 Ödemeler</a>
            <span class="menu-baslik">Personel</span>
            <a href="#" onclick="bolumGoster('admin_personel', this)">👷 Personel</a>
            <a href="#" onclick="bolumGoster('admin_calisma', this)">🗓️ Çalışma Takvimi</a>
            <span class="menu-baslik">Diğer</span>
            <a href="#" onclick="bolumGoster('admin_hizmet', this)">⚽ Hizmetler</a>
            <a href="#" onclick="bolumGoster('admin_mac', this)">🏆 Maç Organizasyonu</a>
            <a href="#" onclick="bolumGoster('admin_kampanya', this)">🎯 Kampanyalar</a>
<?php endif; ?>
        </div>

        <!-- İÇERİK -->
        <div class="content">

<?php if ($rol == 'musteri'): ?>
<!-- ============ MÜŞTERİ BÖLÜMLERİ ============ -->

<!-- Rezervasyon Yap -->
<div id="rez_yap" class="section-box active">
    <h2>Rezervasyon Yap</h2>
    <div class="card">
        <h3>Yeni Rezervasyon</h3>
        <form id="rezervasyonForm" class="inline-form">
            <div class="form-group">
                <label>Saha:</label>
                <select name="saha_id" id="saha_id" onchange="dinamikSaatYukle()">
                    <?php foreach($sahalar_array as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo $s['saha_adi']." (".$s['saatlik_ucret']." TL)"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Tarih:</label>
                <input type="date" name="tarih" id="tarih" required>
            </div>
            <div class="form-group">
                <label>Saat:</label>
                <select name="saat" id="saat">
                    <option value="16:00 - 17:00">16:00 - 17:00</option>
                    <option value="17:00 - 18:00">17:00 - 18:00</option>
                    <option value="18:00 - 19:00">18:00 - 19:00</option>
                    <option value="19:00 - 20:00">19:00 - 20:00</option>
                    <option value="20:00 - 21:00">20:00 - 21:00</option>
                    <option value="21:00 - 22:00">21:00 - 22:00</option>
                </select>
            </div>
            <button type="button" onclick="rezervasyonEkle()" class="btn-success">Rezervasyon Oluştur</button>
        </form>
    </div>
</div>

<!-- Rezervasyonlarım -->
<div id="rez_listesi" class="section-box">
    <h2>Rezervasyonlarım</h2>
    <div id="rezervasyonListesi"></div>
</div>

<!-- Ödemelerim -->
<div id="odeme_goruntule" class="section-box">
    <h2>Ödemelerim</h2>
    <div id="musteriOdemeListesi"></div>
</div>

<!-- Kampanyalar -->
<div id="kampanya_goruntule" class="section-box">
    <h2>Aktif Kampanyalar</h2>
    <div id="musteriKampanyaListesi"></div>
</div>

<!-- Hizmetler -->
<div id="hizmet_goruntule" class="section-box">
    <h2>Sunulan Hizmetler</h2>
    <div id="musteriHizmetListesi"></div>
</div>

<!-- Maç Organizasyonu -->
<div id="mac_goruntule" class="section-box">
    <h2>Maç Organizasyonları</h2>
    <div id="musteriMacListesi"></div>
</div>

<?php else: ?>
<!-- ============ ADMİN BÖLÜMLERİ ============ -->

<!-- Dashboard -->
<div id="dashboard" class="section-box active">
    <h2>Özet Panel</h2>
    <div id="dashboardIcerik"></div>
</div>

<!-- Sahalar -->
<div id="admin_sahalar" class="section-box">
    <h2>Saha Yönetimi</h2>
    <div class="card">
        <h3>Yeni Saha Ekle</h3>
        <form id="sahaForm" class="inline-form">
            <div class="form-group"><label>Saha Adı:</label><input type="text" name="saha_adi" required></div>
            <div class="form-group"><label>Konum:</label><input type="text" name="konum"></div>
            <div class="form-group"><label>Tür:</label>
                <select name="tur"><option>Kapalı</option><option>Açık</option></select>
            </div>
            <div class="form-group"><label>Kapasite:</label><input type="number" name="kapasite" value="14"></div>
            <div class="form-group"><label>Saatlik Ücret:</label><input type="number" name="saatlik_ucret" step="0.01"></div>
            <button type="button" onclick="sahaEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="sahaListesi"></div>
</div>

<!-- Zaman Dilimleri -->
<div id="admin_zaman" class="section-box">
    <h2>Zaman Dilimi Yönetimi</h2>
    <div class="card">
        <h3>Yeni Zaman Dilimi</h3>
        <form id="zamanDilimiForm" class="inline-form">
            <div class="form-group"><label>Saha:</label>
                <select name="saha_id">
                    <?php foreach($sahalar_array as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo $s['saha_adi']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Başlangıç:</label><input type="text" name="baslangic_saati" placeholder="18:00"></div>
            <div class="form-group"><label>Bitiş:</label><input type="text" name="bitis_saati" placeholder="19:00"></div>
            <div class="form-group"><label>Gün:</label>
                <select name="gun"><option>Her Gün</option><option>Pazartesi</option><option>Salı</option><option>Çarşamba</option><option>Perşembe</option><option>Cuma</option><option>Cumartesi</option><option>Pazar</option></select>
            </div>
            <button type="button" onclick="zamanDilimiEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="zamanDilimiListesi"></div>
</div>

<!-- Müşteriler -->
<div id="admin_musteriler" class="section-box">
    <h2>Müşteri Listesi</h2>
    <div id="musteriListesi"></div>
</div>

<!-- Rezervasyonlar -->
<div id="admin_rez" class="section-box">
    <h2>Tüm Rezervasyonlar</h2>
    <div id="adminRezervasyonlar"></div>
</div>

<!-- Ödemeler -->
<div id="admin_odeme" class="section-box">
    <h2>Ödeme Yönetimi</h2>
    <div class="card">
        <h3>Yeni Ödeme Kaydı</h3>
        <form id="odemeForm" class="inline-form">
            <div class="form-group"><label>Rezervasyon:</label>
                <select name="rezervasyon_id">
                    <?php foreach($rez_array as $rz): ?>
                    <option value="<?php echo $rz['id']; ?>">#<?php echo $rz['id']." - ".$rz['ad_soyad']." (".$rz['saha_adi'].")"; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Tutar (TL):</label><input type="number" name="tutar" step="0.01"></div>
            <div class="form-group"><label>Yöntem:</label>
                <select name="odeme_yontemi"><option>Nakit</option><option>Kart</option><option>Havale</option></select>
            </div>
            <div class="form-group"><label>Durum:</label>
                <select name="odeme_durumu"><option>Ödendi</option><option>Bekliyor</option><option>İade</option></select>
            </div>
            <button type="button" onclick="odemeEkle()" class="btn-success">Kaydet</button>
        </form>
    </div>
    <div id="odemeListesi"></div>
</div>

<!-- Personel -->
<div id="admin_personel" class="section-box">
    <h2>Personel Yönetimi</h2>
    <div class="card">
        <h3>Yeni Personel</h3>
        <form id="personelForm" class="inline-form">
            <div class="form-group"><label>Ad Soyad:</label><input type="text" name="ad_soyad" required></div>
            <div class="form-group"><label>Pozisyon:</label>
                <select name="pozisyon"><option>Saha Görevlisi</option><option>Kasiyer</option><option>Temizlik</option><option>Yönetici</option></select>
            </div>
            <div class="form-group"><label>Telefon:</label><input type="text" name="telefon"></div>
            <div class="form-group"><label>Maaş:</label><input type="number" name="maas" step="0.01"></div>
            <div class="form-group"><label>İşe Başlama:</label><input type="date" name="ise_baslama_tarihi"></div>
            <button type="button" onclick="personelEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="personelListesi"></div>
</div>

<!-- Personel Çalışma -->
<div id="admin_calisma" class="section-box">
    <h2>Personel Çalışma Takvimi</h2>
    <div class="card">
        <h3>Çalışma Kaydı Ekle</h3>
        <form id="personelCalismaForm" class="inline-form">
            <div class="form-group"><label>Personel:</label>
                <select name="personel_id">
                    <?php foreach($personel_array as $p): ?>
                    <option value="<?php echo $p['id']; ?>"><?php echo $p['ad_soyad']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Saha:</label>
                <select name="saha_id">
                    <?php foreach($sahalar_array as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo $s['saha_adi']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Gün:</label>
                <select name="calisma_gunu"><option>Pazartesi</option><option>Salı</option><option>Çarşamba</option><option>Perşembe</option><option>Cuma</option><option>Cumartesi</option><option>Pazar</option></select>
            </div>
            <div class="form-group"><label>Başlangıç:</label><input type="text" name="baslangic_saati" placeholder="16:00"></div>
            <div class="form-group"><label>Bitiş:</label><input type="text" name="bitis_saati" placeholder="22:00"></div>
            <button type="button" onclick="personelCalismaEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="personelCalismaListesi"></div>
</div>

<!-- Hizmetler -->
<div id="admin_hizmet" class="section-box">
    <h2>Hizmet Yönetimi</h2>
    <div class="card">
        <h3>Yeni Hizmet</h3>
        <form id="hizmetForm" class="inline-form">
            <div class="form-group"><label>Hizmet Adı:</label><input type="text" name="hizmet_adi" required></div>
            <div class="form-group"><label>Açıklama:</label><input type="text" name="aciklama"></div>
            <div class="form-group"><label>Fiyat:</label><input type="number" name="fiyat" step="0.01"></div>
            <button type="button" onclick="hizmetEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="hizmetListesi"></div>
</div>

<!-- Maç Organizasyonu -->
<div id="admin_mac" class="section-box">
    <h2>Maç Organizasyonu</h2>
    <div class="card">
        <h3>Yeni Organizasyon</h3>
        <form id="macForm" class="inline-form">
            <div class="form-group"><label>Başlık:</label><input type="text" name="baslik" required></div>
            <div class="form-group"><label>Saha:</label>
                <select name="saha_id">
                    <?php foreach($sahalar_array as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo $s['saha_adi']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Tarih:</label><input type="date" name="organizasyon_tarihi"></div>
            <div class="form-group"><label>Saat:</label><input type="text" name="baslangic_saati" placeholder="18:00"></div>
            <div class="form-group"><label>Tür:</label>
                <select name="tur"><option>Dostluk Maçı</option><option>Turnuva</option><option>Lig</option></select>
            </div>
            <div class="form-group"><label>Katılımcı:</label><input type="number" name="katilimci_sayisi" value="14"></div>
            <div class="form-group"><label>Açıklama:</label><input type="text" name="aciklama"></div>
            <button type="button" onclick="macEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="macListesi"></div>
</div>

<!-- Kampanyalar -->
<div id="admin_kampanya" class="section-box">
    <h2>Kampanya Yönetimi</h2>
    <div class="card">
        <h3>Yeni Kampanya</h3>
        <form id="kampanyaForm" class="inline-form">
            <div class="form-group"><label>Kampanya Adı:</label><input type="text" name="kampanya_adi" required></div>
            <div class="form-group"><label>Açıklama:</label><input type="text" name="aciklama"></div>
            <div class="form-group"><label>İndirim %:</label><input type="number" name="indirim_orani" value="10"></div>
            <div class="form-group"><label>Başlangıç:</label><input type="date" name="baslangic_tarihi"></div>
            <div class="form-group"><label>Bitiş:</label><input type="date" name="bitis_tarihi"></div>
            <button type="button" onclick="kampanyaEkle()" class="btn-success">Ekle</button>
        </form>
    </div>
    <div id="kampanyaListesi"></div>
</div>

<?php endif; ?>

        </div><!-- content -->
    </div><!-- main-wrapper -->

    <footer class="footer">
        <p>İnternet Tabanlı Programlama & Veritabanı Proje Ödevi &copy; 2026</p>
    </footer>

    <script>
    // Sayfa yüklenince verileri çek
    $(document).ready(function() {
        <?php if ($rol == 'musteri'): ?>
        rezervasyonListele();
        musteriOdemeListele();
        musteriKampanyaListele();
        musteriHizmetListele();
        musteriMacListele();
        <?php else: ?>
        dashboardYukle();
        sahaListele();
        zamanDilimiListele();
        musteriListele();
        adminRezervasyonListele();
        odemeListele();
        personelListele();
        personelCalismaListele();
        hizmetListele();
        macListele();
        kampanyaListele();
        <?php endif; ?>
    });
    </script>

<?php if ($rol == 'admin'): ?>
<!-- ==================== SAHA DÜZENLEME MODALI ==================== -->
<div id="sahaEditModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>✏️ Saha Düzenle</h3>
        <form id="sahaEditForm" class="inline-form">
            <input type="hidden" name="id" id="edit_saha_id">
            <div class="form-group"><label>Saha Adı:</label><input type="text" name="saha_adi" id="edit_saha_adi" required></div>
            <div class="form-group"><label>Konum:</label><input type="text" name="konum" id="edit_konum"></div>
            <div class="form-group"><label>Tür:</label>
                <select name="tur" id="edit_tur"><option value="Kapalı">Kapalı</option><option value="Açık">Açık</option></select>
            </div>
            <div class="form-group"><label>Kapasite:</label><input type="number" name="kapasite" id="edit_kapasite"></div>
            <div class="form-group"><label>Saatlik Ücret (TL):</label><input type="number" name="saatlik_ucret" id="edit_saatlik_ucret" step="0.01"></div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="button" onclick="sahaGuncelle()" class="btn-success">✅ Güncelle</button>
                <button type="button" onclick="modalKapat('sahaEditModal')" class="btn-danger">❌ İptal</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== KAMPANYA DÜZENLEME MODALI ==================== -->
<div id="kampanyaEditModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>✏️ Kampanya Düzenle</h3>
        <form id="kampanyaEditForm" class="inline-form">
            <input type="hidden" name="id" id="edit_kampanya_id">
            <div class="form-group"><label>Kampanya Adı:</label><input type="text" name="kampanya_adi" id="edit_kampanya_adi" required></div>
            <div class="form-group"><label>Açıklama:</label><input type="text" name="aciklama" id="edit_kampanya_aciklama"></div>
            <div class="form-group"><label>İndirim %:</label><input type="number" name="indirim_orani" id="edit_indirim_orani" min="0" max="100"></div>
            <div class="form-group"><label>Başlangıç:</label><input type="date" name="baslangic_tarihi" id="edit_baslangic_tarihi"></div>
            <div class="form-group"><label>Bitiş:</label><input type="date" name="bitis_tarihi" id="edit_bitis_tarihi"></div>
            <div style="display:flex;gap:10px;margin-top:10px;">
                <button type="button" onclick="kampanyaGuncelle()" class="btn-success">✅ Güncelle</button>
                <button type="button" onclick="modalKapat('kampanyaEditModal')" class="btn-danger">❌ İptal</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

</body>
</html>
