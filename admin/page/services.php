<?php
// DÜZELTME: header.php'yi tam yol ile çağırıyoruz
require_once 'header.php';

$msg = "";
if (isset($_POST['add_service'])) {
    $res = addService($_POST['name'], $_POST['price'], $_POST['desc']);
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
    <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <input type="text" name="name" placeholder="Hizmet Adı" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white">
        </div>
        <div class="w-full md:w-32">
            <input type="number" name="price" placeholder="Fiyat (TL)" required class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white">
        </div>
        <div class="flex-1 w-full">
            <input type="text" name="desc" placeholder="Kısa Açıklama" class="w-full bg-gray-900 border border-gray-600 p-3 rounded focus:border-yellow-500 outline-none text-white">
        </div>
        <button type="submit" name="add_service" class="bg-green-600 hover:bg-green-500 text-white px-6 py-3 rounded font-bold transition w-full md:w-auto">
            <i class="fas fa-plus"></i> Ekle
        </button>
    </form>
</div>

<!-- Tablo -->
<div class="bg-gray-800 rounded-xl shadow-xl overflow-hidden border border-gray-700">
    <table class="w-full text-left">
        <thead class="bg-gray-700 text-gray-300 uppercase text-xs tracking-wider">
            <tr>
                <th class="p-4">Hizmet Adı</th>
                <th class="p-4">Fiyat</th>
                <th class="p-4">Açıklama</th>
                <th class="p-4 text-right">İşlem</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
            <?php foreach ($services as $s): ?>
                <tr class="hover:bg-gray-700/50 transition">
                    <td class="p-4 font-bold text-white"><?= htmlspecialchars($s['name']) ?></td>
                    <td class="p-4 text-yellow-500 font-bold"><?= number_format($s['price'], 0) ?> ₺</td>
                    <td class="p-4 text-sm text-gray-400"><?= htmlspecialchars($s['description']) ?></td>
                    <td class="p-4 text-right">
                        <a href="?del_service=<?= $s['id'] ?>" onclick="return confirm('Silinsin mi?')" class="text-red-400 hover:bg-red-400/10 px-3 py-1 rounded transition">
                            <i class="fas fa-trash-alt"></i> Sil
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Header.php'de açılan etiketleri kapat -->
</div></main></div></body></html>