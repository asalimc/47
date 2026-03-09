<?php
include 'db.php';
$lang = (isset($_GET['lang']) && $_GET['lang'] == 'pt') ? 'pt' : 'tr';

// SLIDER SORGUSU: Dil fark etmeksizin Başyazar olmayan tüm güncel içerikleri getirir
$slider_sorgu = $db->query("SELECT * FROM makaleler WHERE kategori != 'BAŞYAZAR ÖZEL' ORDER BY id DESC LIMIT 5");
$slider_data = $slider_sorgu->fetchAll(PDO::FETCH_ASSOC);

// BAŞYAZAR SORGUSU
$basyazar_sorgu = $db->query("SELECT m.*, y.isim as yazar_ad, y.foto as yazar_foto, y.rol_tr, y.rol_pt 
                             FROM makaleler m 
                             LEFT JOIN yazarlar y ON m.yazar_id = y.id 
                             WHERE m.kategori = 'BAŞYAZAR ÖZEL' 
                             ORDER BY m.id DESC LIMIT 1");
$basyazar = $basyazar_sorgu->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahmet ÇINARBAŞ | BELLEK Arşivi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Lora:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bellek-mavi: #1D4ED8; --bellek-lacivert: #0F172A; --bellek-bg: #F8FAFC; --hardal-sari: #EAB308; }
        * { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        body { background-color: var(--bellek-bg); font-family: 'Inter', sans-serif; color: #1e293b; margin: 0; overflow-x: hidden; }
        #bg-glow { position: fixed; inset: 0; pointer-events: none; z-index: -1; background: radial-gradient(circle at var(--x) var(--y), rgba(234, 179, 8, 0.08) 0%, transparent 25%); }
        .main-layout { max-width: 1600px; margin: 0 auto; display: grid; grid-template-columns: 1.6fr 1fr; gap: 2.5rem; padding: 1.5rem; }
        .glow-card { background: #ffffff; border-radius: 45px; border: 1px solid rgba(234, 179, 8, 0.2); overflow: hidden; display: flex; flex-direction: column; }
        .glow-card:hover { transform: translateY(-8px); box-shadow: 0 40px 80px rgba(15, 23, 42, 0.1); }
        .large-news-panel { background: var(--bellek-lacivert) !important; min-height: 580px; position: relative; }
        .news-slider-container { display: flex; height: 100%; transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1); }
        .news-item { min-width: 100%; padding: 4rem; display: flex; align-items: center; gap: 3rem; color: white; }
        .news-thumb { width: 260px; height: 360px; border-radius: 40px; object-fit: cover; transform: rotate(-3deg); box-shadow: 20px 20px 40px rgba(0,0,0,0.4); border: 4px solid rgba(255,255,255,0.1); }
        .action-btn { background: white; color: var(--bellek-lacivert); padding: 18px 35px; border-radius: 22px; font-weight: 800; font-size: 11px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; }
        .action-btn:hover { background: var(--hardal-sari); transform: scale(1.05); }
        footer { background: var(--bellek-lacivert); padding: 5rem 2rem; margin-top: 5rem; border-radius: 70px 70px 0 0; color: white; }
    </style>
</head>
<body>
    <div id="bg-glow"></div>
    <header class="max-w-[1600px] w-full mx-auto px-10 py-8 flex justify-between items-center h-[120px]">
        <div class="text-3xl font-black tracking-tighter uppercase">AHMET ÇINARBAŞ <span class="text-blue-700">BELLEK</span></div>
        <div class="flex items-center gap-4">
            <div class="flex bg-white p-1.5 rounded-2xl border border-yellow-200 shadow-sm">
                <a href="?lang=tr" class="px-5 py-2 rounded-xl text-[11px] font-black <?= $lang=='tr'?'bg-yellow-400 text-slate-900':'text-slate-400' ?>">TR</a>
                <a href="?lang=pt" class="px-5 py-2 rounded-xl text-[11px] font-black <?= $lang=='pt'?'bg-yellow-400 text-slate-900':'text-slate-400' ?>">PT</a>
            </div>
            <a href="admin.php" class="bg-slate-900 text-white p-3 rounded-2xl hover:bg-blue-700 shadow-lg" title="Yönetim Paneli">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
            </a>
        </div>
    </header>

    <div class="main-layout">
        <div class="glow-card large-news-panel">
            <div class="news-slider-container" id="newsSlider">
                <?php foreach($slider_data as $row): 
                    $img = $row['resim'];
                    // Resim yolu tam URL değilse başına klasör ekle (Boşsa varsayılan görsel koy)
                    if (!$img) { $img = "https://via.placeholder.com/400x600?text=BELLEK"; }
                    elseif (!filter_var($img, FILTER_VALIDATE_URL)) { $img = "uploads/" . $img; }
                ?>
                <div class="news-item">
                    <img src="<?= $img ?>" class="news-thumb" alt="News Image">
                    <div class="flex-1">
                        <span class="bg-yellow-500 text-slate-900 px-4 py-1.5 rounded-xl text-[10px] font-black uppercase mb-6 inline-block tracking-widest"><?= $row['kategori'] ?></span>
                        <h1 class="text-4xl md:text-5xl font-black mb-10 leading-tight"><?= $row['baslik_'.$lang] ?></h1>
                        <a href="detay.php?id=<?= $row['id'] ?>&lang=<?= $lang ?>" class="action-btn"><?= $lang == 'tr' ? 'DETAYI GÖR' : 'VER DETALHES' ?> <span>→</span></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex flex-col gap-6">
            <?php if($basyazar): ?>
            <div class="glow-card p-10 bg-yellow-400 items-center text-center">
                <img src="<?= $basyazar['yazar_foto'] ?>" class="w-32 h-32 rounded-[40px] object-cover border-4 border-white mb-4 grayscale mx-auto">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter"><?= $basyazar['yazar_ad'] ?></h2>
                <div class="mt-8 p-8 bg-white rounded-[45px] shadow-2xl w-full">
                    <h3 class="font-bold text-slate-900 text-lg mb-8 leading-snug"><?= $basyazar['baslik_'.$lang] ?></h3>
                    <a href="detay.php?id=<?= $basyazar['id'] ?>&lang=<?= $lang ?>" class="action-btn w-full justify-center bg-slate-900 text-white"><?= $lang == 'tr' ? 'MAKALEYİ OKU' : 'LER ARTIGO' ?></a>
                </div>
            </div>
            <?php endif; ?>
            <div class="glow-card p-10 bg-[#1e1b18] border-none text-[#f5e6d3]">
                <span class="text-[10px] font-black text-yellow-500 tracking-[0.4em] uppercase mb-4 block">SPONSOR</span>
                <h3 class="text-2xl font-black uppercase mb-1">Yaşarbey Kahve</h3>
                <p class="text-[11px] opacity-60 font-medium"><?= $lang == 'tr' ? 'Her yudumda bir mühür.' : 'Um selo em cada gole.' ?></p>
            </div>
        </div>
    </div>

    <footer>
        <div class="max-w-[1600px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-16 items-center">
            <div class="text-2xl font-black italic tracking-tighter underline decoration-yellow-500 underline-offset-8 uppercase">BELLEK.</div>
            <div class="flex flex-col items-center">
                <div class="w-14 h-14 bg-yellow-500 rounded-full flex items-center justify-center text-slate-900 font-black text-2xl mb-4 shadow-2xl shadow-yellow-500/40">M</div>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-[0.5em]"><?= $lang == 'tr' ? 'TAŞIN HAFIZASI' : 'MEMÓRIA DA PEDRA' ?></p>
            </div>
            <p class="text-slate-600 text-[9px] font-bold text-center md:text-right uppercase tracking-widest">© 2026 Mühürlenmiştir.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('mousemove', e => {
            const x = (e.clientX / window.innerWidth) * 100;
            const y = (e.clientY / window.innerHeight) * 100;
            document.documentElement.style.setProperty('--x', x + '%');
            document.documentElement.style.setProperty('--y', y + '%');
        });
        let slideIdx = 0;
        const slides = document.querySelectorAll('.news-item');
        if(slides.length > 1) {
            setInterval(() => {
                slideIdx = (slideIdx + 1) % slides.length;
                document.getElementById('newsSlider').style.transform = `translateX(-${slideIdx * 100}%)`;
            }, 7000);
        }
    </script>
</body>
</html>