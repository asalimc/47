<?php
try {
    $db = new PDO("mysql:host=localhost;dbname=haber_sitesi;charset=utf8", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}

function koruma() {
    // Admin yetki kontrolü buraya gelecek
}
?>