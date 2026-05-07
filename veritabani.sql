/* 
Halı Saha Rezervasyon Sistemi - Veritabanı Şeması (MySQL)
Bilgisayar Mühendisliği - İnternet Tabanlı Programlama & Veritabanı Ödevi
Entity Sayısı: 10
*/

CREATE DATABASE IF NOT EXISTS halisaha_otomasyon;
USE halisaha_otomasyon;

-- =============================================
-- 1. KULLANICILAR (Müşteri) Tablosu
-- =============================================
CREATE TABLE kullanicilar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefon VARCHAR(20),
    sifre VARCHAR(64) NOT NULL,  -- SHA256 için 64 karakter
    rol ENUM('musteri', 'admin') DEFAULT 'musteri',
    kayit_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 2. SAHALAR Tablosu
-- =============================================
CREATE TABLE sahalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    saha_adi VARCHAR(100) NOT NULL,
    konum VARCHAR(200),
    tur VARCHAR(50) DEFAULT 'Kapalı',       -- Açık / Kapalı
    kapasite INT NOT NULL DEFAULT 14,
    saatlik_ucret DECIMAL(10,2) NOT NULL,
    durum VARCHAR(20) DEFAULT 'Aktif'        -- Aktif / Bakımda
);

-- =============================================
-- 3. ZAMAN DİLİMLERİ Tablosu
-- =============================================
CREATE TABLE zaman_dilimleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    saha_id INT NOT NULL,
    baslangic_saati VARCHAR(10) NOT NULL,    -- Örn: "18:00"
    bitis_saati VARCHAR(10) NOT NULL,        -- Örn: "19:00"
    gun VARCHAR(20) DEFAULT 'Her Gün',       -- Pazartesi, Salı vb.
    musait TINYINT(1) DEFAULT 1,             -- 1=müsait, 0=dolu
    FOREIGN KEY (saha_id) REFERENCES sahalar(id) ON DELETE CASCADE
);

-- =============================================
-- 4. REZERVASYONLAR Tablosu
-- =============================================
CREATE TABLE rezervasyonlar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kullanici_id INT NOT NULL,
    saha_id INT NOT NULL,
    zaman_dilimi_id INT,
    rezervasyon_tarihi DATE NOT NULL,
    rezervasyon_saati VARCHAR(20),
    durum VARCHAR(20) DEFAULT 'Onaylandı',   -- Onaylandı / İptal
    olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (saha_id) REFERENCES sahalar(id) ON DELETE CASCADE,
    FOREIGN KEY (zaman_dilimi_id) REFERENCES zaman_dilimleri(id) ON DELETE SET NULL
);

-- =============================================
-- 5. ÖDEMELER Tablosu
-- =============================================
CREATE TABLE odemeler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rezervasyon_id INT NOT NULL,
    tutar DECIMAL(10,2) NOT NULL,
    odeme_yontemi VARCHAR(50) DEFAULT 'Nakit',  -- Nakit / Kart / Havale
    odeme_durumu VARCHAR(20) DEFAULT 'Bekliyor', -- Bekliyor / Ödendi / İade
    odeme_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rezervasyon_id) REFERENCES rezervasyonlar(id) ON DELETE CASCADE
);

-- =============================================
-- 6. PERSONEL Tablosu
-- =============================================
CREATE TABLE personel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    pozisyon VARCHAR(50) NOT NULL,           -- Saha Görevlisi, Kasiyer vb.
    telefon VARCHAR(20),
    maas DECIMAL(10,2),
    ise_baslama_tarihi DATE,
    durum VARCHAR(20) DEFAULT 'Aktif'        -- Aktif / Pasif
);

-- =============================================
-- 7. PERSONEL ÇALIŞMA TAKVİMİ Tablosu
-- =============================================
CREATE TABLE personel_calisma (
    id INT AUTO_INCREMENT PRIMARY KEY,
    personel_id INT NOT NULL,
    saha_id INT,
    calisma_gunu VARCHAR(20) NOT NULL,       -- Pazartesi, Salı vb.
    baslangic_saati VARCHAR(10) NOT NULL,
    bitis_saati VARCHAR(10) NOT NULL,
    FOREIGN KEY (personel_id) REFERENCES personel(id) ON DELETE CASCADE,
    FOREIGN KEY (saha_id) REFERENCES sahalar(id) ON DELETE SET NULL
);

-- =============================================
-- 8. HİZMETLER Tablosu
-- =============================================
CREATE TABLE hizmetler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hizmet_adi VARCHAR(100) NOT NULL,        -- Forma Kiralama, Top Kiralama vb.
    aciklama TEXT,
    fiyat DECIMAL(10,2) NOT NULL,
    durum VARCHAR(20) DEFAULT 'Aktif'
);

-- =============================================
-- 9. MAÇ ORGANİZASYONU Tablosu
-- =============================================
CREATE TABLE mac_organizasyonu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    baslik VARCHAR(150) NOT NULL,
    saha_id INT,
    organizasyon_tarihi DATE NOT NULL,
    baslangic_saati VARCHAR(10),
    tur VARCHAR(50) DEFAULT 'Dostluk Maçı', -- Dostluk Maçı / Turnuva / Lig
    katilimci_sayisi INT DEFAULT 0,
    aciklama TEXT,
    durum VARCHAR(20) DEFAULT 'Planlandı',   -- Planlandı / Tamamlandı / İptal
    FOREIGN KEY (saha_id) REFERENCES sahalar(id) ON DELETE SET NULL
);

-- =============================================
-- 10. KAMPANYALAR Tablosu
-- =============================================
CREATE TABLE kampanyalar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kampanya_adi VARCHAR(150) NOT NULL,
    aciklama TEXT,
    indirim_orani INT DEFAULT 0,             -- Yüzde olarak
    baslangic_tarihi DATE,
    bitis_tarihi DATE,
    durum VARCHAR(20) DEFAULT 'Aktif'        -- Aktif / Sona Erdi
);


-- =============================================
-- ÖRNEK VERİLER
-- =============================================

-- Admin Kullanıcı (şifre: admin123 -> SHA256)
INSERT INTO kullanicilar (ad_soyad, email, telefon, sifre, rol) VALUES 
('Admin Yönetici', 'admin@halisaha.com', '0555 111 2233', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'admin');

-- Örnek Müşteri (şifre: test123 -> SHA256)
INSERT INTO kullanicilar (ad_soyad, email, telefon, sifre, rol) VALUES 
('Ahmet Yılmaz', 'ahmet@test.com', '0532 444 5566', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae', 'musteri');

-- Sahalar
INSERT INTO sahalar (saha_adi, konum, tur, kapasite, saatlik_ucret, durum) VALUES 
('Merkez Halı Saha', 'Atatürk Cad. No:15', 'Kapalı', 14, 250.00, 'Aktif'),
('Kampüs Halı Saha', 'Üniversite Kampüsü', 'Açık', 12, 180.00, 'Aktif'),
('Yıldız Halı Saha', 'Cumhuriyet Mah. Sok:7', 'Kapalı', 16, 300.00, 'Bakımda');

-- Zaman Dilimleri
INSERT INTO zaman_dilimleri (saha_id, baslangic_saati, bitis_saati, gun) VALUES 
(1, '16:00', '17:00', 'Her Gün'),
(1, '17:00', '18:00', 'Her Gün'),
(1, '18:00', '19:00', 'Her Gün'),
(1, '19:00', '20:00', 'Her Gün'),
(1, '20:00', '21:00', 'Her Gün'),
(1, '21:00', '22:00', 'Her Gün'),
(2, '17:00', '18:00', 'Her Gün'),
(2, '18:00', '19:00', 'Her Gün'),
(2, '19:00', '20:00', 'Her Gün'),
(2, '20:00', '21:00', 'Her Gün');

-- Personel
INSERT INTO personel (ad_soyad, pozisyon, telefon, maas, ise_baslama_tarihi) VALUES 
('Mehmet Kaya', 'Saha Görevlisi', '0544 222 3344', 18000.00, '2024-03-15'),
('Ali Demir', 'Kasiyer', '0533 555 6677', 16000.00, '2024-06-01'),
('Veli Çelik', 'Saha Görevlisi', '0542 888 9900', 18000.00, '2025-01-10');

-- Personel Çalışma
INSERT INTO personel_calisma (personel_id, saha_id, calisma_gunu, baslangic_saati, bitis_saati) VALUES 
(1, 1, 'Pazartesi', '16:00', '22:00'),
(1, 1, 'Çarşamba', '16:00', '22:00'),
(1, 1, 'Cuma', '16:00', '22:00'),
(2, 1, 'Salı', '16:00', '22:00'),
(2, 1, 'Perşembe', '16:00', '22:00'),
(3, 2, 'Pazartesi', '17:00', '21:00'),
(3, 2, 'Salı', '17:00', '21:00');

-- Hizmetler
INSERT INTO hizmetler (hizmet_adi, aciklama, fiyat) VALUES 
('Forma Kiralama', 'Takım forması seti (11 adet)', 100.00),
('Top Kiralama', 'Profesyonel maç topu', 30.00),
('Su Paketi', '12 adet 0.5L su', 50.00),
('Duş Kullanımı', 'Soyunma odası ve duş', 20.00);

-- Maç Organizasyonu
INSERT INTO mac_organizasyonu (baslik, saha_id, organizasyon_tarihi, baslangic_saati, tur, katilimci_sayisi, aciklama) VALUES 
('Bahar Kupası Turnuvası', 1, '2026-06-15', '18:00', 'Turnuva', 32, '4 takım arası eleme usulü turnuva'),
('Haftalık Dostluk Maçı', 2, '2026-05-20', '19:00', 'Dostluk Maçı', 14, 'Her hafta salı günü dostluk maçı');

-- Kampanyalar
INSERT INTO kampanyalar (kampanya_adi, aciklama, indirim_orani, baslangic_tarihi, bitis_tarihi) VALUES 
('Erken Rezervasyon', '3 gün önceden yapılan rezervasyonlara %15 indirim', 15, '2026-05-01', '2026-06-30'),
('Öğrenci İndirimi', 'Öğrenci kartı gösterenlere %20 indirim', 20, '2026-05-01', '2026-12-31'),
('Hafta İçi Fırsatı', 'Pazartesi-Cuma 16:00-18:00 arası %10 indirim', 10, '2026-05-01', '2026-08-31');

-- Örnek Rezervasyon
INSERT INTO rezervasyonlar (kullanici_id, saha_id, zaman_dilimi_id, rezervasyon_tarihi, rezervasyon_saati, durum) VALUES 
(2, 1, 3, '2026-05-10', '18:00 - 19:00', 'Onaylandı');

-- Örnek Ödeme
INSERT INTO odemeler (rezervasyon_id, tutar, odeme_yontemi, odeme_durumu) VALUES 
(1, 250.00, 'Nakit', 'Ödendi');


-- =============================================
-- SQL DML SORGULARI
-- =============================================

-- 1. ALT SORGU (SUBQUERY): En fazla rezervasyon yapılan sahanın bilgilerini getir
SELECT saha_adi, konum, tur, saatlik_ucret
FROM sahalar
WHERE id = (
    SELECT saha_id
    FROM rezervasyonlar
    GROUP BY saha_id
    ORDER BY COUNT(*) DESC
    LIMIT 1
);

-- 2. JOIN: Müşteri, saha ve ödeme bilgilerini birleştirerek listele
SELECT
    k.ad_soyad        AS musteri_adi,
    s.saha_adi        AS saha,
    r.rezervasyon_tarihi,
    r.rezervasyon_saati,
    r.durum           AS rezervasyon_durumu,
    o.tutar           AS odeme_tutari,
    o.odeme_durumu
FROM rezervasyonlar r
JOIN kullanicilar k ON r.kullanici_id = k.id
JOIN sahalar      s ON r.saha_id      = s.id
LEFT JOIN odemeler o ON o.rezervasyon_id = r.id
ORDER BY r.rezervasyon_tarihi DESC;

-- 3. GROUP BY: Her saha için rezervasyon sayısı ve toplam geliri göster
SELECT
    s.saha_adi,
    COUNT(r.id)    AS toplam_rezervasyon,
    SUM(o.tutar)   AS toplam_gelir
FROM sahalar s
LEFT JOIN rezervasyonlar r ON s.id   = r.saha_id
LEFT JOIN odemeler       o ON r.id   = o.rezervasyon_id
GROUP BY s.id, s.saha_adi
ORDER BY toplam_rezervasyon DESC;

-- 4. TARİH FONKSİYONU: Bu ay yapılan rezervasyonları ve bugüne olan gün farkını getir
SELECT
    r.id,
    k.ad_soyad,
    s.saha_adi,
    r.rezervasyon_tarihi,
    DATEDIFF(CURDATE(), r.rezervasyon_tarihi) AS gecen_gun,
    DAYNAME(r.rezervasyon_tarihi)             AS gun_adi
FROM rezervasyonlar r
JOIN kullanicilar k ON r.kullanici_id = k.id
JOIN sahalar      s ON r.saha_id      = s.id
WHERE MONTH(r.rezervasyon_tarihi) = MONTH(CURDATE())
  AND YEAR(r.rezervasyon_tarihi)  = YEAR(CURDATE())
ORDER BY r.rezervasyon_tarihi;

-- 5. KARAKTER FONKSİYONU: Müşterilerin ad/soyad ve e-posta bilgilerini biçimlendir
SELECT
    UPPER(ad_soyad)          AS isim_buyuk_harf,
    LOWER(email)             AS email_kucuk_harf,
    LENGTH(ad_soyad)         AS isim_uzunlugu,
    SUBSTRING(telefon, 1, 4) AS telefon_alan_kodu,
    CONCAT(ad_soyad, ' <', email, '>') AS iletisim_bilgisi
FROM kullanicilar
WHERE rol = 'musteri'
ORDER BY ad_soyad;
