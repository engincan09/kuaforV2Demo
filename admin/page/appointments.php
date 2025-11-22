<?php
// DÜZELTME: Tam yol kullanımı
require_once __DIR__ . '/header.php';

// Durum Güncelleme İsteği Geldi mi?
if (isset($_GET['action']) && isset($_GET['id'])) {
    $newStatus = '';
    switch ($_GET['action']) {
        case 'approve': $newStatus = 'Approved'; break;
        case 'complete': $newStatus = 'Completed'; break;
        case 'cancel': $newStatus = 'Cancelled'; break;
    }
    
    if ($newStatus) {
        updateAppointmentStatus($_GET['id'], $newStatus);
        // İşlemden sonra URL'i temizle ve yenile
        echo "<script>window.location.href='appointments.php';</script>";
        exit;
    }
}

$appointments = fetchAppointments();
?>

<h2 class="text-3xl font-bold text-white mb-6">Randevu Yönetimi</h2>

<div class="bg-gray-800 rounded-xl shadow-xl overflow-hidden border border-gray-700">
    <!-- Mobilde yatay scroll için overflow-x-auto -->
    <div class="overflow-x-auto">
        <table class="w-full text-left min-w-[800px]">
            <thead class="bg-gray-700 text-gray-300 uppercase text-xs tracking-wider">
                <tr>
                    <th class="p-4">Tarih & Saat</th>
                    <th class="p-4">Müşteri</th>
                    <th class="p-4">Hizmet & Personel</th>
                    <th class="p-4">Durum</th>
                    <th class="p-4 text-right">İşlemler</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                <?php if(!empty($appointments)): foreach ($appointments as $app): ?>
                    <tr class="hover:bg-gray-700/50 transition group">
                        <!-- Tarih -->
                        <td class="p-4">
                            <div class="font-bold text-white"><?= date('d.m.Y', strtotime($app['appointment_date'])) ?></div>
                            <div class="text-yellow-500 text-sm font-bold"><i class="far fa-clock mr-1"></i> <?= substr($app['appointment_time'], 0, 5) ?></div>
                        </td>
                        
                        <!-- Müşteri -->
                        <td class="p-4">
                            <div class="text-white font-medium"><?= htmlspecialchars($app['customer_name']) ?></div>
                            <div class="text-gray-400 text-sm"><i class="fas fa-phone-alt mr-1 text-xs"></i> <?= htmlspecialchars($app['customer_phone']) ?></div>
                        </td>
                        
                        <!-- Hizmet ve Personel -->
                        <td class="p-4">
                            <div class="text-white font-medium"><?= htmlspecialchars($app['service_name'] ?? 'Bilinmiyor') ?></div>
                            <div class="text-xs mt-1 inline-block px-2 py-0.5 rounded bg-gray-900 border border-gray-600 text-gray-300">
                                <i class="fas fa-user mr-1 text-yellow-500"></i>
                                <?= !empty($app['staff_name']) ? htmlspecialchars($app['staff_name']) : 'Genel' ?>
                            </div>
                        </td>
                        
                        <!-- Durum -->
                        <td class="p-4">
                            <?php
                            $statusColor = 'bg-gray-600 text-gray-200'; // Varsayılan
                            $statusText = 'Bilinmiyor';
                            
                            switch($app['status']) {
                                case 'Pending': 
                                    $statusColor = 'bg-orange-500/20 text-orange-400 border-orange-500/30'; 
                                    $statusText = 'Bekliyor'; 
                                    break;
                                case 'Approved': 
                                    $statusColor = 'bg-blue-500/20 text-blue-400 border-blue-500/30'; 
                                    $statusText = 'Onaylandı'; 
                                    break;
                                case 'Completed': 
                                    $statusColor = 'bg-green-500/20 text-green-400 border-green-500/30'; 
                                    $statusText = 'Tamamlandı'; 
                                    break;
                                case 'Cancelled': 
                                    $statusColor = 'bg-red-500/20 text-red-400 border-red-500/30'; 
                                    $statusText = 'İptal'; 
                                    break;
                            }
                            ?>
                            <span class="<?= $statusColor ?> border px-3 py-1 rounded-full text-xs font-bold uppercase">
                                <?= $statusText ?>
                            </span>
                        </td>

                        <!-- İşlemler (Butonlar) -->
                        <td class="p-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                
                                <!-- Onayla (Sadece Bekliyorsa veya İptalse) -->
                                <?php if($app['status'] == 'Pending' || $app['status'] == 'Cancelled'): ?>
                                    <a href="?action=approve&id=<?= $app['id'] ?>" class="bg-blue-600 hover:bg-blue-500 text-white p-2 rounded-lg text-xs font-bold shadow-lg transition" title="Onayla">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- Tamamla (Sadece Onaylıysa) -->
                                <?php if($app['status'] == 'Approved'): ?>
                                    <a href="?action=complete&id=<?= $app['id'] ?>" class="bg-green-600 hover:bg-green-500 text-white p-2 rounded-lg text-xs font-bold shadow-lg transition" title="Tamamla">
                                        <i class="fas fa-check-double"></i>
                                    </a>
                                <?php endif; ?>

                                <!-- İptal Et (Tamamlanmamışsa) -->
                                <?php if($app['status'] != 'Cancelled' && $app['status'] != 'Completed'): ?>
                                    <a href="?action=cancel&id=<?= $app['id'] ?>" onclick="return confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?')" class="bg-red-600 hover:bg-red-500 text-white p-2 rounded-lg text-xs font-bold shadow-lg transition" title="İptal Et">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">Henüz kayıtlı randevu yok.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</div></main></div></body></html>