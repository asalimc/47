CREATE TABLE IF NOT EXISTS makaleler (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    baslik TEXT NOT NULL,
    slug TEXT NOT NULL,
    ozet TEXT,
    icerik TEXT NOT NULL,
    kapak_resmi TEXT,
    tarih DATETIME DEFAULT CURRENT_TIMESTAMP
);