import requests
import json
import os
from datetime import datetime
from bs4 import BeautifulSoup

HABER_SITELERI = [
    {"url": "https://www.sozcu.com.tr/", "kategori": "ana_akim"},
    {"url": "https://www.hurriyet.com.tr/", "kategori": "ana_akim"},
    {"url": "https://www.milliyet.com.tr/", "kategori": "ana_akim"},
    {"url": "https://www.sabah.com.tr/", "kategori": "ana_akim"},
    {"url": "https://www.t24.com.tr/", "kategori": "alternatif"},
    {"url": "https://www.gazeteduvar.com.tr/", "kategori": "alternatif"},
    {"url": "https://www.birgun.net/", "kategori": "alternatif"},
    {"url": "https://www.cumhuriyet.com.tr/", "kategori": "alternatif"},
    {"url": "https://www.dunya.com/", "kategori": "ekonomi"},
    {"url": "https://www.bloomberght.com/", "kategori": "ekonomi"},
    {"url": "https://www.bbc.com/turkce", "kategori": "uluslararasi"},
    {"url": "https://tr.euronews.com/", "kategori": "uluslararasi"},
]

KRiZ_KATEGORILERI = {
    "ekonomi": {
        "kelimeler": ["dolar", "enflasyon", "faiz", "kriz", "resesyon", "işsizlik", "iflas", "borç", "kur", "zam", "fiyat artışı"],
        "agirlik": 10
    },
    "siyaset": {
        "kelimeler": ["seçim", "kavga", "gerilim", "protesto", "gösteri", "çatışma", "istifa", "gerginlik"],
        "agirlik": 9
    },
    "deprem_afet": {
        "kelimeler": ["deprem", "artçı", "sel", "yangın", "afet", "hasar", "can kaybı"],
        "agirlik": 8
    },
    "saglik": {
        "kelimeler": ["salgın", "hastalık", "vaka", "ölüm", "virüs", "grip", "salgın"],
        "agirlik": 7
    },
    "gida": {
        "kelimeler": ["gıda", "ekmek", "kıtlık", "açlık", "kuraklık", "rekolte"],
        "agirlik": 8
    },
    "guvenlik": {
        "kelimeler": ["terör", "saldırı", "patlama", "polis", "operasyon", "tutuklama"],
        "agirlik": 9
    },
    "enerji": {
        "kelimeler": ["elektrik", "doğalgaz", "akaryakıt", "benzin", "enerji", "kesinti"],
        "agirlik": 6
    }
}

def get_news():
    tum_haberler = []
    for site in HABER_SITELERI:
        try:
            headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
            response = requests.get(site["url"], headers=headers, timeout=10)
            soup = BeautifulSoup(response.text, 'html.parser')
            
            metinler = []
            for tag in ['title', 'h1', 'h2', 'h3']:
                for element in soup.find_all(tag):
                    metinler.append(element.get_text().lower())
            
            tum_haberler.append({"site": site["url"], "metinler": metinler[:10]})
        except:
            pass
    return tum_haberler

def analyze(haberler):
    skorlar = {}
    for kategori, bilgi in KRiZ_KATEGORILERI.items():
        toplam = 0
        for haber in haberler:
            for metin in haber["metinler"]:
                for kelime in bilgi["kelimeler"]:
                    if kelime in metin:
                        toplam += 1
        skorlar[kategori] = min(100, toplam * bilgi["agirlik"])
    
    toplam_risk = min(100, int(sum(skorlar.values()) / len(skorlar)))
    en_yuksek = max(skorlar, key=skorlar.get)
    
    return skorlar, toplam_risk, en_yuksek

def send_telegram(ozet):
    token = os.environ.get("TELEGRAM_TOKEN")
    chat_id = os.environ.get("TELEGRAM_CHAT_ID")
    if token and chat_id:
        url = f"https://api.telegram.org/bot{token}/sendMessage"
        try:
            requests.post(url, json={"chat_id": chat_id, "text": ozet})
        except:
            pass

def main():
    print("KRİZ GÖZLEM BASLIYOR...")
    haberler = get_news()
    skorlar, toplam_risk, en_yuksek = analyze(haberler)
    
    ozet = f"""
⚠️ KRİZ GÖZLEM | {datetime.now().strftime('%d.%m.%Y %H:%M')}

📊 RİSK SKORU: {toplam_risk}/100

🔴 EN YUKSEK: {en_yuksek.upper()}

📋 KATEGORİLER:
"""
    for kat, skor in skorlar.items():
        ozet += f"• {kat}: {skor}/100\n"
    
    ozet += f"\n🔗 Detay: https://asalimc.github.io"
    
    print(ozet)
    send_telegram(ozet)
    
    with open("son_rapor.json", "w", encoding="utf-8") as f:
        json.dump({
            "tarih": datetime.now().isoformat(),
            "toplam_risk": toplam_risk,
            "kategoriler": skorlar,
            "en_yuksek": en_yuksek
        }, f, ensure_ascii=False, indent=2)

if __name__ == "__main__":
    main()