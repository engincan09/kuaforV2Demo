<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$base_dir = __DIR__;

if (file_exists($base_dir . '/include/config.php')) {
    require_once($base_dir . '/include/config.php');
} else {
    die("HATA: Config dosyası bulunamadı.");
}

if (file_exists($base_dir . '/include/function.php')) {
    require_once($base_dir . '/include/function.php');
} else {
    die("HATA: Function dosyası bulunamadı.");
}

// --- AJAX API ---
if (isset($_GET['action']) && $_GET['action'] == 'get_availability' && isset($_GET['date'])) {
    header('Content-Type: application/json');
    $data = getDailyAvailability($_GET['date']);
    echo json_encode($data);
    exit;
}

// --- İŞLEM MANTIĞI ---
$toast_data = null; 

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    $result = createAppointment($_POST);
    
    $_SESSION['toast_data'] = [
        'type' => $result['status'] ? 'success' : 'error',
        'message' => $result['message']
    ];

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_SESSION['toast_data'])) {
    $toast_data = $_SESSION['toast_data'];
    unset($_SESSION['toast_data']); 
}

// Verileri Çek
$services = fetchServices();
$gallery = fetchGallery();
$staff_members = fetchStaff(); 

// Site Ayarları
$site_title = getSetting('site_title') ?: "Elite Cuts | Profesyonel Erkek Kuaförü";
$site_desc = getSetting('site_description') ?: "Profesyonel saç kesimi ve bakım hizmetleri.";
$site_keys = getSetting('site_keywords') ?: "kuaför, berber, saç kesimi";
$site_fav = getSetting('site_favicon');
$site_logo_text = getSetting('site_logo_text') ?: 'ELITE<span class="text-gold-400">CUTS</span>';
$about_img = getSetting('about_image') ?: "https://images.unsplash.com/photo-1560869713-7d0a29430803?ixlib=rb-4.0.3&q=80&w=2070&auto=format&fit=crop";
$about_text = getSetting('about_text');
$hero_img = getSetting('hero_image') ?: "https://images.unsplash.com/photo-1585747860715-2ba37e788b70?q=80&w=2074&auto=format&fit=crop";

// RENKLER
$primary_color = getSetting('theme_color_primary') ?: '#D4AF37'; 
$secondary_color = getSetting('theme_color_secondary') ?: '#121212'; 
?>
<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site_desc) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($site_keys) ?>">
    
    <?php if($site_fav): ?>
        <link rel="icon" href="<?= $site_fav ?>" type="image/x-icon">
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { 
                            400: '<?= $primary_color ?>', 
                            500: '<?= $primary_color ?>', 
                            600: '<?= $primary_color ?>' 
                        },
                        dark: { 
                            900: '<?= $secondary_color ?>', 
                            800: '#1E1E1E', 
                            700: '#2D2D2D' 
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-serif { font-family: 'Playfair Display', serif; }
        
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
        .toast-enter { animation: slideIn 0.5s ease-out forwards; }
        .toast-exit { animation: fadeOut 0.5s ease-out forwards; }
        
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: #2D2D2D; }
        .modal-scroll::-webkit-scrollbar-thumb { background: <?= $primary_color ?>; border-radius: 3px; }
        
        ::selection { background-color: <?= $primary_color ?>; color: black; }
    </style>
</head>
<body class="bg-dark-900 text-gray-100 antialiased overflow-x-hidden">

    <div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-3 pointer-events-none"></div>

    <!-- GALERİ LIGHTBOX (BÜYÜTME MODALI) -->
    <div id="galleryModal" class="fixed inset-0 z-[70] hidden bg-black/90 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeGallery()">
        <div class="relative max-w-5xl w-full flex justify-center">
            <img id="galleryImage" src="" class="max-h-[90vh] max-w-full rounded-lg shadow-2xl transform transition-transform duration-300 scale-95">
            <button class="absolute -top-12 right-0 text-white text-4xl hover:text-gold-500 transition focus:outline-none" onclick="closeGallery()">&times;</button>
        </div>
    </div>

    <!-- MÜSAİTLİK MODALI -->
    <div id="availabilityModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="toggleModal(false)"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-dark-800 border border-white/10 rounded-2xl shadow-2xl w-full max-w-3xl max-h-[80vh] flex flex-col overflow-hidden transform transition-all scale-95" id="modalContent">
                <div class="p-6 border-b border-white/10 flex justify-between items-center bg-dark-900">
                    <div>
                        <h3 class="text-xl font-bold text-gold-500">Personel Müsaitlik Durumu</h3>
                        <p class="text-xs text-gray-400 mt-1">Tarih seçerek personellerin doluluk oranını görebilirsiniz.</p>
                    </div>
                    <button onclick="toggleModal(false)" class="text-gray-400 hover:text-white transition"><i class="fas fa-times text-2xl"></i></button>
                </div>
                
                <div class="p-6 border-b border-white/5 bg-dark-800/50 flex gap-4 items-center">
                    <input type="date" id="modalDate" class="bg-dark-900 border border-gray-600 text-white rounded-lg px-4 py-2 focus:border-gold-500 outline-none w-full md:w-auto" value="<?= date('Y-m-d') ?>">
                    <button onclick="fetchAvailability()" class="bg-gold-500 hover:bg-gold-600 text-black px-4 py-2 rounded-lg font-bold transition"><i class="fas fa-search mr-2"></i>Sorgula</button>
                </div>

                <div class="p-6 overflow-y-auto modal-scroll bg-dark-900" id="availabilityGrid">
                    <div class="text-center text-gray-500 py-10">Lütfen tarih seçip sorgulama yapın.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- NAVBAR -->
    <nav class="fixed w-full z-50 bg-dark-900/90 backdrop-blur-lg border-b border-white/5 transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2 cursor-pointer" onclick="window.scrollTo(0,0)">
                <?php if($site_fav): ?>
                    <img src="<?= $site_fav ?>" class="w-10 h-10 rounded-full p-1 object-cover">
                <?php else: ?>
                    <div class="w-10 h-10 bg-gold-500 rounded-full flex items-center justify-center text-black font-bold text-lg"><i class="fas fa-cut"></i></div>
                <?php endif; ?>
                <span class="text-2xl font-bold font-serif tracking-wide text-white"><?= $site_logo_text ?></span>
            </div>
            
            <div class="hidden md:flex space-x-8 items-center">
                <a href="#home" class="hover:text-gold-400 text-sm font-medium transition">Ana Sayfa</a>
                <a href="#about" class="hover:text-gold-400 text-sm font-medium transition">Hakkımızda</a>
                <a href="#services" class="hover:text-gold-400 text-sm font-medium transition">Hizmetler</a>
                <a href="#gallery" class="hover:text-gold-400 text-sm font-medium transition">Galeri</a>
                <a href="#booking" class="bg-gold-500 text-black font-bold px-6 py-2 rounded hover:bg-white transition shadow-lg shadow-gold-500/20">Randevu Al</a>
            </div>

            <div class="md:hidden flex items-center">
                <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="text-white text-2xl focus:outline-none hover:text-gold-400 transition"><i class="fas fa-bars"></i></button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-dark-900 border-t border-white/10 absolute w-full left-0 top-20 shadow-2xl transition-all duration-300 z-40">
            <div class="px-4 pt-4 pb-6 space-y-2 flex flex-col">
                <a href="#home" class="block px-3 py-3 text-base font-medium text-gray-300">Ana Sayfa</a>
                <a href="#services" class="block px-3 py-3 text-base font-medium text-gray-300">Hizmetler</a>
                <a href="admin/" class="block px-3 py-3 text-sm text-gray-500 border-t border-white/5 pt-4">Yönetim Paneli</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section id="home" class="relative h-screen flex items-center justify-center text-center px-4 pt-20">
        <div class="absolute inset-0 z-0">
            <img src="<?= $hero_img ?>" class="w-full h-full object-cover opacity-30" alt="Background">
            <div class="absolute inset-0 bg-gradient-to-t from-dark-900 via-transparent to-dark-900/90"></div>
        </div>
        <div class="relative z-10 max-w-4xl mx-auto animate-fade-in-up">
            <span class="text-gold-400 uppercase tracking-[0.3em] text-sm font-bold mb-4 block"><?= getSetting('hero_subtitle') ?></span>
            <h1 class="text-5xl md:text-7xl font-bold text-white mb-8 leading-tight font-serif"><?= getSetting('hero_title') ?></h1>
            <div class="flex justify-center gap-4">
                <a href="#booking" class="bg-gold-500 text-black font-bold px-10 py-4 rounded hover:bg-white transition duration-300 transform hover:scale-105 shadow-xl shadow-gold-500/20">HEMEN RANDEVU AL</a>
            </div>
        </div>
    </section>

    <!-- HAKKIMIZDA -->
    <section id="about" class="py-24 bg-dark-900 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <div class="relative group">
                <div class="absolute -inset-4 bg-gold-500/20 rounded-2xl blur opacity-25 group-hover:opacity-75 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-white/10">
                    <img src="<?= $about_img ?>" class="w-full h-[500px] object-cover filter grayscale group-hover:grayscale-0 transition duration-700 transform group-hover:scale-105">
                    <div class="absolute inset-0 border-2 border-gold-500/30 rounded-2xl m-4 pointer-events-none"></div>
                </div>
            </div>
            <div>
                <h2 class="text-gold-400 text-sm font-bold tracking-[0.2em] uppercase mb-4 flex items-center">
                    <span class="w-10 h-[2px] bg-gold-500 mr-3"></span> Biz Kimiz?
                </h2>
                <h3 class="text-4xl md:text-5xl font-bold text-white mb-8 font-serif leading-tight"><?= getSetting('about_title') ?></h3>
                <div class="text-gray-400 mb-8 leading-relaxed text-lg font-light space-y-4">
                    <?= nl2br($about_text) ?>
                </div>
                <div class="grid grid-cols-2 gap-8 border-t border-gray-800 pt-8">
                    <div>
                        <div class="text-5xl font-bold text-white mb-2">15+</div>
                        <div class="text-sm text-gray-500 uppercase tracking-wider">Yıllık Deneyim</div>
                    </div>
                    <div>
                        <div class="text-5xl font-bold text-white mb-2">5k+</div>
                        <div class="text-sm text-gray-500 uppercase tracking-wider">Mutlu Müşteri</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HİZMETLER -->
    <section id="services" class="py-24 bg-dark-800">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-gold-400 text-sm font-bold tracking-widest uppercase mb-2">Hizmetlerimiz</h2>
                <h3 class="text-3xl md:text-4xl font-bold text-white font-serif">Size Özel Bakım</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php if(!empty($services)): foreach ($services as $svc): ?>
                    <div class="bg-dark-900 p-10 rounded-xl border border-white/5 hover:border-gold-500 transition duration-300 group cursor-pointer relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-6 opacity-5 text-6xl group-hover:text-gold-500 transition"><i class="fas fa-cut"></i></div>
                        <div class="relative z-10">
                            <h3 class="text-2xl font-bold text-white mb-2 font-serif group-hover:text-gold-400 transition"><?= htmlspecialchars($svc['name']) ?></h3>
                            <div class="text-gold-500 font-bold text-xl mb-4"><?= number_format($svc['price'], 0) ?> ₺</div>
                            <p class="text-gray-400 text-sm leading-relaxed group-hover:text-gray-300"><?= htmlspecialchars($svc['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="col-span-3 text-center py-10 bg-dark-900 rounded border border-dashed border-gray-700 text-gray-500">Henüz hizmet eklenmemiş.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- GALERİ BÖLÜMÜ (GÜNCELLENDİ - ONCLICK EKLENDİ) -->
    <section id="gallery" class="py-24 bg-dark-900 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-gold-400 text-sm font-bold tracking-widest uppercase mb-2">Galerimiz</h2>
                <h3 class="text-3xl md:text-4xl font-bold text-white font-serif">Salonumuzdan Kareler</h3>
            </div>

            <?php if(empty($gallery)): ?>
                <div class="text-center py-12 border border-dashed border-gray-800 rounded-lg text-gray-600">
                    Henüz galeriye resim eklenmemiş.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach($gallery as $img): ?>
                        <!-- ONCLICK EKLENDİ: Resim yolunu fonksiyona gönderiyoruz -->
                        <div class="group relative overflow-hidden rounded-lg aspect-square cursor-pointer" onclick="openGallery('<?= htmlspecialchars($img['image_url']) ?>')">
                            <img src="<?= htmlspecialchars($img['image_url']) ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700 filter grayscale group-hover:grayscale-0">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-gold-500 text-3xl transform scale-0 group-hover:scale-100 transition duration-300 delay-100"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- RANDEVU FORMU -->
    <section id="booking" class="py-24 bg-dark-800 relative">
        <div class="max-w-4xl mx-auto px-4 relative z-10">
            <div class="bg-dark-900 p-8 md:p-12 rounded-2xl shadow-2xl border border-white/10 relative">
                
                <h2 class="text-3xl font-bold text-center mb-2 font-serif text-white">Randevu Oluştur</h2>
                <p class="text-center text-gray-400 mb-10">Size uygun zamanı ve personeli seçin.</p>
                
                <div class="text-center mb-8">
                    <button onclick="toggleModal(true)" class="inline-flex items-center gap-2 bg-blue-600/20 text-blue-400 border border-blue-500/50 px-4 py-2 rounded-full text-sm font-bold hover:bg-blue-600 hover:text-white transition">
                        <i class="fas fa-calendar-alt"></i> Personel Doluluk Durumunu Gör
                    </button>
                </div>

                <form method="POST" id="bookingForm">
                    <input type="hidden" name="book_appointment" value="1">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-2 font-bold">Ad Soyad</label>
                                <input type="text" name="name" required class="w-full bg-dark-800 border border-gray-700 p-4 rounded text-white focus:border-gold-500 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-2 font-bold">Telefon</label>
                                <input type="tel" name="phone" required class="w-full bg-dark-800 border border-gray-700 p-4 rounded text-white focus:border-gold-500 focus:outline-none transition">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-2 font-bold">Hizmet Seçimi</label>
                                <select name="service" class="w-full bg-dark-800 border border-gray-700 p-4 rounded text-white focus:border-gold-500 focus:outline-none transition">
                                    <?php if(!empty($services)): foreach ($services as $svc): ?>
                                        <option value="<?= $svc['id'] ?>"><?= $svc['name'] ?> - <?= $svc['price'] ?> ₺</option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-2 font-bold">Personel (İsteğe Bağlı)</label>
                                <select name="staff_id" class="w-full bg-dark-800 border border-gray-700 p-4 rounded text-white focus:border-gold-500 focus:outline-none transition">
                                    <option value="">Farketmez (Herhangi bir personel)</option>
                                    <?php foreach ($staff_members as $staff): ?>
                                        <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-2 font-bold">Tarih</label>
                                <input type="date" name="date" required class="w-full bg-dark-800 border border-gray-700 p-4 rounded text-white focus:border-gold-500 focus:outline-none transition" min="<?= date('Y-m-d') ?>">
                            </div>
                            <div>
                                <label class="block text-xs uppercase text-gray-500 mb-2 font-bold">Saat</label>
                                <input type="time" name="time" required class="w-full bg-dark-800 border border-gray-700 p-4 rounded text-white focus:border-gold-500 focus:outline-none transition" min="09:00" max="20:00">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gold-500 text-black font-bold py-5 rounded hover:bg-white transition duration-300 mt-4 uppercase tracking-widest shadow-lg shadow-gold-500/20">
                            Randevuyu Onayla
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black py-8 border-t border-white/10 text-center">
        <div class="text-gray-500 text-sm">&copy; 2025 Elite Cuts. Tüm hakları saklıdır.</div>
    </footer>

    <!-- SCRIPTS -->
    <script>
        // Toast Function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            const icon = type === 'success' ? '<i class="fas fa-check-circle text-xl"></i>' : '<i class="fas fa-exclamation-circle text-xl"></i>';
            toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-4 min-w-[300px] pointer-events-auto transform transition-all duration-300 toast-enter border-l-4 border-white/20`;
            toast.innerHTML = `${icon}<div class="flex-1"><h4 class="font-bold text-sm uppercase tracking-wider">${type==='success'?'Başarılı':'Hata'}</h4><p class="text-sm text-white/90">${message}</p></div><button onclick="this.parentElement.remove()" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('toast-enter'); toast.classList.add('toast-exit'); setTimeout(() => toast.remove(), 500); }, 5000);
        }

        // Availability Modal
        function toggleModal(show) {
            const modal = document.getElementById('availabilityModal');
            if(show) {
                modal.classList.remove('hidden');
                fetchAvailability(); 
            } else {
                modal.classList.add('hidden');
            }
        }

        async function fetchAvailability() {
            const date = document.getElementById('modalDate').value;
            const grid = document.getElementById('availabilityGrid');
            
            grid.innerHTML = '<div class="text-center py-10"><i class="fas fa-spinner fa-spin text-gold-500 text-3xl"></i><div class="text-gray-400 mt-2">Yükleniyor...</div></div>';

            try {
                const response = await fetch(`index.php?action=get_availability&date=${date}`);
                const data = await response.json();

                if(data.length === 0) {
                    grid.innerHTML = '<div class="text-center text-gray-500 py-10">Veri bulunamadı.</div>';
                    return;
                }

                let html = '';
                data.forEach(staff => {
                    html += `
                    <div class="mb-6 border-b border-white/5 pb-4 last:border-0">
                        <h4 class="text-white font-bold mb-3 flex items-center"><i class="fas fa-user-circle text-gray-400 mr-2"></i>${staff.staff_name}</h4>
                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
                    `;
                    
                    staff.schedule.forEach(slot => {
                        const bgClass = slot.status === 'free' 
                            ? 'bg-green-600/20 text-green-400 border-green-600/30 hover:bg-green-600 hover:text-white' 
                            : 'bg-red-600/10 text-red-500 border-red-600/20 cursor-not-allowed opacity-50';
                        
                        html += `<div class="${bgClass} border rounded py-1 px-2 text-center text-xs font-mono transition">${slot.time}</div>`;
                    });

                    html += `</div></div>`;
                });

                grid.innerHTML = html;

            } catch (error) {
                grid.innerHTML = '<div class="text-center text-red-500 py-10">Bir hata oluştu.</div>';
            }
        }

        // GALERİ LIGHTBOX FONKSİYONLARI (YENİ)
        function openGallery(imageSrc) {
            const modal = document.getElementById('galleryModal');
            const img = document.getElementById('galleryImage');
            img.src = imageSrc;
            modal.classList.remove('hidden');
            // Animasyon için küçük bir gecikme
            setTimeout(() => img.classList.remove('scale-95'), 10);
        }

        function closeGallery() {
            const modal = document.getElementById('galleryModal');
            const img = document.getElementById('galleryImage');
            img.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                img.src = '';
            }, 300);
        }

        <?php if ($toast_data): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?= addslashes($toast_data['message']) ?>", "<?= $toast_data['type'] ?>");
            });
        <?php endif; ?>
    </script>

</body>
</html>