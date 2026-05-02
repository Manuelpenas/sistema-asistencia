<?php
session_start();
require_once 'functions.php';

if (isset($_SESSION['auth']) && $_SESSION['auth'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $correct_password = getConfig('password') ?: 'D3yf0rE1RL';

    if ($password === $correct_password) {
        $_SESSION['auth'] = true;
        header('Location: config.php');
        exit;
    } else {
        $error = 'Contraseña incorrecta';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Sistema de Asistencia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0fdf0; height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-box { background: white; padding: 50px 40px; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,100,0,0.12); width: 100%; max-width: 400px; }
        .logo-area { text-align: center; margin-bottom: 40px; }
        .logo-area img { height: 60px; margin-bottom: 15px; }
        .logo-area h1 { color: #1b5e20; font-weight: 300; font-size: 24px; letter-spacing: 1px; }
        input { width: 100%; padding: 14px; margin: 12px 0; border: 1px solid #c8e6c9; border-radius: 8px; font-size: 15px; }
        input:focus { outline: none; border-color: #4caf50; box-shadow: 0 0 0 3px rgba(76,175,80,0.1); }
        button { width: 100%; padding: 14px; background: #1b5e20; color: white; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; margin-top: 10px; transition: background 0.3s; }
        button:hover { background: #2e7d32; }
        .error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="logo-area">
        <img src="<?php echo getConfig('logo') ?: 'assets/logo.png'; ?>" alt="Logo">
        <h1>Sistema de Asistencia</h1>
    </div>

    <?php if ($error): ?>
    <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="password" name="password" placeholder="Contraseña de acceso" required autofocus>
        <button type="submit">INGRESAR</button>
    </form>
</div>
</body>
</html>
