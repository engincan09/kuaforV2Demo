<?php
// AYAR FONKSİYONLARI

function getSetting($key) {
    global $conn;
    if (!$conn) return "";
    try {
        $stmt = $conn->prepare("SELECT setting_value FROM Settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: ""; 
    } catch (PDOException $e) {
        return "";
    }
}

function updateSiteSettings($postData, $filesData = null) {
    global $conn;
    
    $settings = [
        'hero_title', 'hero_subtitle', 'about_title', 'about_text',
        'site_title', 'site_description', 'site_keywords', 'site_logo_text',
        'theme_color_primary', 'theme_color_secondary'
    ];

    try {
        $sql = "INSERT INTO Settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);

        foreach ($settings as $key) {
            if (isset($postData[$key])) {
                $stmt->execute([$key, $postData[$key]]);
            }
        }

        if (isset($filesData['site_favicon']) && $filesData['site_favicon']['error'] == 0) {
            $allowed = ['ico', 'png', 'jpg', 'jpeg', 'svg'];
            $ext = strtolower(pathinfo($filesData['site_favicon']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../uploads/'; 
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileName = 'favicon.' . $ext;
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($filesData['site_favicon']['tmp_name'], $targetPath)) {
                    $dbPath = 'uploads/' . $fileName . '?v=' . time();
                    $stmt->execute(['site_favicon', $dbPath]);
                }
            }
        }

        if (isset($filesData['about_image']) && $filesData['about_image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($filesData['about_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../uploads/'; 
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $fileName = 'about_bg_' . time() . '.' . $ext;
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($filesData['about_image']['tmp_name'], $targetPath)) {
                    $dbPath = 'uploads/' . $fileName;
                    $stmt->execute(['about_image', $dbPath]);
                }
            }
        }

        return ["status" => true, "message" => "Tüm ayarlar başarıyla güncellendi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

// ... (Diğer fonksiyonlar aynı: fetchServices, addService, deleteService, fetchGallery, addGalleryImage, deleteGalleryImage, fetchAppointments, updateAppointmentStatus)
/**
 * HİZMET FONKSİYONLARI
 */
function fetchServices() {
    global $conn;
    if (!$conn) return [];
    try {
        $stmt = $conn->query("SELECT * FROM Services");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function addService($name, $price, $desc) {
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO Services (name, price, description) VALUES (?, ?, ?)");
        $result = $stmt->execute([$name, $price, $desc]);
        return $result ? ["status" => true, "message" => "Hizmet eklendi."] : ["status" => false, "message" => "Eklenemedi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

function deleteService($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("DELETE FROM Services WHERE id = ?");
        $stmt->execute([$id]);
        return ["status" => true, "message" => "Hizmet silindi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Silinemedi."];
    }
}

/**
 * GALERİ & RANDEVU FONKSİYONLARI
 */
function fetchGallery() {
    global $conn;
    if (!$conn) return [];
    try {
        $stmt = $conn->query("SELECT * FROM Gallery ORDER BY uploaded_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function addGalleryImage($fileData) {
    global $conn;
    if ($fileData['error'] != 0) return ["status" => false, "message" => "Dosya yükleme hatası."];

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) return ["status" => false, "message" => "Geçersiz dosya formatı."];

    $uploadDir = __DIR__ . '/../uploads/gallery/'; 
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = uniqid() . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
        try {
            $dbPath = 'uploads/gallery/' . $fileName;
            $stmt = $conn->prepare("INSERT INTO Gallery (image_url) VALUES (?)");
            $stmt->execute([$dbPath]);
            return ["status" => true, "message" => "Resim galeriye eklendi."];
        } catch (PDOException $e) {
            return ["status" => false, "message" => "Veritabanı hatası."];
        }
    }
    return ["status" => false, "message" => "Dosya taşınamadı."];
}

function deleteGalleryImage($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT image_url FROM Gallery WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($img) {
            $filePath = __DIR__ . '/../' . $img['image_url'];
            if (file_exists($filePath)) unlink($filePath);

            $del = $conn->prepare("DELETE FROM Gallery WHERE id = ?");
            $del->execute([$id]);
            return ["status" => true, "message" => "Resim silindi."];
        }
        return ["status" => false, "message" => "Resim bulunamadı."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Silinemedi."];
    }
}

function fetchAppointments() {
    global $conn;
    if (!$conn) return [];
    try {
        $sql = "SELECT a.*, u.username as staff_name, s.name as service_name 
                FROM Appointments a 
                LEFT JOIN Users u ON a.staff_id = u.id 
                LEFT JOIN Services s ON a.service_id = s.id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function updateAppointmentStatus($id, $status) {
    global $conn;
    try {
        $allowed = ['Pending', 'Approved', 'Cancelled', 'Completed'];
        if (!in_array($status, $allowed)) return ["status" => false, "message" => "Geçersiz durum."];

        $stmt = $conn->prepare("UPDATE Appointments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        return ["status" => true, "message" => "Randevu durumu güncellendi: " . $status];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

// KRİTİK GÜNCELLEME: Personel Müsaitlik Kontrolü
// Sadece 'Pending' ve 'Approved' olanlar "DOLU" sayılır. 
// 'Completed' veya 'Cancelled' olanlar "BOŞ" sayılır.
function checkStaffAvailability($staffId, $date, $time) {
    global $conn;
    try {
        if (empty($staffId)) return true; // Personel seçilmediyse genel randevu, her zaman uygun kabul edelim (veya iş mantığına göre değişir)

        $stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE staff_id = ? AND appointment_date = ? AND appointment_time = ? AND status IN ('Pending', 'Approved')");
        $stmt->execute([$staffId, $date, $time]);
        $count = $stmt->fetchColumn();
        
        // Eğer count 0 ise (Pending veya Approved yoksa) MÜSAİTTİR (true).
        return $count == 0; 
    } catch (PDOException $e) {
        return false;
    }
}

// KRİTİK GÜNCELLEME: Günlük Müsaitlik Listesi (Modal İçin)
// Sadece 'Pending' ve 'Approved' olanları 'full' (kırmızı) olarak işaretler.
function getDailyAvailability($date) {
    global $conn;
    $startHour = 9;
    $endHour = 20;
    
    try {
        $staffs = fetchStaff();
        
        // Sadece DOLU sayılan statüleri çekiyoruz
        $stmt = $conn->prepare("SELECT staff_id, appointment_time FROM Appointments WHERE appointment_date = ? AND status IN ('Pending', 'Approved')");
        $stmt->execute([$date]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $availability = [];

        foreach ($staffs as $staff) {
            $staffSchedule = [];
            $staffId = $staff['id'];
            
            $busyTimes = array_map(function($b) { return substr($b['appointment_time'], 0, 5); }, array_filter($bookings, function($b) use ($staffId) { return $b['staff_id'] == $staffId; }));

            for ($h = $startHour; $h < $endHour; $h++) {
                $timeSlot = sprintf("%02d:00", $h);
                $isBusy = in_array($timeSlot, $busyTimes);
                $staffSchedule[] = [
                    'time' => $timeSlot, 
                    'status' => $isBusy ? 'full' : 'free' // full=kırmızı, free=yeşil
                ];
            }
            $availability[] = ['staff_name' => $staff['username'], 'schedule' => $staffSchedule];
        }
        return $availability;
    } catch (PDOException $e) {
        return [];
    }
}

function createAppointment($postData) {
    global $conn;
    if (!$conn) return ["status" => false, "message" => "Veritabanı bağlantısı yok."];
    
    if(empty($postData['date']) || empty($postData['time'])) {
        return ["status" => false, "message" => "Lütfen tarih ve saat seçiniz."];
    }

    // Personel seçildiyse DOLULUK KONTROLÜ YAP
    if (!empty($postData['staff_id'])) {
        if (!checkStaffAvailability($postData['staff_id'], $postData['date'], $postData['time'])) {
            return ["status" => false, "message" => "Seçtiğiniz personel bu tarih ve saatte DOLU (Bekliyor veya Onaylanmış randevusu var)."];
        }
    }

    try {
        $staffId = !empty($postData['staff_id']) ? $postData['staff_id'] : null;
        $sql = "INSERT INTO Appointments (customer_name, customer_phone, service_id, appointment_date, appointment_time, staff_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([$postData['name'], $postData['phone'], $postData['service'], $postData['date'], $postData['time'], $staffId]);
        return $result ? ["status" => true, "message" => "Randevunuz başarıyla oluşturuldu."] : ["status" => false, "message" => "Hata oluştu."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Veritabanı Hatası: " . $e->getMessage()];
    }
}

function checkAdminLogin($username, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password_hash'])) {
            return ["status" => true, "user" => $user];
        } else {
            return ["status" => false, "message" => "Kullanıcı adı veya şifre hatalı."];
        }
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Giriş hatası: " . $e->getMessage()];
    }
}

function fetchUsers() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM Users ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function fetchStaff() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM Users WHERE role_type = 2 ORDER BY id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function addUser($username, $password, $roleType = 2) {
    global $conn;
    try {
        $check = $conn->prepare("SELECT count(*) FROM Users WHERE username = ?");
        $check->execute([$username]);
        if($check->fetchColumn() > 0) { return ["status" => false, "message" => "Bu kullanıcı adı zaten var."]; }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Users (username, password_hash, role_type) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $roleType]);
        return ["status" => true, "message" => "Kullanıcı oluşturuldu."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

function deleteUser($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("DELETE FROM Users WHERE id = ?");
        $stmt->execute([$id]);
        return ["status" => true, "message" => "Kullanıcı silindi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Silinemedi."];
    }
}

function updateUserPassword($id, $newPassword) {
    global $conn;
    try {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);
        return ["status" => true, "message" => "Şifre güncellendi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}
?>