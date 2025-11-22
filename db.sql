
CREATE TABLE Settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(1000)
);

    INSERT INTO Settings (setting_key, setting_value) VALUES 
    ('hero_title', 'Sadece Bir Tıraş Değil, <br> <span class="gold-text italic">Bir Deneyim.</span>'),
    ('hero_subtitle', 'Profesyonel Erkek Bakımı'),
    ('about_title', 'Ustalık ve Modernlik'),
    ('about_text', 'Elite Cuts, geleneksel berber kültürünü modern dokunuşlarla harmanlayan...'),
    ('site_title', 'Elite Cuts | Profesyonel Erkek Kuaförü'),
    ('site_description', 'Profesyonel saç kesimi, sakal tıraşı ve bakım hizmetleri.'),
    ('site_keywords', 'kuaför, berber, saç kesimi, istanbul, damat tıraşı'),
    ('theme_color_primary', '#D4AF37'),
    ('theme_color_secondary', '#121212'),
    ('site_favicon', ''),
    ('site_navbar_title', '');

CREATE TABLE Services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    price DECIMAL(10, 2) NOT NULL,
    duration INT DEFAULT 30 
);

INSERT INTO Services (name, description, price) VALUES 
('Saç Kesimi', 'Yıkama ve fön dahil modern kesim.', 300),
('Sakal Tıraşı', 'Sıcak havlu ve ustura ile.', 150),
('VIP Bakım', 'Komple bakım, maske ve masaj.', 600);

CREATE TABLE Gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url TEXT NOT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE Appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    service_id INT,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending', 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES Services(id)
);


CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role_type INT DEFAULT 1

);

-- Admin  (Şifre: password)
INSERT INTO Users (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');