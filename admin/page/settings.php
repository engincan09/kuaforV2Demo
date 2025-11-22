<?php
require_once __DIR__ . '/header.php';

$msg = "";
if (isset($_POST['update_settings'])) {
    $res = updateSiteSettings($_POST, $_FILES);
    $msg = $res['message'];
}
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold text-white">Genel & SEO Ayarları</h2>
</div>

<?php if ($msg): ?>
    <div class="bg-green-600 text-white p-4 rounded mb-6"><i class="fas fa-check mr-2"></i><?= $msg ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- SOL: İçerik Ayarları -->
    <div class="bg-gray-800 p-8 rounded-xl shadow-xl border border-gray-700">
        <h3 class="text-xl font-bold text-yellow-500 mb-6 border-b border-gray-700 pb-2">Site İçerikleri</h3>
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Hero Başlık</label>
                <textarea name="hero_title" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-yellow-500 focus:outline-none text-white h-24"><?= getSetting('hero_title') ?></textarea>
            </div>
            <div>
                <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Hero Alt Başlık</label>
                <input type="text" name="hero_subtitle" value="<?= getSetting('hero_subtitle') ?>" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-yellow-500 focus:outline-none text-white">
            </div>
            <div>
                <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Hakkımızda Başlık</label>
                <input type="text" name="about_title" value="<?= getSetting('about_title') ?>" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-yellow-500 focus:outline-none text-white">
            </div>
            <div>
                <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Hakkımızda Metni</label>
                <textarea name="about_text" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-yellow-500 focus:outline-none text-white h-32"><?= getSetting('about_text') ?></textarea>
            </div>
            
            <div>
                <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Hakkımızda Resmi</label>
                <div class="flex items-center gap-4">
                    <?php 
                    $aboutImg = getSetting('about_image');
                    if($aboutImg): 
                    ?>
                        <img src="../../<?= $aboutImg ?>" class="w-20 h-20 object-cover rounded border border-gray-600" title="Mevcut Resim">
                    <?php endif; ?>
                    <input type="file" name="about_image" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-600 file:text-black hover:file:bg-yellow-500"/>
                </div>
            </div>
        </div>
    </div>

    <!-- SAĞ: Görünüm & SEO -->
    <div class="space-y-8">
        <!-- Görünüm Ayarları (Renkler) -->
        <div class="bg-gray-800 p-8 rounded-xl shadow-xl border border-gray-700">
            <h3 class="text-xl font-bold text-pink-500 mb-6 border-b border-gray-700 pb-2">Tema Renkleri</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Ana Renk (Gold)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="theme_color_primary" value="<?= getSetting('theme_color_primary') ?: '#D4AF37' ?>" class="w-12 h-12 rounded border-0 cursor-pointer">
                        <span class="text-gray-500 text-xs">Butonlar, vurgular</span>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Arka Plan (Dark)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="theme_color_secondary" value="<?= getSetting('theme_color_secondary') ?: '#121212' ?>" class="w-12 h-12 rounded border-0 cursor-pointer">
                        <span class="text-gray-500 text-xs">Site genel arka planı</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO & Logo Ayarları -->
        <div class="bg-gray-800 p-8 rounded-xl shadow-xl border border-gray-700">
            <h3 class="text-xl font-bold text-blue-400 mb-6 border-b border-gray-700 pb-2">Logo & SEO</h3>
            <div class="grid grid-cols-1 gap-6">
                <!-- Logo Metni -->
                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Logo Metni (HTML)</label>
                    <input type="text" name="site_logo_text" value="<?= htmlspecialchars(getSetting('site_logo_text')) ?>" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-blue-500 focus:outline-none text-white font-mono text-sm">
                </div>

                <div class="border-t border-gray-700 my-2"></div>

                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Site Başlığı</label>
                    <input type="text" name="site_title" placeholder="Örn: Elite Cuts" value="<?= getSetting('site_title') ?>" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                </div>
                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Açıklama</label>
                    <textarea name="site_description" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-blue-500 focus:outline-none text-white h-24"><?= getSetting('site_description') ?></textarea>
                </div>
                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Anahtar Kelimeler</label>
                    <input type="text" name="site_keywords" value="<?= getSetting('site_keywords') ?>" class="w-full bg-gray-900 border border-gray-600 p-4 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                </div>
                
                <div>
                    <label class="block text-gray-400 mb-2 font-bold text-sm uppercase">Favicon</label>
                    <div class="flex items-center gap-4">
                        <?php if($fav = getSetting('site_favicon')): ?>
                            <img src="../../<?= $fav ?>" class="w-12 h-12 rounded bg-white p-1" title="Mevcut İkon">
                        <?php endif; ?>
                        <input type="file" name="site_favicon" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"/>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700">
                <button type="submit" name="update_settings" class="w-full bg-yellow-500 hover:bg-yellow-400 text-black font-bold px-8 py-4 rounded-lg transition shadow-lg transform hover:-translate-y-1">
                    <i class="fas fa-save mr-2"></i> TÜM AYARLARI KAYDET
                </button>
            </div>
        </div>
    </div>
</form>

</div></main></div></body></html>