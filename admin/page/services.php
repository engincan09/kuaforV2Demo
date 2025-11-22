<?php
// DÜZELTME: Tam yol kullanımı (Dosya admin/page altında olduğu için)
require_once __DIR__ . '/header.php';

$msg = "";
if (isset($_POST['add_service'])) {
    // GÜNCELLEME: Süre (duration) parametresi de gönderiliyor
    $res = addService($_POST['name'], $_POST['price'], $_POST['desc'], $_POST['duration']);
    $msg = $res['message'];
}

if (isset($_GET['del_service'])) {
    deleteService($_GET['del_service']);
    echo "<script>window.location.href='services.php';</script>";
}

$services = fetchServices();
?>

<h2 class="text-3xl font-bold text-white mb-6">Hizmet Yönetimi</h2>

<?php if ($msg): ?>
    <div class="bg-green-600 text-white p-4 rounded mb-6"><i class="fas fa-check mr-2"></i><?= $msg ?></div>
<?php endif; ?>

<!-- Ekleme Formu -->
<div class="bg-gray-800 p-6 rounded-xl shadow-xl border border-gray-700 mb-8">
    <h3 class="text-lg font-bold text-yellow-500 mb-4">Yeni Hizmet Ekle</h3>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div class="md:col-span-2">
            <label class="text-gray-400 text-xs block mb-1 font-bold">Hizmet Adı</label>
            <input type="text" name="name" placeholder="Örn: Saç Kesimi" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white">
        </div>
        
        <div>
            <label class="text-gray-400 text-xs block mb-1 font-bold">Fiyat (TL)</label>
            <input type="number" name="price" placeholder="300" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white">
        </div>

        <!-- YENİ: Süre Alanı -->
        <div>
            <label class="text-gray-400 text-xs block mb-1 font-bold">Süre (Dk)</label>
            <input type="number" name="duration" placeholder="30" value="30" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white" title="İşlem süresi dakika cinsinden">
        </div>

        <div>
            <label class="text-gray-400 text-xs block mb-1 font-bold">Açıklama</label>
            <input type="text" name="desc" placeholder="Kısa bilgi" class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white">
        </div>

        <div class="md:col-span-5 text-right mt-2">
            <button type="submit" name="add_service" class="bg-green-600 hover:bg-green-500 text-white px-8 py-3 rounded-lg font-bold transition shadow-lg transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i> Ekle
            </button>
        </div>
    </form>
</div>

<!-- Tablo -->
<div class="bg-gray-800 rounded-xl shadow-xl overflow-hidden border border-gray-700">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-700 text-gray-300 uppercase text-xs tracking-wider">
                <tr>
                    <th class="p-4">Hizmet Adı</th>
                    <th class="p-4">Fiyat</th>
                    <th class="p-4">Süre</th> <!-- YENİ SÜTUN -->
                    <th class="p-4">Açıklama</th>
                    <th class="p-4 text-right">İşlem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if(!empty($services)): foreach ($services as $s): ?>
                    <tr class="hover:bg-gray-700/50 transition group">
                        <td class="p-4 font-bold text-white"><?= htmlspecialchars($s['name']) ?></td>
                        <td class="p-4 text-yellow-500 font-bold"><?= number_format($s['price'], 0) ?> ₺</td>
                        <td class="p-4 text-blue-300 font-mono text-sm">
                            <i class="far fa-clock mr-1"></i> <?= $s['duration'] ?> dk
                        </td>
                        <td class="p-4 text-sm text-gray-400"><?= htmlspecialchars($s['description']) ?></td>
                        <td class="p-4 text-right">
                            <a href="?del_service=<?= $s['id'] ?>" onclick="return confirm('Bu hizmeti silmek istediğinize emin misiniz?')" class="text-red-400 hover:bg-red-400/10 p-2 rounded transition inline-block">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">Henüz hizmet eklenmemiş.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div></main></div></body></html>