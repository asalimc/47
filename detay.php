<?php
include 'db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$lang = (isset($_GET['lang']) && $_GET['lang'] == 'pt') ? 'pt' : 'tr';

$sorgu = $db->prepare("SELECT m.*, y.isim as yazar_ad, y.foto as yazar_foto, y.rol_tr, y.rol_pt FROM makaleler m LEFT JOIN yazarlar y ON m.yazar_id = y.id WHERE m.id = ?");
$sorgu->execute([$id]);
$m = $sorgu->fetch(PDO::FETCH_ASSOC);

if (!$m) { header("Location: index.php"); exit; }

$resim = $m['resim'];
if (!$resim) { $resim = "https://via.placeholder.com/1200x800?text=BELLEK"; }
elseif (!filter_var($resim, FILTER_VALIDATE_URL)) { $resim = "uploads/" . $resim; }
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $m['baslik_'.$lang] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;800&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bellek-lacivert: #0F172A; --hardal-sari: #EAB308; }
        body { background: #FFFFFF; font-family: 'Inter', sans-serif; color: #1a1a1a; margin: 0; }
        .content-text { 
            font-family: 'Lora', serif; font-size: 1.35rem; line-height: 2.1; color: #2d3748;
            text-align: justify; /* İki yana yaslı metin */
            hyphens: auto;
        }
        blockquote {
            font-family: 'Inter', sans-serif; font-weight: 800; font-size: 1.9rem; line-height: 1.3;
            color: var(--bellek-lacivert); border-left: 10px solid var(--hardal-sari);
            padding: 2.5rem; margin: 4rem 0; background: #fdfaf3; border-radius: 0 40px 40px 0; text-align: left;
        }
        #scrollProgress { position: fixed; top: 0; left: 0; width: 0%; height: 6px; background: var(--hardal-sari); z-index: 1000; }
        .article-card { max-width: 950px; margin: -150px auto 100px; background: white; padding: 6rem; border-radius: 80px; box-shadow: 0 60px 120px rgba(0,0,0,0.05); position: relative; z-index: 10; }
        .hero-section { height: 65vh; width: 100%; background: #0f172a; position: relative; overflow: hidden; }
        .hero-image { width: 100%; height: 100%; object-fit: cover; opacity: 0.5; }
    </style>
</head>
<body class="bg-slate-50">
    <div id="scrollProgress"></div>
    <div class="hero-section">
        <img src="<?= $resim ?>" class="hero-image" alt="Hero">
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-10">
            <a href="index.php?lang=<?= $lang ?>" class="text-white/60 font-black text-[10px] tracking-[0.6em] mb-10 hover:text-yellow-400 uppercase">← <?= $lang == 'tr' ? 'GERİ DÖN' : 'VOLTAR' ?></a>
            <span class="bg-yellow-500 text-slate-900 px-6 py-2 rounded-full text-[12px] font-black uppercase mb-6"><?= $m['kategori'] ?></span>
            <h1 class="text-white text-5xl md:text-7xl font-black max-w-5xl tracking-tighter leading-tight"><?= $m['baslik_'.$lang] ?></h1>
        </div>
    </div>
    <main class="px-6 pb-20">
        <article class="article-card">
            <div class="flex items-center gap-8 mb-16 pb-12 border-b border-slate-100">
                <img src="<?= $m['yazar_foto'] ?>" class="w-24 h-24 rounded-[35px] grayscale object-cover">
                <div>
                    <h4 class="font-black text-3xl text-slate-900"><?= $m['yazar_ad'] ?></h4>
                    <p class="text-slate-400 font-bold uppercase tracking-[0.3em] text-xs"><?= $lang=='tr'?$m['rol_tr']:$m['rol_pt'] ?></p>
                </div>
            </div>
            <div class="content-text">
                <?php 
                    $icerik = nl2br($m['icerik_'.$lang]);
                    $icerik = str_replace(['[quote]', '[/quote]'], ['<blockquote>', '</blockquote>'], $icerik);
                    echo $icerik;
                ?>
            </div>
        </article>
    </main>
    <script>
        window.onscroll = () => {
            let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            document.getElementById("scrollProgress").style.width = (winScroll / height) * 100 + "%";
        };
    </script>
</body>
</html>