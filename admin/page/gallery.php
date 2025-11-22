<?php
require_once __DIR__ . '/header.php';

$msg = "";
$error = "";

// Resim Yükleme
if (isset($_POST['upload_image'])) {
    if (isset($_FILES['gallery_image'])) {
        $res = addGalleryImage($_FILES['gallery_image']);
        if ($res['status']) $msg = $res['message']; else $error = $res['message'];
    }
}

// Resim Silme
if (isset($_GET['delete_id'])) {
    $res = deleteGalleryImage($_GET['delete_id']);
    if ($res['status']) $msg = $res['message']; else $error = $res['message'];
    echo "<script>history.replaceState({}, '', 'gallery.php');</script>";
}

$images = fetchGallery();
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Galeri Yönetimi</h2>
</div>

<?php if ($msg): ?>
    <div class="bg-green-600 text-white p-4 rounded mb-6"><i class="fas fa-check mr-2"></i><?= $msg ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-600 text-white p-4 rounded mb-6"><i class="fas fa-exclamation-triangle mr-2"></i><?= $error ?></div>
<?php endif; ?>

<!-- Resim Yükleme Formu -->
<div class="bg-gray-800 p-6 rounded-xl shadow-xl border border-gray-700 mb-8">
    <h3 class="text-lg font-bold text-yellow-500 mb-4">Yeni Resim Ekle</h3>
    <form method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <input type="file" name="gallery_image" class="block w-full text-sm text-gray-400 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-600 file:text-black hover:file:bg-yellow-500 cursor-pointer bg-gray-900 rounded-lg" required>
        </div>
        <button type="submit" name="upload_image" class="bg-green-600 hover:bg-green-500 text-white px-6 py-3 rounded-lg font-bold transition w-full md:w-auto shadow-lg">
            <i class="fas fa-cloud-upload-alt mr-2"></i> Yükle
        </button>
    </form>
</div>

<!-- Galeri Grid -->
<?php if (empty($images)): ?>
    <div class="text-center py-12 bg-gray-800 rounded-xl border border-dashed border-gray-600">
        <i class="far fa-images text-6xl text-gray-600 mb-4"></i>
        <p class="text-gray-400">Henüz hiç resim yüklenmemiş.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php foreach ($images as $img): ?>
            <div class="relative group bg-gray-800 p-2 rounded-xl border border-gray-700 shadow-lg">
                <div class="aspect-square overflow-hidden rounded-lg">
                    <img src="../../<?= htmlspecialchars($img['image_url']) ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
                </div>
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center rounded-xl">
                    <a href="?delete_id=<?= $img['id'] ?>" onclick="return confirm('Bu resmi silmek istediğinize emin misiniz?')" class="bg-red-600 hover:bg-red-500 text-white p-3 rounded-full shadow-xl transform hover:scale-110 transition">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</div></main></div></body></html>