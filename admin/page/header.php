<?php
session_start();

// DÜZELTME VE AÇIKLAMA:
// Dosyamızın yeri: /admin/page/header.php
// Hedef dosya:     /include/config.php

// 1. "../" ile "admin" klasörüne çıkarız.
// 2. "../" ile ana dizine (kuaforV2) çıkarız.
// 3. "/include/config.php" ile dosyayı buluruz.

// En garantili yöntem __DIR__ kullanmaktır, bu sayede "dosya nereden çağrılırsa çağrılsın" yol bozulmaz.
$base_path = __DIR__ . '/../../include';

if (file_exists($base_path . '/config.php')) {
    require_once $base_path . '/config.php';
    require_once $base_path . '/function.php';
} else {
    // Hata durumunda yolun nereye baktığını ekrana basar
    die("<div style='background:red; color:white; padding:20px;'>
            <b>HATA:</b> Config dosyası bulunamadı.<br>
            <b>Aranan Yol:</b> " . realpath($base_path) . "/config.php <br>
            <em>Lütfen include klasörünün ana dizinde olduğundan emin olun.</em>
         </div>");
}

// Oturum Kontrolü
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetim Paneli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #111827; }
        ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #D4AF37; }
    </style>
</head>
<body class="bg-gray-900 text-white font-sans antialiased overflow-hidden">
    <div class="flex h-screen">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 flex flex-col border-r border-gray-700 shadow-2xl z-20">
            <div class="h-20 flex items-center justify-center border-b border-gray-700 bg-gray-800/50 backdrop-blur">
                <h2 class="text-2xl font-bold text-yellow-500 tracking-widest">Yönetim<span class="text-white">Paneli</span></h2>
            </div>
            
            <nav class="flex-1 overflow-y-auto py-6 space-y-1 px-2">
                <a href="index.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 <?= $current_page == 'index.php' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-home w-6 text-lg <?= $current_page == 'index.php' ? 'text-black' : 'text-gray-400 group-hover:text-white' ?>"></i>
                    Özet / Dashboard
                </a>
                <a href="settings.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 <?= $current_page == 'settings.php' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-sliders-h w-6 text-lg <?= $current_page == 'settings.php' ? 'text-black' : 'text-gray-400 group-hover:text-white' ?>"></i>
                    Genel Ayarlar
                </a>
                <a href="services.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 <?= $current_page == 'services.php' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-cut w-6 text-lg <?= $current_page == 'services.php' ? 'text-black' : 'text-gray-400 group-hover:text-white' ?>"></i>
                    Hizmetler
                </a>
                <a href="appointments.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 <?= $current_page == 'appointments.php' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-calendar-check w-6 text-lg <?= $current_page == 'appointments.php' ? 'text-black' : 'text-gray-400 group-hover:text-white' ?>"></i>
                    Randevular
                </a>
                 <a href="gallery.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 <?= $current_page == 'gallery.php' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-calendar-check w-6 text-lg <?= $current_page == 'gallery.php' ? 'text-black' : 'text-gray-400 group-hover:text-white' ?>"></i>
                    Galeri
                </a>
                <a href="users.php" class="group flex items-center px-4 py-3 text-sm font-medium rounded-md transition-all duration-200 <?= $current_page == 'users.php' ? 'bg-yellow-500 text-black shadow-lg shadow-yellow-500/20' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <i class="fas fa-users w-6 text-lg <?= $current_page == 'users.php' ? 'text-black' : 'text-gray-400 group-hover:text-white' ?>"></i>
                    Kullanıcılar
                </a>
            </nav>

            <div class="p-4 border-t border-gray-700 bg-gray-800/50">
                <a href="../../index.php" target="_blank" class="flex items-center justify-center px-4 py-2 border border-gray-600 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-700 hover:text-white transition-colors mb-3">
                    <i class="fas fa-external-link-alt mr-2"></i> Siteyi Görüntüle
                </a>
                <a href="logout.php" class="flex items-center justify-center px-4 py-2 bg-red-600/10 border border-red-600/20 rounded-md text-sm font-medium text-red-500 hover:bg-red-600 hover:text-white transition-all duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i> Çıkış Yap
                </a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <main class="flex-1 overflow-y-auto bg-gray-900 relative">
            <div class="p-8 md:p-10 max-w-7xl mx-auto">