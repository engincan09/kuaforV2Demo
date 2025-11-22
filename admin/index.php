<?php
require 'page/header.php'; // Header dosyasını çekiyoruz

// İstatistikler için verileri çekelim
$services = fetchServices();
$appointments = fetchAppointments();
$users = fetchUsers();

$total_app = count($appointments);
$pending_app = 0;
$today_app = 0;
$today_date = date('Y-m-d');
// Gelir Hesaplama (Basitçe onaylananların fiyatlarını toplayalım)
$revenue = 0;

foreach($appointments as $a) { 
    if(($a['status'] ?? 'Pending') == 'Pending') $pending_app++; 
    if($a['appointment_date'] == $today_date) $today_app++;
    
    // Onaylanmış veya Tamamlanmış randevuların gelirini hesapla
    // (Not: Gerçek sistemde join ile fiyatı çekmek daha doğru olur, burada simüle ediyoruz veya services dizisinden eşleştiriyoruz)
    if(($a['status'] ?? 'Pending') == 'Approved' || ($a['status'] ?? 'Pending') == 'Completed') {
        // Service ID'den fiyatı bul
        foreach($services as $s) {
            if($s['id'] == $a['service_id']) {
                $revenue += $s['price'];
                break;
            }
        }
    }
}
?>

<!-- Sayfa Başlığı -->
<div class="flex justify-between items-end mb-10">
    <div>
        <h1 class="text-4xl font-bold text-white mb-2">Hoş Geldiniz, Admin 👋</h1>
        <p class="text-gray-400">Salonunuzun günlük durumunu buradan takip edebilirsiniz.</p>
    </div>
    <div class="text-right hidden md:block">
        <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Bugünün Tarihi</div>
        <div class="text-xl text-yellow-500 font-mono"><?= date('d.m.Y') ?></div>
    </div>
</div>

<!-- İstatistik Kartları (Yeni Tasarım) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <!-- Kart 1: Toplam Randevu -->
    <div class="relative group bg-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-yellow-500/50 transition-all duration-300 shadow-lg hover:shadow-yellow-500/10 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-calendar-check text-6xl text-white"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">Toplam Randevu</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= $total_app ?></div>
            <div class="text-xs text-gray-500">Tüm zamanlar</div>
        </div>
    </div>

    <!-- Kart 2: Onay Bekleyen -->
    <div class="relative group bg-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-orange-500/50 transition-all duration-300 shadow-lg hover:shadow-orange-500/10 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-clock text-6xl text-white"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-400 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">Onay Bekleyen</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= $pending_app ?></div>
            <div class="text-xs text-orange-400 font-medium">Aksiyon gerekiyor</div>
        </div>
    </div>

    <!-- Kart 3: Bugünün Randevuları -->
    <div class="relative group bg-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 shadow-lg hover:shadow-green-500/10 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-users text-6xl text-white"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 group-hover:bg-green-500 group-hover:text-white transition-colors">
                    <i class="fas fa-user-clock"></i>
                </div>
                <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">Bugün</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= $today_app ?></div>
            <div class="text-xs text-gray-500"><?= date('d.m.Y') ?> tarihinde</div>
        </div>
    </div>

    <!-- Kart 4: Tahmini Gelir -->
    <div class="relative group bg-gradient-to-br from-yellow-600 to-yellow-800 rounded-2xl p-6 border border-yellow-600 transition-all duration-300 shadow-lg shadow-yellow-900/50 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-wallet text-6xl text-black"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-black/20 flex items-center justify-center text-white group-hover:bg-black group-hover:text-yellow-500 transition-colors">
                    <i class="fas fa-lira-sign"></i>
                </div>
                <span class="text-yellow-100 text-sm font-medium uppercase tracking-wider">Tahmini Ciro</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= number_format($revenue, 0) ?> ₺</div>
            <div class="text-xs text-yellow-200">Onaylanan işlemlerden</div>
        </div>
    </div>
</div>

<!-- Alt Bilgi Alanı -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Hızlı Erişim -->
    <div class="lg:col-span-2 bg-gray-800 rounded-2xl border border-gray-700 p-8">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-rocket text-yellow-500 mr-3"></i> Hızlı İşlemler
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="appointments.php" class="flex items-center p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-yellow-500 transition group">
                <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-yellow-500 group-hover:bg-yellow-500/10 transition">
                    <i class="fas fa-list-check"></i>
                </div>
                <div class="ml-4">
                    <div class="text-white font-bold group-hover:text-yellow-500 transition">Randevuları Yönet</div>
                    <div class="text-xs text-gray-500">Onaylama ve iptal işlemleri</div>
                </div>
            </a>
            <a href="services.php" class="flex items-center p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-yellow-500 transition group">
                <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-yellow-500 group-hover:bg-yellow-500/10 transition">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="ml-4">
                    <div class="text-white font-bold group-hover:text-yellow-500 transition">Hizmet Ekle</div>
                    <div class="text-xs text-gray-500">Fiyatları güncelle</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Sistem Durumu -->
    <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-server text-green-500 mr-3"></i> Sistem Durumu
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                <span class="text-gray-400">Yönetici Sayısı</span>
                <span class="text-white font-mono bg-gray-700 px-2 py-1 rounded"><?= count($users) ?></span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                <span class="text-gray-400">Aktif Hizmetler</span>
                <span class="text-white font-mono bg-gray-700 px-2 py-1 rounded"><?= count($services) ?></span>
            </div>
            <div class="mt-4 text-center">
                <a href="settings.php" class="text-sm text-yellow-500 hover:text-yellow-400 underline">Site Ayarlarını Düzenle</a>
            </div>
        </div>
    </div>
</div>

<!-- Kapanış Etiketleri (Header.php'de açılanlar) -->
            </div>
        </main>
    </div>
</body>
</html><?php
require 'header.php'; // Header dosyasını çekiyoruz

// İstatistikler için verileri çekelim
$services = fetchServices();
$appointments = fetchAppointments();
$users = fetchUsers();

$total_app = count($appointments);
$pending_app = 0;
$today_app = 0;
$today_date = date('Y-m-d');

// Gelir Hesaplama (Basitçe onaylananların fiyatlarını toplayalım)
$revenue = 0;

foreach($appointments as $a) { 
    if(($a['status'] ?? 'Pending') == 'Pending') $pending_app++; 
    if($a['appointment_date'] == $today_date) $today_app++;
    
    // Onaylanmış veya Tamamlanmış randevuların gelirini hesapla
    // (Not: Gerçek sistemde join ile fiyatı çekmek daha doğru olur, burada simüle ediyoruz veya services dizisinden eşleştiriyoruz)
    if(($a['status'] ?? 'Pending') == 'Approved' || ($a['status'] ?? 'Pending') == 'Completed') {
        // Service ID'den fiyatı bul
        foreach($services as $s) {
            if($s['id'] == $a['service_id']) {
                $revenue += $s['price'];
                break;
            }
        }
    }
}
?>

<!-- Sayfa Başlığı -->
<div class="flex justify-between items-end mb-10">
    <div>
        <h1 class="text-4xl font-bold text-white mb-2">Hoş Geldiniz, Admin 👋</h1>
        <p class="text-gray-400">Salonunuzun günlük durumunu buradan takip edebilirsiniz.</p>
    </div>
    <div class="text-right hidden md:block">
        <div class="text-sm text-gray-500 uppercase tracking-wider font-bold">Bugünün Tarihi</div>
        <div class="text-xl text-yellow-500 font-mono"><?= date('d.m.Y') ?></div>
    </div>
</div>

<!-- İstatistik Kartları (Yeni Tasarım) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    
    <!-- Kart 1: Toplam Randevu -->
    <div class="relative group bg-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-yellow-500/50 transition-all duration-300 shadow-lg hover:shadow-yellow-500/10 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-calendar-check text-6xl text-white"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">Toplam Randevu</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= $total_app ?></div>
            <div class="text-xs text-gray-500">Tüm zamanlar</div>
        </div>
    </div>

    <!-- Kart 2: Onay Bekleyen -->
    <div class="relative group bg-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-orange-500/50 transition-all duration-300 shadow-lg hover:shadow-orange-500/10 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-clock text-6xl text-white"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-orange-500/20 flex items-center justify-center text-orange-400 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">Onay Bekleyen</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= $pending_app ?></div>
            <div class="text-xs text-orange-400 font-medium">Aksiyon gerekiyor</div>
        </div>
    </div>

    <!-- Kart 3: Bugünün Randevuları -->
    <div class="relative group bg-gray-800 rounded-2xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 shadow-lg hover:shadow-green-500/10 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-users text-6xl text-white"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-green-500/20 flex items-center justify-center text-green-400 group-hover:bg-green-500 group-hover:text-white transition-colors">
                    <i class="fas fa-user-clock"></i>
                </div>
                <span class="text-gray-400 text-sm font-medium uppercase tracking-wider">Bugün</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= $today_app ?></div>
            <div class="text-xs text-gray-500"><?= date('d.m.Y') ?> tarihinde</div>
        </div>
    </div>

    <!-- Kart 4: Tahmini Gelir -->
    <div class="relative group bg-gradient-to-br from-yellow-600 to-yellow-800 rounded-2xl p-6 border border-yellow-600 transition-all duration-300 shadow-lg shadow-yellow-900/50 overflow-hidden">
        <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="fas fa-wallet text-6xl text-black"></i>
        </div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-black/20 flex items-center justify-center text-white group-hover:bg-black group-hover:text-yellow-500 transition-colors">
                    <i class="fas fa-lira-sign"></i>
                </div>
                <span class="text-yellow-100 text-sm font-medium uppercase tracking-wider">Tahmini Ciro</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1"><?= number_format($revenue, 0) ?> ₺</div>
            <div class="text-xs text-yellow-200">Onaylanan işlemlerden</div>
        </div>
    </div>
</div>

<!-- Alt Bilgi Alanı -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Hızlı Erişim -->
    <div class="lg:col-span-2 bg-gray-800 rounded-2xl border border-gray-700 p-8">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-rocket text-yellow-500 mr-3"></i> Hızlı İşlemler
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="appointments.php" class="flex items-center p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-yellow-500 transition group">
                <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-yellow-500 group-hover:bg-yellow-500/10 transition">
                    <i class="fas fa-list-check"></i>
                </div>
                <div class="ml-4">
                    <div class="text-white font-bold group-hover:text-yellow-500 transition">Randevuları Yönet</div>
                    <div class="text-xs text-gray-500">Onaylama ve iptal işlemleri</div>
                </div>
            </a>
            <a href="services.php" class="flex items-center p-4 bg-gray-900 rounded-xl border border-gray-700 hover:border-yellow-500 transition group">
                <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 group-hover:text-yellow-500 group-hover:bg-yellow-500/10 transition">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="ml-4">
                    <div class="text-white font-bold group-hover:text-yellow-500 transition">Hizmet Ekle</div>
                    <div class="text-xs text-gray-500">Fiyatları güncelle</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Sistem Durumu -->
    <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center">
            <i class="fas fa-server text-green-500 mr-3"></i> Sistem Durumu
        </h3>
        <div class="space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                <span class="text-gray-400">Yönetici Sayısı</span>
                <span class="text-white font-mono bg-gray-700 px-2 py-1 rounded"><?= count($users) ?></span>
            </div>
            <div class="flex justify-between items-center pb-3 border-b border-gray-700">
                <span class="text-gray-400">Aktif Hizmetler</span>
                <span class="text-white font-mono bg-gray-700 px-2 py-1 rounded"><?= count($services) ?></span>
            </div>
            <div class="mt-4 text-center">
                <a href="settings.php" class="text-sm text-yellow-500 hover:text-yellow-400 underline">Site Ayarlarını Düzenle</a>
            </div>
        </div>
    </div>
</div>

<!-- Kapanış Etiketleri (Header.php'de açılanlar) -->
            </div>
        </main>
    </div>
</body>
</html>