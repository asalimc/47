<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }
if (!file_exists('uploads')) { mkdir('uploads', 0777, true); }

$tab = $_GET['tab'] ?? 'makale';

function dosyaYukle($file) {
    if (isset($file) && $file['size'] > 0) {
        $ad = time() . '_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $hedef = "uploads/" . $ad;
        if (move_uploaded_file($file['tmp_name'], $hedef)) return $hedef;
    }
    return null;
}

if ($_POST) {
    if (isset($_POST['save_makale'])) {
        $id = $_POST['id'];
        $kapak = $_POST['mevcut_kapak'];
        $yeni_kapak = dosyaYukle($_FILES['kapak_foto']);
        if ($yeni_kapak) $kapak = $yeni_kapak;
        $veriler = [$_POST['kategori'], $_POST['yazar_id'], $_POST['baslik_tr'], $_POST['baslik_pt'], $_POST['icerik_tr'], $_POST['icerik_pt'], $kapak];
        if ($id) { $db->prepare("UPDATE makaleler SET kategori=?, yazar_id=?, baslik_tr=?, baslik_pt=?, icerik_tr=?, icerik_pt=?, kapak_foto=? WHERE id=?")->execute(array_merge($veriler, [$id])); }
        else { $db->prepare("INSERT INTO makaleler (kategori, yazar_id, baslik_tr, baslik_pt, icerik_tr, icerik_pt, kapak_foto, tarih) VALUES (?,?,?,?,?,?,?, NOW())")->execute($veriler); }
    }
    if (isset($_POST['save_yazar'])) {
        $id = $_POST['id'];
        $foto = $_POST['mevcut_foto'];
        $yeni_foto = dosyaYukle($_FILES['foto_dosya']);
        if ($yeni_foto) $foto = $yeni_foto;
        $veriler = [$_POST['isim'], $_POST['rol_tr'], $_POST['rol_pt'], $foto];
        if ($id) { $db->prepare("UPDATE yazarlar SET isim=?, rol_tr=?, rol_pt=?, foto=? WHERE id=?")->execute(array_merge($veriler, [$id])); }
        else { $db->prepare("INSERT INTO yazarlar (isim, rol_tr, rol_pt, foto) VALUES (?,?,?,?)")->execute($veriler); }
    }
    header("Location: admin.php?tab=$tab&durum=ok"); exit;
}

if (isset($_GET['sil_makale'])) { $db->prepare("DELETE FROM makaleler WHERE id=?")->execute([$_GET['sil_makale']]); header("Location: admin.php?tab=makale"); }
if (isset($_GET['sil_yazar'])) { $db->prepare("DELETE FROM yazarlar WHERE id=?")->execute([$_GET['sil_yazar']]); header("Location: admin.php?tab=yazar"); }

$edit = null;
if (isset($_GET['duzenle_makale'])) { $edit = $db->query("SELECT * FROM makaleler WHERE id=".(int)$_GET['duzenle_makale'])->fetch(); }
if (isset($_GET['duzenle_yazar'])) { $edit = $db->query("SELECT * FROM yazarlar WHERE id=".(int)$_GET['duzenle_yazar'])->fetch(); }

$yazarlar = $db->query("SELECT * FROM yazarlar ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$makaleler = $db->query("SELECT * FROM makaleler ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <title>Yönetim Paneli</title>
</head>
<body class="bg-slate-50 p-6 lg:p-12 font-['Plus_Jakarta_Sans']">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-12">
            <h1 class="text-2xl font-black italic uppercase tracking-tighter">Mühürleme Paneli</h1>
            <div class="flex gap-4">
                <a href="?tab=makale" class="px-8 py-3 rounded-2xl font-bold text-xs uppercase <?= $tab=='makale'?'bg-blue-600 text-white shadow-lg':'bg-white text-slate-400' ?>">Makaleler</a>
                <a href="?tab=yazar" class="px-8 py-3 rounded-2xl font-bold text-xs uppercase <?= $tab=='yazar'?'bg-blue-600 text-white shadow-lg':'bg-white text-slate-400' ?>">Yazarlar</a>
                <a href="index.php" class="px-8 py-3 rounded-2xl font-bold text-xs uppercase bg-black text-white">Siteyi Gör</a>
            </div>
        </div>

        <div class="bg-white p-10 rounded-[45px] shadow-sm mb-12 border border-slate-100">
            <?php if($tab == 'makale'): ?>
                <form method="post" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
                    <input type="hidden" name="mevcut_kapak" value="<?= $edit['kapak_foto'] ?? '' ?>">
                    <div class="grid grid-cols-2 gap-6">
                        <select name="kategori" class="w-full p-4 bg-slate-50 rounded-2xl font-bold border-none outline-none ring-2 ring-slate-100 focus:ring-blue-500">
                            <option value="BELLEK DOSYASI" <?= ($edit['kategori']??'')=='BELLEK DOSYASI'?'selected':'' ?>>BELLEK DOSYASI</option>
                            <option value="BAŞYAZAR ÖZEL" <?= ($edit['kategori']??'')=='BAŞYAZAR ÖZEL'?'selected':'' ?>>BAŞYAZI</option>
                        </select>
                        <select name="yazar_id" class="w-full p-4 bg-slate-50 rounded-2xl font-bold border-none outline-none ring-2 ring-slate-100 focus:ring-blue-500">
                            <?php foreach($yazarlar as $y): ?><option value="<?= $y['id'] ?>" <?= ($edit['yazar_id']??'')==$y['id']?'selected':'' ?>><?= $y['isim'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <input type="text" name="baslik_tr" placeholder="Başlık (TR)" value="<?= $edit['baslik_tr'] ?? '' ?>" class="w-full p-4 bg-slate-50 rounded-2xl font-bold">
                        <input type="text" name="baslik_pt" placeholder="Başlık (PT)" value="<?= $edit['baslik_pt'] ?? '' ?>" class="w-full p-4 bg-slate-50 rounded-2xl font-bold">
                    </div>
                    <textarea name="icerik_tr" rows="6" placeholder="İçerik (TR)" class="w-full p-4 bg-slate-50 rounded-2xl border-none"><?= $edit['icerik_tr'] ?? '' ?></textarea>
                    <textarea name="icerik_pt" rows="6" placeholder="İçerik (PT)" class="w-full p-4 bg-slate-50 rounded-2xl border-none"><?= $edit['icerik_pt'] ?? '' ?></textarea>
                    <div class="p-6 border-2 border-dashed rounded-3xl flex justify-between items-center bg-slate-50/50">
                        <span class="text-[10px] font-black uppercase text-slate-400 italic">Kapak Fotoğrafı:</span>
                        <input type="file" name="kapak_foto" class="text-xs">
                    </div>
                    <button name="save_makale" class="w-full bg-blue-600 text-white p-5 rounded-3xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-xl shadow-blue-100">MAKALE MÜHÜRÜNÜ BAS</button>
                </form>
            <?php elseif($tab == 'yazar'): ?>
                <form method="post" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
                    <input type="hidden" name="mevcut_foto" value="<?= $edit['foto'] ?? '' ?>">
                    <div class="flex gap-10">
                        <div class="w-1/3 p-10 bg-slate-50 rounded-[40px] border-2 border-dashed flex flex-col items-center">
                            <?php if(!empty($edit['foto'])): ?><img src="<?= $edit['foto'] ?>" class="w-32 h-32 rounded-full object-cover mb-6 shadow-2xl border-4 border-white"><?php endif; ?>
                            <input type="file" name="foto_dosya" class="text-[10px]">
                        </div>
                        <div class="flex-1 space-y-4">
                            <input type="text" name="isim" placeholder="Ad Soyad" value="<?= $edit['isim'] ?? '' ?>" class="w-full p-4 bg-slate-50 rounded-2xl font-bold">
                            <input type="text" name="rol_tr" placeholder="Rol (TR)" value="<?= $edit['rol_tr'] ?? '' ?>" class="w-full p-4 bg-slate-50 rounded-2xl">
                            <input type="text" name="rol_pt" placeholder="Rol (PT)" value="<?= $edit['rol_pt'] ?? '' ?>" class="w-full p-4 bg-slate-50 rounded-2xl">
                        </div>
                    </div>
                    <button name="save_yazar" class="w-full bg-slate-900 text-white p-5 rounded-3xl font-black uppercase tracking-widest hover:bg-black transition-all">YAZAR PROFİLİNİ MÜHÜRLE</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-[40px] overflow-hidden border border-slate-100 shadow-sm">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b"><tr><th class="p-6 text-[10px] font-black uppercase text-slate-400 tracking-widest">Arşiv Listesi</th><th class="p-6 text-right text-[10px] font-black uppercase text-slate-400">İşlemler</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    <?php $liste = ($tab == 'makale') ? $makaleler : $yazarlar; foreach($liste as $l): ?>
                    <tr class="hover:bg-slate-50/50 transition-all">
                        <td class="p-6 font-bold text-slate-800 text-sm italic"><?= $l['baslik_tr'] ?? $l['isim'] ?></td>
                        <td class="p-6 text-right space-x-4">
                            <a href="?tab=<?= $tab ?>&duzenle_<?= $tab ?>=<?= $l['id'] ?>" class="text-blue-600 font-black text-[10px] uppercase hover:underline">Düzenle</a>
                            <a href="?tab=<?= $tab ?>&sil_<?= $tab ?>=<?= $l['id'] ?>" onclick="return confirm('Silinsin mi?')" class="text-red-500 font-black text-[10px] uppercase hover:underline">Sil</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>