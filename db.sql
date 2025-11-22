-- 1. Ayarlar Tablosu
CREATE TABLE IF NOT EXISTS Settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Varsayılan Ayarlar
INSERT IGNORE INTO Settings (setting_key, setting_value) VALUES 
('hero_title', 'Sadece Bir Tıraş Değil, <br> <span class="text-gold-400 italic">Bir Deneyim.</span>'),
('hero_subtitle', 'Profesyonel Erkek Bakımı'),
('about_title', 'Ustalık ve Modernlik'),
('about_text', 'Elite Cuts, geleneksel berber kültürünü modern dokunuşlarla harmanlayan...'),
('site_title', 'Elite Cuts | Profesyonel Erkek Kuaförü'),
('site_description', 'Profesyonel saç kesimi, sakal tıraşı ve bakım hizmetleri.'),
('site_keywords', 'kuaför, berber, saç kesimi, istanbul, damat tıraşı'),
('theme_color_primary', '#D4AF37'),
('theme_color_secondary', '#121212'),
('site_favicon', ''),
('site_logo_text', 'ELITE<span class="text-gold-400">CUTS</span>'),
('about_image', '');

-- 2. Hizmetler Tablosu
CREATE TABLE IF NOT EXISTS Services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    duration INT DEFAULT 30 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Örnek Hizmetler
INSERT INTO Services (name, description, price) VALUES 
('Saç Kesimi', 'Yıkama ve fön dahil modern kesim.', 300),
('Sakal Tıraşı', 'Sıcak havlu ve ustura ile.', 150),
('VIP Bakım', 'Komple bakım, maske ve masaj.', 600);

-- 3. Galeri Tablosu
CREATE TABLE IF NOT EXISTS Gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url TEXT NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- 4. Kullanıcılar Tablosu (Personel ve Admin)
CREATE TABLE IF NOT EXISTS Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_type INT DEFAULT 2 -- 1: Admin, 2: Personel
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Admin Ekle (Şifre: 1234)
INSERT IGNORE INTO Users (username, password_hash, role_type) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- 5. Randevular Tablosu
CREATE TABLE IF NOT EXISTS Appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    service_id INT,
    staff_id INT NULL, -- Personel seçimi (NULL ise farketmez)
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending', -- Pending, Approved, Completed, Cancelled
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES Services(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_id) REFERENCES Users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;