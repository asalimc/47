<?php
// Bellek Arşivi - Veritabanı Bağlantı Mührü
try {
    // Ekran görüntüsündeki bilgilerle eşleştirildi
    $host = "sql212.infinityfree.com"; // MySQL Ana Bilgisayar Adı
    $dbname = "if0_41348548_asc";      // Oluşturduğun Veritabanı Adı
    $user = "if0_41348548";           // MySQL Kullanıcı Adı
    $pass = "88niHqbPgfq";            // Hesap Şifresi

    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    
    // Hata raporlamayı aktif et (Geliştirme aşamasında yardımcı olur)
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Bağlantı başarısız olursa mühür kırılır ve hata verir
    die("Veritabanı Mührü Açılamadı: " . $e->getMessage());
}
?>
