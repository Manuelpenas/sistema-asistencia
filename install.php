<?php
session_start();
$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($step == 1) {
        $host = $_POST['host'];
        $dbname = $_POST['dbname'];
        $user = $_POST['user'];
        $pass = $_POST['pass'];

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "
            CREATE TABLE IF NOT EXISTS config (
                id INT PRIMARY KEY AUTO_INCREMENT,
                clave VARCHAR(100) UNIQUE,
                valor TEXT
            );

            CREATE TABLE IF NOT EXISTS asistencias (
                id INT PRIMARY KEY AUTO_INCREMENT,
                dni VARCHAR(20),
                nombres VARCHAR(255),
                fecha DATE,
                curso VARCHAR(255),
                cc DECIMAL(5,2),
                mp DECIMAL(5,2),
                nota DECIMAL(5,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS perfiles (
                id INT PRIMARY KEY AUTO_INCREMENT,
                dni VARCHAR(20) UNIQUE,
                nombres VARCHAR(255),
                cc DECIMAL(5,2),
                mp DECIMAL(5,2)
            );

            CREATE TABLE IF NOT EXISTS cursos (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(255) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS inscripciones (
                id INT PRIMARY KEY AUTO_INCREMENT,
                dni VARCHAR(20),
                curso VARCHAR(255),
                fecha DATE,
                cc DECIMAL(5,2),
                mp DECIMAL(5,2),
                UNIQUE KEY unique_insc (dni, curso, fecha)
            );

            INSERT IGNORE INTO config (clave, valor) VALUES
            ('password', 'D3yf0rE1RL'),
            ('logo', 'assets/logo.svg'),
            ('favicon', 'assets/favicon.svg');
            ";

            $pdo->exec($sql);

            $config_content = "<?php
define('DB_HOST', '$host');
define('DB_NAME', '$dbname');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
?>";
            file_put_contents('config_db.php', $config_content);

            $step = 2;
            $success = 'Base de datos configurada correctamente.';
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Sistema de Asistencia</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f9f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .install-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,100,0,0.1); width: 100%; max-width: 500px; }
        h1 { color: #1b5e20; margin-bottom: 30px; font-weight: 300; }
        .step { color: #4caf50; font-size: 14px; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #c8e6c9; border-radius: 6px; font-size: 14px; }
        input:focus { outline: none; border-color: #4caf50; }
        button { width: 100%; padding: 14px; background: #1b5e20; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 20px; }
        button:hover { background: #2e7d32; }
        .error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin: 10px 0; }
        .success { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 6px; margin: 10px 0; }
        .info { background: #e3f2fd; color: #1565c0; padding: 15px; border-radius: 6px; margin: 15px 0; font-size: 13px; }
        a.btn { display: block; text-align: center; padding: 14px; background: #4caf50; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
    </style>
</head>
<body>
<div class="install-box">
    <h1>Sistema de Asistencia</h1>
    <div class="step">Paso <?php echo $step; ?> de 2</div>

    <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>

    <?php if ($step == 1): ?>
    <form method="POST">
        <input type="text" name="host" placeholder="Host (localhost)" value="localhost" required>
        <input type="text" name="dbname" placeholder="Nombre de la Base de Datos" required>
        <input type="text" name="user" placeholder="Usuario MySQL" required>
        <input type="password" name="pass" placeholder="Contraseña MySQL">
        <button type="submit">Configurar Base de Datos</button>
    </form>
    <?php elseif ($step == 2): ?>
    <div class="info">
        <strong>Instalación completada.</strong><br>
        El archivo config_db.php ha sido creado.<br>
        Contraseña configurada: <code>D3yf0rE1RL</code>
    </div>
    <a href="index.php" class="btn">Ir al Sistema</a>
    <?php endif; ?>
</div>
</body>
</html>
