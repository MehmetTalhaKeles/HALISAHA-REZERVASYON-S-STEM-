// script.js - İstemci Tarafı Doğrulamaları ve AJAX İşlemleri

// ==================== FORM DOĞRULAMA (JS Validation) ====================

// Giriş & Kayıt formu doğrulama
function formDogrula() {
    var email = document.getElementById("email").value;
    var sifre = document.getElementById("sifre").value;

    if (email == "") {
        alert("E-posta alanı boş bırakılamaz!");
        return false;
    }

    // Email format kontrolü (Basit regex)
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("Lütfen geçerli bir e-posta adresi girin!");
        return false;
    }

    if (sifre.length < 6) {
        alert("Şifre en az 6 karakter olmalıdır!");
        return false;
    }

}

// Rezervasyon formu doğrulama
function rezervasyonDogrula() {
    var tarih = document.getElementById("tarih");
    var saat = document.getElementById("saat");

    if (tarih && tarih.value == "") {
        alert("Lütfen bir tarih seçin!");
        return false;
    }

    if (saat && saat.value == "") {
        alert("Lütfen bir saat seçin!");
        return false;
    }

    // Geçmiş tarih kontrolü
    if (tarih) {
        var bugun = new Date();
        bugun.setHours(0, 0, 0, 0);
        var secilen = new Date(tarih.value);
        if (secilen < bugun) {
            alert("Geçmiş bir tarih seçemezsiniz!");
            return false;
        }
    }

    return true;
}

// Genel boş alan kontrolü
function alanKontrol(formId) {
    var form = document.getElementById(formId);
    if (!form) return true;
    var inputs = form.querySelectorAll("input[required], select[required]");
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].value.trim() == "") {
            alert(inputs[i].previousElementSibling.textContent + " alanı boş bırakılamaz!");
            inputs[i].focus();
            return false;
        }
    }
    return true;
}


// ==================== SEKME (TAB) DEĞİŞTİRME ====================

function bolumGoster(bolumId, el) {
    // Tüm bölümleri gizle
    var bolumler = document.querySelectorAll(".section-box");
    for (var i = 0; i < bolumler.length; i++) {
        bolumler[i].classList.remove("active");
    }

    // Seçilen bölümü göster
    var hedef = document.getElementById(bolumId);
    if (hedef) {
        hedef.classList.add("active");
    }

    // Menü aktifliği güncelle
    var linkler = document.querySelectorAll(".sidebar a");
    for (var j = 0; j < linkler.length; j++) {
        linkler[j].classList.remove("active");
    }
    if (el) el.classList.add("active");
}


// ==================== AJAX FONKSİYONLARI ====================

// --- Rezervasyon İşlemleri ---
function rezervasyonEkle() {
    if (!rezervasyonDogrula()) return;
    var veri = $("#rezervasyonForm").serialize();
    $.ajax({
        type: "POST",
        url: "islem.php?islem=rezervasyon_ekle",
        data: veri,
        success: function (yanit) {
            alert(yanit);
            rezervasyonListele();
        },
        error: function () {
            alert("Sunucu hatası! Lütfen tekrar deneyin.");
        }
    });
}

function rezervasyonListele() {
    $("#rezervasyonListesi").load("islem.php?islem=rezervasyon_listele");
}

function rezervasyonSil(id) {
    if (confirm("Bu rezervasyonu silmek istediğinize emin misiniz?")) {
        $.get("islem.php?islem=rezervasyon_sil&id=" + id, function (yanit) {
            alert(yanit);
            rezervasyonListele();
            adminRezervasyonListele();
        });
    }
}

function rezervasyonOnayla(id) {
    if (confirm("Bu rezervasyonu onaylamak istiyor musunuz?")) {
        $.get("islem.php?islem=rezervasyon_onayla&id=" + id, function (yanit) {
            alert(yanit);
            adminRezervasyonListele();
        });
    }
}

function rezervasyonReddet(id) {
    if (confirm("Bu rezervasyonu reddetmek istiyor musunuz?")) {
        $.get("islem.php?islem=rezervasyon_reddet&id=" + id, function (yanit) {
            alert(yanit);
            adminRezervasyonListele();
        });
    }
}

// --- Admin Rezervasyon Listesi ---
function adminRezervasyonListele() {
    $("#adminRezervasyonlar").load("islem.php?islem=admin_rezervasyon_listele");
}

// --- Saha İşlemleri ---
function sahaEkle() {
    var veri = $("#sahaForm").serialize();
    $.post("islem.php?islem=saha_ekle", veri, function (yanit) {
        alert(yanit);
        sahaListele();
    });
}

function sahaListele() {
    $("#sahaListesi").load("islem.php?islem=saha_listele");
}

function sahaSil(id) {
    if (confirm("Bu sahayı silmek?")) {
        $.get("islem.php?islem=saha_sil&id=" + id, function (yanit) {
            alert(yanit);
            sahaListele();
        });
    }
}

// --- Saha Düzenleme (UPDATE) ---
function sahaDuzenle(id, adi, konum, tur, kapasite, ucret) {
    document.getElementById('edit_saha_id').value = id;
    document.getElementById('edit_saha_adi').value = adi;
    document.getElementById('edit_konum').value = konum;
    var turSelect = document.getElementById('edit_tur');
    for (var i = 0; i < turSelect.options.length; i++) {
        if (turSelect.options[i].value === tur) { turSelect.selectedIndex = i; break; }
    }
    document.getElementById('edit_kapasite').value = kapasite;
    document.getElementById('edit_saatlik_ucret').value = ucret;
    document.getElementById('sahaEditModal').style.display = 'flex';
}

function sahaGuncelle() {
    var veri = $("#sahaEditForm").serialize();
    $.ajax({
        type: "POST",
        url: "islem.php?islem=saha_guncelle",
        data: veri,
        success: function (yanit) {
            alert(yanit);
            modalKapat('sahaEditModal');
            sahaListele();
        },
        error: function () { alert("Güncelleme hatası!"); }
    });
}

// --- Zaman Dilimi İşlemleri ---
function zamanDilimiEkle() {
    var veri = $("#zamanDilimiForm").serialize();
    $.post("islem.php?islem=zaman_dilimi_ekle", veri, function (yanit) {
        alert(yanit);
        zamanDilimiListele();
    });
}

function zamanDilimiListele() {
    $("#zamanDilimiListesi").load("islem.php?islem=zaman_dilimi_listele");
}

function zamanDilimiSil(id) {
    if (confirm("Bu zaman dilimini silmek?")) {
        $.get("islem.php?islem=zaman_dilimi_sil&id=" + id, function (yanit) {
            alert(yanit);
            zamanDilimiListele();
        });
    }
}

// --- Personel İşlemleri ---
function personelEkle() {
    var veri = $("#personelForm").serialize();
    $.post("islem.php?islem=personel_ekle", veri, function (yanit) {
        alert(yanit);
        personelListele();
    });
}

function personelListele() {
    $("#personelListesi").load("islem.php?islem=personel_listele");
}

function personelSil(id) {
    if (confirm("Bu personeli silmek?")) {
        $.get("islem.php?islem=personel_sil&id=" + id, function (yanit) {
            alert(yanit);
            personelListele();
        });
    }
}

// --- Personel Çalışma İşlemleri ---
function personelCalismaEkle() {
    var veri = $("#personelCalismaForm").serialize();
    $.post("islem.php?islem=personel_calisma_ekle", veri, function (yanit) {
        alert(yanit);
        personelCalismaListele();
    });
}

function personelCalismaListele() {
    $("#personelCalismaListesi").load("islem.php?islem=personel_calisma_listele");
}

function personelCalismaSil(id) {
    if (confirm("Bu kaydı silmek?")) {
        $.get("islem.php?islem=personel_calisma_sil&id=" + id, function (yanit) {
            alert(yanit);
            personelCalismaListele();
        });
    }
}

// --- Hizmet İşlemleri ---
function hizmetEkle() {
    var veri = $("#hizmetForm").serialize();
    $.post("islem.php?islem=hizmet_ekle", veri, function (yanit) {
        alert(yanit);
        hizmetListele();
    });
}

function hizmetListele() {
    $("#hizmetListesi").load("islem.php?islem=hizmet_listele");
}

function hizmetSil(id) {
    if (confirm("Bu hizmeti silmek?")) {
        $.get("islem.php?islem=hizmet_sil&id=" + id, function (yanit) {
            alert(yanit);
            hizmetListele();
        });
    }
}

// --- Müşteri Hizmet Görüntüleme ---
function musteriHizmetListele() {
    $("#musteriHizmetListesi").load("islem.php?islem=musteri_hizmet_listele");
}

// --- Maç Organizasyonu İşlemleri ---
function macEkle() {
    var veri = $("#macForm").serialize();
    $.post("islem.php?islem=mac_ekle", veri, function (yanit) {
        alert(yanit);
        macListele();
    });
}

function macListele() {
    $("#macListesi").load("islem.php?islem=mac_listele");
}

function macSil(id) {
    if (confirm("Bu organizasyonu silmek?")) {
        $.get("islem.php?islem=mac_sil&id=" + id, function (yanit) {
            alert(yanit);
            macListele();
        });
    }
}

// --- Müşteri Maç Görüntüleme ---
function musteriMacListele() {
    $("#musteriMacListesi").load("islem.php?islem=musteri_mac_listele");
}

// --- Kampanya İşlemleri ---
function kampanyaEkle() {
    var veri = $("#kampanyaForm").serialize();
    $.post("islem.php?islem=kampanya_ekle", veri, function (yanit) {
        alert(yanit);
        kampanyaListele();
    });
}

function kampanyaListele() {
    $("#kampanyaListesi").load("islem.php?islem=kampanya_listele");
}

function kampanyaSil(id) {
    if (confirm("Bu kampanyayı silmek?")) {
        $.get("islem.php?islem=kampanya_sil&id=" + id, function (yanit) {
            alert(yanit);
            kampanyaListele();
        });
    }
}

// --- Kampanya Düzenleme (UPDATE) ---
function kampanyaDuzenle(id, adi, acik, oran, bas, bit) {
    document.getElementById('edit_kampanya_id').value = id;
    document.getElementById('edit_kampanya_adi').value = adi;
    document.getElementById('edit_kampanya_aciklama').value = acik;
    document.getElementById('edit_indirim_orani').value = oran;
    document.getElementById('edit_baslangic_tarihi').value = bas;
    document.getElementById('edit_bitis_tarihi').value = bit;
    document.getElementById('kampanyaEditModal').style.display = 'flex';
}

function kampanyaGuncelle() {
    var veri = $("#kampanyaEditForm").serialize();
    $.ajax({
        type: "POST",
        url: "islem.php?islem=kampanya_guncelle",
        data: veri,
        success: function (yanit) {
            alert(yanit);
            modalKapat('kampanyaEditModal');
            kampanyaListele();
        },
        error: function () { alert("Güncelleme hatası!"); }
    });
}

// --- Müşteri Kampanya Görüntüleme ---
function musteriKampanyaListele() {
    $("#musteriKampanyaListesi").load("islem.php?islem=musteri_kampanya_listele");
}

// --- Ödeme İşlemleri ---
function odemeEkle() {
    var veri = $("#odemeForm").serialize();
    $.post("islem.php?islem=odeme_ekle", veri, function (yanit) {
        alert(yanit);
        odemeListele();
    });
}

function odemeListele() {
    $("#odemeListesi").load("islem.php?islem=odeme_listele");
}

function odemeSil(id) {
    if (confirm("Bu ödeme kaydını silmek?")) {
        $.get("islem.php?islem=odeme_sil&id=" + id, function (yanit) {
            alert(yanit);
            odemeListele();
        });
    }
}

// --- Müşteri Ödeme Görüntüleme ---
function musteriOdemeListele() {
    $("#musteriOdemeListesi").load("islem.php?islem=musteri_odeme_listele");
}

// --- Müşteri Listesi (Admin) ---
function musteriListele() {
    $("#musteriListesi").load("islem.php?islem=musteri_listele");
}

// --- Dashboard İstatistikler ---
function dashboardYukle() {
    $("#dashboardIcerik").load("islem.php?islem=dashboard");
}


// --- Modal Kapat ---
function modalKapat(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// --- Dinamik Saat Yükleme (AJAX) ---
function dinamikSaatYukle() {
    var sahaId = document.getElementById('saha_id') ? document.getElementById('saha_id').value : null;
    if (!sahaId) return;
    $.ajax({
        url: 'islem.php?islem=zaman_dilimleri_getir&saha_id=' + sahaId,
        success: function (data) {
            document.getElementById('saat').innerHTML = data;
        },
        error: function () { /* sessizce devam et, mevcut seçenekler kalx */ }
    });
}

console.log("Halı Saha Sistemi JS yüklendi.");
