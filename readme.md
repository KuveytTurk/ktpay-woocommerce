# Kuveyt Türk WooCommerce Ödeme ve Otomatik İptal/İade Modülü

Kuveyt Türk Sanal POS altyapısı (KTPay) ile WooCommerce üzerinde 3D Secure güvenli ödeme tahsilatı ve sipariş yönetim ekranından tek tıkla otomatik İptal, İade ve Kısmi İade işlemlerini gerçekleştiren resmi eklenti geliştirmesidir.

---

## Özellikler

* **3D Secure Güvenli Ödeme:** 3D Secure doğrulaması ile tam entegre kredi/banka kartı tahsilatı.
* **Otomatik Sipariş İptali (CANCEL):** Satışla aynı gün yapılan tam tutar iadelerinde Kuveyt Türk KTPay REST API üzerinden siparişi otomatik olarak iptal eder (Gün sonu kapatılmadan önce yetki iptali).
* **Otomatik Sipariş İadesi (DRAWBACK):** Gün sonu kapanmış siparişlerde WooCommerce sipariş ekranından doğrudan banka iadesi yapar.
* **Kısmi İade Desteği (PARTIAL_DRAWBACK):** Sipariş tutarının bir kısmı iade edilmek istendiğinde tutar bazlı kısmi iade işlemi uygular.
* **Sipariş Meta Bilgileri:** Başarılı ödeme akışında bankadan dönen `OrderId`, `BusinessKey`, `ProvisionNumber`, `RRN` ve `Stan` bilgileri sipariş özel alanlarına eksiksiz kaydedilir.

---

## Yükleme

1. Depodaki `kt-payment-module.zip` dosyasını indirin.
2. WordPress yönetim panelinde **Eklentiler > Yeni Eklenti Ekle > Eklenti Yükle** butonuna tıklayarak zip dosyasını yükleyin ve etkinleştirin.
3. **WooCommerce > Ayarlar > Ödemeler** sekmesine gidin ve **KT Payment Module** satırındaki **Yönet** butonuna tıklayın.
4. Kuveyt Türk tarafından sağlanan:
   * Üye İşyeri Numarası (Merchant ID)
   * Müşteri Numarası (Customer ID)
   * API Kullanıcı Adı (Username)
   * API Şifresi (Password)
   bilgilerini girip kaydedin.
