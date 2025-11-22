<?php
// AYAR FONKSİYONLARI
function getSetting($key)
{
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

function updateSiteSettings($postData, $filesData = null)
{
    global $conn;
    $settings = ['hero_title', 'hero_subtitle', 'about_title', 'about_text', 'site_title', 'site_description', 'site_keywords', 'site_logo_text', 'theme_color_primary', 'theme_color_secondary'];
    try {
        $sql = "INSERT INTO Settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);
        foreach ($settings as $key) {
            if (isset($postData[$key])) $stmt->execute([$key, $postData[$key]]);
        }

        // Dosya yüklemeleri
        $uploads = [
            'site_favicon' => 'favicon',
            'about_image' => 'about_bg_' . time(),
            'hero_image' => 'hero_bg_' . time()
        ];

        foreach ($uploads as $inputName => $prefix) {
            if (isset($filesData[$inputName]) && $filesData[$inputName]['error'] == 0) {
                $ext = strtolower(pathinfo($filesData[$inputName]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'ico', 'webp'])) {
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                    $fileName = $prefix . '.' . $ext;
                    if (move_uploaded_file($filesData[$inputName]['tmp_name'], $uploadDir . $fileName)) {
                        $stmt->execute([$inputName, 'uploads/' . $fileName]);
                    }
                }
            }
        }
        return ["status" => true, "message" => "Ayarlar güncellendi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

// HİZMETLER
function fetchServices()
{
    global $conn;
    if (!$conn) return [];
    return $conn->query("SELECT * FROM Services")->fetchAll(PDO::FETCH_ASSOC);
}

function addService($name, $price, $desc, $duration)
{
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO Services (name, price, description, duration) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $desc, $duration]);
        return ["status" => true, "message" => "Hizmet eklendi."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

function deleteService($id)
{
    global $conn;
    $conn->prepare("DELETE FROM Services WHERE id = ?")->execute([$id]);
}

// GALERİ
function fetchGallery()
{
    global $conn;
    return $conn->query("SELECT * FROM Gallery ORDER BY uploaded_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}

function addGalleryImage($fileData)
{
    global $conn;
    if ($fileData['error'] != 0) return ["status" => false, "message" => "Dosya hatası."];
    $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
    $uploadDir = __DIR__ . '/../uploads/gallery/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $fileName = uniqid() . '.' . $ext;
    if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $fileName)) {
        $conn->prepare("INSERT INTO Gallery (image_url) VALUES (?)")->execute(['uploads/gallery/' . $fileName]);
        return ["status" => true, "message" => "Resim eklendi."];
    }
    return ["status" => false, "message" => "Yükleme başarısız."];
}

function deleteGalleryImage($id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT image_url FROM Gallery WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($img) {
        if (file_exists(__DIR__ . '/../' . $img['image_url'])) unlink(__DIR__ . '/../' . $img['image_url']);
        $conn->prepare("DELETE FROM Gallery WHERE id = ?")->execute([$id]);
        return ["status" => true, "message" => "Silindi."];
    }
    return ["status" => false, "message" => "Bulunamadı."];
}

// RANDEVULAR & PERSONEL
function fetchAppointments()
{
    global $conn;
    $sql = "SELECT a.*, u.username as staff_name, s.name as service_name, s.duration 
            FROM Appointments a 
            LEFT JOIN Users u ON a.staff_id = u.id 
            LEFT JOIN Services s ON a.service_id = s.id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function updateAppointmentStatus($id, $status)
{
    global $conn;
    $conn->prepare("UPDATE Appointments SET status = ? WHERE id = ?")->execute([$status, $id]);
}

// --- MÜSAİTLİK KONTROLÜ (YENİ MANTIK: SÜRE BAZLI ÇAKIŞMA) ---
function checkTimeOverlap($staffId, $date, $newStartTime, $newDurationMinutes)
{
    global $conn;

    // Personel seçilmediyse genel randevu kabul et
    if (empty($staffId)) return false;

    // 1. O personelin o tarihteki tüm aktif randevularını çek (Süresiyle birlikte)
    $sql = "SELECT a.appointment_time, s.duration 
            FROM Appointments a
            LEFT JOIN Services s ON a.service_id = s.id
            WHERE a.staff_id = ? 
            AND a.appointment_date = ? 
            AND a.status IN ('Pending', 'Approved')";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$staffId, $date]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Yeni randevunun başlangıç ve bitiş zaman damgaları
    $newStartTimestamp = strtotime("$date $newStartTime");
    $newEndTimestamp = $newStartTimestamp + ($newDurationMinutes * 60);

    foreach ($appointments as $app) {
        // Mevcut randevunun başlangıç ve bitişi
        $existingStartTimestamp = strtotime("$date " . $app['appointment_time']);
        // Eğer hizmet süresi yoksa varsayılan 30 dk al
        $existingDuration = $app['duration'] ? $app['duration'] : 30;
        $existingEndTimestamp = $existingStartTimestamp + ($existingDuration * 60);

        // ÇAKIŞMA MANTIĞI:
        // Yeni randevu bitişi mevcut başlangıçtan büyükse VE yeni başlangıç mevcut bitişten küçükse çakışma vardır.
        if ($newStartTimestamp < $existingEndTimestamp && $newEndTimestamp > $existingStartTimestamp) {
            return true; // ÇAKIŞMA VAR!
        }
    }

    return false; // Çakışma yok, uygun.
}

// MODAL İÇİN GÜNLÜK DURUM (30 Dk'lık dilimler halinde)
function getDailyAvailability($date)
{
    global $conn;
    $slots = [];
    // 09:00'dan 20:00'a kadar 30'ar dakika
    $start = strtotime('09:00');
    $end = strtotime('20:00');
    while ($start < $end) {
        $slots[] = date('H:i', $start);
        $start = strtotime('+30 minutes', $start);
    }

    try {
        $staffs = fetchStaff();

        // O tarihteki tüm randevuları çek
        $sql = "SELECT a.staff_id, a.appointment_time, s.duration 
                FROM Appointments a
                LEFT JOIN Services s ON a.service_id = s.id
                WHERE a.appointment_date = ? 
                AND a.status IN ('Pending', 'Approved')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$date]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $availability = [];

        foreach ($staffs as $staff) {
            $staffSchedule = [];
            $staffId = $staff['id'];

            // Sadece bu personelin randevuları
            $staffApps = array_filter($appointments, function ($a) use ($staffId) {
                return $a['staff_id'] == $staffId;
            });

            foreach ($slots as $slotTime) {
                $isBusy = false;
                // Slotun başlangıç ve bitişi (görselleştirme için 30dk varsayalım)
                $slotStart = strtotime("$date $slotTime");
                $slotEnd = $slotStart + (30 * 60);

                foreach ($staffApps as $app) {
                    $appStart = strtotime("$date " . $app['appointment_time']);
                    $duration = $app['duration'] ? $app['duration'] : 30;
                    $appEnd = $appStart + ($duration * 60);

                    // Eğer slot herhangi bir randevunun süresi içine denk geliyorsa DOLU işaretle
                    if ($slotStart < $appEnd && $slotEnd > $appStart) {
                        $isBusy = true;
                        break;
                    }
                }

                $staffSchedule[] = [
                    'time' => $slotTime,
                    'status' => $isBusy ? 'full' : 'free'
                ];
            }

            $availability[] = [
                'staff_name' => $staff['username'],
                'schedule' => $staffSchedule
            ];
        }
        return $availability;
    } catch (PDOException $e) {
        return [];
    }
}

function createAppointment($postData)
{
    global $conn;
    if (!$conn) return ["status" => false, "message" => "DB Hatası"];

    if (empty($postData['date']) || empty($postData['time']) || empty($postData['service'])) {
        return ["status" => false, "message" => "Eksik bilgi."];
    }

    // Seçilen hizmetin süresini bul
    $stmt = $conn->prepare("SELECT duration FROM Services WHERE id = ?");
    $stmt->execute([$postData['service']]);
    $serviceDuration = $stmt->fetchColumn();
    $serviceDuration = $serviceDuration ? $serviceDuration : 30; // Varsayılan 30dk

    // Çakışma Kontrolü
    if (!empty($postData['staff_id'])) {
        if (checkTimeOverlap($postData['staff_id'], $postData['date'], $postData['time'], $serviceDuration)) {
            return ["status" => false, "message" => "Seçilen personel o saat aralığında dolu (İşlem süresi: $serviceDuration dk)."];
        }
    }

    try {
        $staffId = !empty($postData['staff_id']) ? $postData['staff_id'] : null;
        $sql = "INSERT INTO Appointments (customer_name, customer_phone, service_id, appointment_date, appointment_time, staff_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$postData['name'], $postData['phone'], $postData['service'], $postData['date'], $postData['time'], $staffId]);
        return ["status" => true, "message" => "Randevunuz oluşturuldu."];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata: " . $e->getMessage()];
    }
}

// KULLANICILAR
function checkAdminLogin($user, $pass)
{
    global $conn;
    $u = $conn->prepare("SELECT * FROM Users WHERE username = ?");
    $u->execute([$user]);
    $data = $u->fetch(PDO::FETCH_ASSOC);
    if ($data && password_verify($pass, $data['password_hash'])) return ["status" => true, "user" => $data];
    return ["status" => false, "message" => "Hatalı giriş."];
}
function fetchUsers()
{
    global $conn;
    return $conn->query("SELECT * FROM Users")->fetchAll(PDO::FETCH_ASSOC);
}
function fetchStaff()
{
    global $conn;
    return $conn->query("SELECT * FROM Users WHERE role_type = 2")->fetchAll(PDO::FETCH_ASSOC);
}
function addUser($u, $p, $r)
{
    global $conn;
    $h = password_hash($p, PASSWORD_DEFAULT);
    try {
        $conn->prepare("INSERT INTO Users (username, password_hash, role_type) VALUES (?,?,?)")->execute([$u, $h, $r]);
        return ["status" => true, "message" => "Eklendi"];
    } catch (PDOException $e) {
        return ["status" => false, "message" => "Hata"];
    }
}
function deleteUser($id)
{
    global $conn;
    $conn->prepare("DELETE FROM Users WHERE id = ?")->execute([$id]);
    return ["status" => true, "message" => "Silindi"];
}
function updateUserPassword($id, $p)
{
    global $conn;
    $h = password_hash($p, PASSWORD_DEFAULT);
    $conn->prepare("UPDATE Users SET password_hash = ? WHERE id = ?")->execute([$h, $id]);
    return ["status" => true, "message" => "Güncellendi"];
}
