<?php
session_start();
if(isset($_POST['login'])) {
    if($_POST['user'] == 'admin' && $_POST['pass'] == '123456') {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit;
    } else { $hata = "Yetkisiz Erişim!"; }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Bellek Arşivi | Giriş</title>
</head>
<body class="bg-slate-100 h-screen flex items-center justify-center font-sans">
    <form method="post" class="bg-white p-12 rounded-[50px] shadow-2xl w-[400px] border border-slate-200">
        <h2 class="text-3xl font-black mb-8 text-center tracking-tighter italic">MÜHÜR GİRİŞİ</h2>
        <?php if(isset($hata)): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl text-xs font-bold mb-6 text-center border border-red-100"><?= $hata ?></div>
        <?php endif; ?>
        <input type="text" name="user" placeholder="Kullanıcı Adı" class="w-full p-5 bg-slate-50 rounded-2xl mb-4 outline-none border-2 focus:border-blue-500 transition-all">
        <input type="password" name="pass" placeholder="Şifre" class="w-full p-5 bg-slate-50 rounded-2xl mb-8 outline-none border-2 focus:border-blue-500 transition-all">
        <button name="login" class="w-full bg-black text-white p-5 rounded-2xl font-black uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all">SİSTEMİ AÇ</button>
    </form>
</body>
</html>