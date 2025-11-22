<?php
session_start();
require '../include/config.php';
require '../include/function.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $loginResult = checkAdminLogin($_POST['username'], $_POST['password']);
    
    if ($loginResult['status']) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = $loginResult['message'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Giriş</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="background:#121212; color:white; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; font-family:sans-serif;">
    <form method="POST" style="background:#1E1E1E; padding:40px; border-radius:10px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); width:100%; max-width:400px;">
        <h2 style="color:#D4AF37; text-align:center; margin-bottom:30px;">Yönetici Girişi</h2>
        
        <div style="margin-bottom:15px;">
            <input type="text" name="username" placeholder="Kullanıcı Adı" required 
                   style="display:block; width:100%; padding:12px; background:#2D2D2D; border:1px solid #444; color:white; border-radius:5px; box-sizing:border-box;">
        </div>
        
        <div style="margin-bottom:20px;">
            <input type="password" name="password" placeholder="Şifre" required 
                   style="display:block; width:100%; padding:12px; background:#2D2D2D; border:1px solid #444; color:white; border-radius:5px; box-sizing:border-box;">
        </div>
        
        <button type="submit" name="login" 
                style="width:100%; padding:12px; background:#D4AF37; border:none; border-radius:5px; font-weight:bold; cursor:pointer; transition:0.3s;">GİRİŞ YAP</button>
        
        <?php if (isset($error)) echo "<p style='color:#ff6b6b; text-align:center; margin-top:15px;'>$error</p>"; ?>
    </form>
</body>
</html>