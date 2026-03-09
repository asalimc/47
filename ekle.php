<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bellek | İçerik Ekle</title>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-10 rounded-[40px] shadow-2xl w-full max-w-lg border border-yellow-200">
        <h1 class="text-2xl font-black text-slate-900 mb-6 uppercase tracking-tighter">Yeni Bellek Dosyası</h1>
        
        <form action="index.php" method="POST" class="space-y-4">
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Başlık</label>
                <input type="text" name="baslik" required class="w-full border-b-2 border-slate-100 focus:border-yellow-500 outline-none py-2 text-lg font-semibold">
            </div>
            <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">İçerik</label>
                <textarea name="icerik" rows="5" required class="w-full border-2 border-slate-50 rounded-2xl p-4 focus:border-yellow-500 outline-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all">Sisteme Kaydet</button>
        </form>
        <a href="index.php" class="block text-center mt-6 text-xs font-bold text-slate-400 uppercase">← İptal Et ve Dön</a>
    </div>
</body>
</html>