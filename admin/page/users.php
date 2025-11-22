<?php
require_once __DIR__ . '/header.php';

$msg = "";
$error = "";

// 1. Kullanıcı Ekle
if (isset($_POST['add_user'])) {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        // Rol tipini de gönderiyoruz
        $res = addUser($_POST['username'], $_POST['password'], $_POST['role_type']);
        if ($res['status']) $msg = $res['message']; else $error = $res['message'];
    }
}

// 2. Kullanıcı Sil
if (isset($_GET['del_user'])) {
    $res = deleteUser($_GET['del_user']);
    if ($res['status']) $msg = $res['message']; else $error = $res['message'];
    echo "<script>history.replaceState({}, '', 'users.php');</script>";
}

// 3. Şifre Güncelle
if (isset($_POST['change_password'])) {
    if (!empty($_POST['user_id']) && !empty($_POST['new_password'])) {
        $res = updateUserPassword($_POST['user_id'], $_POST['new_password']);
        if ($res['status']) $msg = $res['message']; else $error = $res['message'];
    }
}

$users = fetchUsers();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Kullanıcı Yönetimi</h2>
</div>

<!-- Bildirimler -->
<?php if ($msg): ?>
    <div class="bg-green-600 text-white p-4 rounded mb-6"><i class="fas fa-check mr-2"></i><?= $msg ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-600 text-white p-4 rounded mb-6"><i class="fas fa-exclamation-triangle mr-2"></i><?= $error ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- SOL: Kullanıcı Listesi ve Şifre Değiştirme -->
    <div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden shadow-lg mb-8">
            <div class="p-4 bg-gray-700 border-b border-gray-600 font-bold text-yellow-500">
                <i class="fas fa-list mr-2"></i> Kayıtlı Kullanıcılar
            </div>
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-900/50 text-gray-400 text-sm uppercase">
                        <th class="p-4">Kullanıcı Adı</th>
                        <th class="p-4">Rol</th>
                        <th class="p-4 text-right">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-700/50 transition">
                        <td class="p-4 font-bold text-white"><?= htmlspecialchars($u['username']) ?></td>
                        <td class="p-4">
                            <?php if(isset($u['role_type']) && $u['role_type'] == 1): ?>
                                <span class="bg-red-500/20 text-red-400 px-2 py-1 rounded text-xs border border-red-500/30">Yönetici</span>
                            <?php else: ?>
                                <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-xs border border-blue-500/30">Personel</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right">
                            <a href="?del_user=<?= $u['id'] ?>" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')" class="text-red-400 hover:bg-red-400/10 p-2 rounded">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Şifre Değiştirme Alanı -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-lg p-6">
            <h3 class="text-lg font-bold text-yellow-500 mb-4"><i class="fas fa-key mr-2"></i> Şifre Değiştir</h3>
            <form method="POST" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Kullanıcı Seçin</label>
                    <select name="user_id" class="w-full bg-gray-900 border border-gray-600 p-3 rounded text-white focus:border-yellow-500 outline-none">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Yeni Şifre</label>
                    <input type="text" name="new_password" placeholder="Yeni şifreyi girin" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded text-white focus:border-yellow-500 outline-none">
                </div>
                <button type="submit" name="change_password" class="bg-blue-600 hover:bg-blue-500 text-white py-3 rounded font-bold transition">
                    Şifreyi Güncelle
                </button>
            </form>
        </div>
    </div>

    <!-- SAĞ: Yeni Kullanıcı Ekleme -->
    <div>
        <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-lg p-8 sticky top-4">
            <div class="mb-6 text-center">
                <div class="w-16 h-16 bg-green-600/20 text-green-500 rounded-full flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Yeni Kullanıcı Ekle</h3>
                <p class="text-gray-400 text-sm">Personel veya yönetici ekleyin.</p>
            </div>
            
            <form method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-2">Kullanıcı Adı</label>
                    <input type="text" name="username" placeholder="Örn: ahmet_berber" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded text-white focus:border-green-500 outline-none transition">
                </div>
                
                <!-- Rol Seçimi -->
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-2">Yetki Rolü</label>
                    <select name="role_type" class="w-full bg-gray-900 border border-gray-600 p-3 rounded text-white focus:border-green-500 outline-none transition">
                        <option value="2">Personel (Randevu Alınabilir)</option>
                        <option value="1">Yönetici / Admin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-2">Şifre</label>
                    <input type="password" name="password" placeholder="******" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded text-white focus:border-green-500 outline-none transition">
                </div>
                <button type="submit" name="add_user" class="w-full bg-green-600 hover:bg-green-500 text-white py-3 rounded font-bold transition shadow-lg transform hover:scale-[1.02]">
                    <i class="fas fa-plus-circle mr-2"></i> Kullanıcı Oluştur
                </button>
            </form>
        </div>
    </div>
</div>

</div></main></div></body></html>