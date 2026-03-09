<?php
$host = 'localhost'; $user = 'root'; $pass = '';
try {
    $db = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
    $db->exec("CREATE DATABASE IF NOT EXISTS haber_sitesi");
    $db->exec("USE haber_sitesi");
    
    $sql = "CREATE TABLE IF NOT EXISTS makaleler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kategori VARCHAR(50),
        baslik_tr VARCHAR(255),
        baslik_pt VARCHAR(255),
        icerik_tr TEXT,
        icerik_pt TEXT,
        resim VARCHAR(255) DEFAULT 'default.jpg',
        tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "Veritabanı ve tablolar başarıyla hazırlandı! Artık admin paneline girebilirsin.";
} catch (PDOException $e) { die("Hata: " . $e->getMessage()); }
?>