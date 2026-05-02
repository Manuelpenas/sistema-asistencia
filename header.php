<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sistema de Asistencia</title>
    <?php $favicon = getConfig('favicon') ?: 'assets/favicon.svg'; ?>
    <link rel="icon" href="<?php echo $favicon; ?>" type="<?php echo strpos($favicon, '.svg') !== false ? 'image/svg+xml' : 'image/x-icon'; ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5fbf5; color: #1a1a1a; }

        .header { background: linear-gradient(135deg, #1b5e20, #2e7d32); color: white; padding: 0 40px; height: 70px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header .logo { display: flex; align-items: center; gap: 15px; }
        .header .logo img { height: 45px; width: auto; max-width: 200px; }
        .header .logo h1 { font-size: 20px; font-weight: 300; letter-spacing: 1px; }
        .header nav { display: flex; gap: 5px; }
        .header nav a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 8px 16px; border-radius: 6px; font-size: 14px; transition: all 0.3s; }
        .header nav a:hover, .header nav a.active { background: rgba(255,255,255,0.15); color: white; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 12px rgba(0,100,0,0.08); margin-bottom: 20px; }
        .card h2 { color: #1b5e20; font-weight: 400; margin-bottom: 20px; font-size: 22px; }

        .btn { display: inline-block; padding: 10px 24px; background: #2e7d32; color: white; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; text-decoration: none; transition: background 0.3s; }
        .btn:hover { background: #1b5e20; }
        .btn-secondary { background: #66bb6a; }
        .btn-secondary:hover { background: #4caf50; }

        input, select { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #c8e6c9; border-radius: 6px; font-size: 14px; transition: border 0.3s; }
        input:focus, select:focus { outline: none; border-color: #4caf50; box-shadow: 0 0 0 3px rgba(76,175,80,0.1); }

        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #e8f5e9; color: #1b5e20; padding: 12px; text-align: left; font-weight: 500; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        table td { padding: 12px; border-bottom: 1px solid #e8f5e9; font-size: 14px; }
        table tr:hover { background: #f1f8f1; }

        .alert { padding: 15px 20px; border-radius: 8px; margin: 15px 0; font-size: 14px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #4caf50; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #f44336; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,100,0,0.08); border-left: 4px solid #4caf50; }
        .stat-card .number { font-size: 32px; font-weight: 300; color: #1b5e20; }
        .stat-card .label { font-size: 13px; color: #666; margin-top: 5px; text-transform: uppercase; letter-spacing: 0.5px; }

        .search-box { display: flex; gap: 10px; margin: 20px 0; }
        .search-box input { flex: 1; margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="<?php echo getConfig('logo') ?: 'assets/logo.png'; ?>" alt="Logo">
            <h1>Sistema de Asistencia</h1>
        </div>
        <nav>
            <a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Dashboard</a>
            <a href="inscripcion.php" <?php echo basename($_SERVER['PHP_SELF']) == 'inscripcion.php' ? 'class="active"' : ''; ?>>Inscripción</a>
            <a href="asistencia.php" <?php echo basename($_SERVER['PHP_SELF']) == 'asistencia.php' ? 'class="active"' : ''; ?>>Asistencia</a>
            <a href="registros.php" <?php echo basename($_SERVER['PHP_SELF']) == 'registros.php' ? 'class="active"' : ''; ?>>Registros</a>
            <a href="consulta.php" <?php echo basename($_SERVER['PHP_SELF']) == 'consulta.php' ? 'class="active"' : ''; ?>>Consulta</a>
            <a href="config.php" <?php echo basename($_SERVER['PHP_SELF']) == 'config.php' ? 'class="active"' : ''; ?>>Configuración</a>
            <?php if (isset($_SESSION['auth']) && $_SESSION['auth'] === true): ?>
            <a href="logout.php">Salir</a>
            <?php endif; ?>
        </nav>
    </div>
    <div class="container">
