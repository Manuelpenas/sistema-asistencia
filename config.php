<?php
session_start();
require_once 'functions.php';
checkAuth();

$page_title = 'Configuración';
$pdo = getDB();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $correct = getConfig('password') ?: 'D3yf0rE1RL';

        if ($current !== $correct) {
            $error = 'Contraseña actual incorrecta';
        } elseif ($new !== $confirm) {
            $error = 'Las contraseñas no coinciden';
        } else {
            setConfig('password', $new);
            $success = 'Contraseña actualizada correctamente';
        }
    }

    if (isset($_POST['upload_excel'])) {
        if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] == 0) {
            $file = $_FILES['excel_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

            $count = 0;
            $stmt = $pdo->prepare("INSERT INTO asistencias (dni, nombres, fecha, curso, cc, mp, nota) VALUES (?, ?, ?, ?, ?, ?, ?)");

            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE dni = ? AND curso = ? AND fecha = ?");
            if ($ext === 'csv') {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    $i = 0;
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $i++;
                        if ($i == 1) continue;
                        if (count($data) < 7) continue;
                        $check_stmt->execute([$data[0], $data[3], $data[2]]);
                        if ($check_stmt->fetchColumn() == 0) {
                            $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]]);
                            $count++;
                        }
                    }
                    fclose($handle);
                }
                $success = "Se importaron $count registros desde CSV correctamente";
            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                if (class_exists('ZipArchive')) {
                    require_once 'SimpleXLSX.php';
                    $xlsx = SimpleXLSX::parse($file);
                    if ($xlsx) {
                        foreach ($xlsx->rows() as $i => $row) {
                            if ($i == 0) continue;
                            if (count($row) < 7) continue;
                            $check_stmt->execute([$row[0], $row[3], $row[2]]);
                            if ($check_stmt->fetchColumn() == 0) {
                                $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]]);
                                $count++;
                            }
                        }
                        $success = "Se importaron $count registros desde Excel correctamente";
                    } else {
                        $error = 'Error al leer el archivo Excel';
                    }
                } else {
                    $error = 'ZipArchive no disponible. Suba el archivo en formato CSV.';
                }
            } else {
                $error = 'Formato no válido. Use .csv, .xls o .xlsx';
            }
        }
    }

    if (isset($_POST['upload_perfiles'])) {
        if (isset($_FILES['perfiles_file']) && $_FILES['perfiles_file']['error'] == 0) {
            $file = $_FILES['perfiles_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['perfiles_file']['name'], PATHINFO_EXTENSION));
            $count = 0;
            $stmt = $pdo->prepare("INSERT OR REPLACE INTO perfiles (dni, nombres, cc, mp) VALUES (?, ?, ?, ?)");

            if ($ext === 'csv') {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    $i = 0;
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $i++;
                        if ($i == 1) continue;
                        if (count($data) < 4) continue;
                        $stmt->execute([$data[0], $data[1], $data[2], $data[3]]);
                        $count++;
                    }
                    fclose($handle);
                }
                $success = "Se importaron $count perfiles correctamente";
            } elseif (in_array($ext, ['xls', 'xlsx']) && class_exists('ZipArchive')) {
                require_once 'SimpleXLSX.php';
                $xlsx = SimpleXLSX::parse($file);
                if ($xlsx) {
                    foreach ($xlsx->rows() as $i => $row) {
                        if ($i == 0) continue;
                        if (count($row) < 4) continue;
                        $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
                        $count++;
                    }
                    $success = "Se importaron $count perfiles desde Excel correctamente";
                }
            }
        }
    }

    if (isset($_POST['upload_cursos'])) {
        if (isset($_FILES['cursos_file']) && $_FILES['cursos_file']['error'] == 0) {
            $file = $_FILES['cursos_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['cursos_file']['name'], PATHINFO_EXTENSION));
            $count = 0;
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO cursos (nombre) VALUES (?)");

            if ($ext === 'csv') {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        if (empty($data[0])) continue;
                        $stmt->execute([$data[0]]);
                        $count++;
                    }
                    fclose($handle);
                }
                $success = "Se importaron $count cursos correctamente";
            } elseif (in_array($ext, ['xls', 'xlsx']) && class_exists('ZipArchive')) {
                require_once 'SimpleXLSX.php';
                $xlsx = SimpleXLSX::parse($file);
                if ($xlsx) {
                    foreach ($xlsx->rows() as $row) {
                        if (empty($row[0])) continue;
                        $stmt->execute([$row[0]]);
                        $count++;
                    }
                    $success = "Se importaron $count cursos desde Excel correctamente";
                }
            }
        }
    }

    if (isset($_POST['upload_proveedores'])) {
        if (isset($_FILES['proveedores_file']) && $_FILES['proveedores_file']['error'] == 0) {
            $file = $_FILES['proveedores_file']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['proveedores_file']['name'], PATHINFO_EXTENSION));
            $count = 0;
            $stmt = $pdo->prepare("INSERT OR IGNORE INTO proveedores (ruc, nombre, razon_social, linea, curso, fecha, tiempos, nota) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            if ($ext === 'csv') {
                if (($handle = fopen($file, "r")) !== FALSE) {
                    $i = 0;
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $i++;
                        if ($i == 1) continue;
                        if (count($data) < 8) continue;
                        $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7]]);
                        $count++;
                    }
                    fclose($handle);
                }
                $success = "Se importaron $count proveedores correctamente";
            } elseif (in_array($ext, ['xls', 'xlsx']) && class_exists('ZipArchive')) {
                require_once 'SimpleXLSX.php';
                $xlsx = SimpleXLSX::parse($file);
                if ($xlsx) {
                    foreach ($xlsx->rows() as $i => $row) {
                        if ($i == 0) continue;
                        if (count($row) < 8) continue;
                        $stmt->execute([$row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]]);
                        $count++;
                    }
                    $success = "Se importaron $count proveedores desde Excel correctamente";
                }
            }
        }
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'gif', 'svg'])) {
            $target = 'assets/logo.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], $target);
            setConfig('logo', $target);
            $success = 'Logo actualizado correctamente';
        }
    }

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] == 0) {
        $ext = pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), ['ico', 'png'])) {
            $target = 'assets/favicon.' . $ext;
            move_uploaded_file($_FILES['favicon']['tmp_name'], $target);
            setConfig('favicon', $target);
            $success = 'Favicon actualizado correctamente';
        }
    }

    if (isset($_POST['clear_data'])) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver == 'mysql') {
            $pdo->exec("TRUNCATE TABLE asistencias");
            $pdo->exec("TRUNCATE TABLE perfiles");
            $pdo->exec("TRUNCATE TABLE cursos");
            $pdo->exec("TRUNCATE TABLE inscripciones");
            $pdo->exec("TRUNCATE TABLE proveedores");
        } else {
            $pdo->exec("DELETE FROM asistencias");
            $pdo->exec("DELETE FROM perfiles");
            $pdo->exec("DELETE FROM cursos");
            $pdo->exec("DELETE FROM inscripciones");
            $pdo->exec("DELETE FROM proveedores");
        }
        $success = 'Todos los datos han sido eliminados';
    }
}

$total_registros = $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
$total_perfiles = $pdo->query("SELECT COUNT(*) FROM perfiles")->fetchColumn();
$total_cursos = $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();
$total_inscripciones = $pdo->query("SELECT COUNT(*) FROM inscripciones")->fetchColumn();
$total_proveedores = $pdo->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
?>
<?php require 'header.php'; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <h2>Subir Archivo de Datos</h2>
    <p style="color:#666; font-size:14px; margin-bottom:15px;">Formato requerido: DNI | Nombres | Fecha | Curso | CC | MP | Nota</p>
    <div style="background:#f1f8e9; padding:12px; border-radius:6px; margin-bottom:15px; font-size:13px; color:#33691e;">
        Descargue la <a href="template.csv" download style="color:#1b5e20; font-weight:600;">plantilla CSV</a> como referencia
    </div>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="excel_file" accept=".csv,.xls,.xlsx" required>
        <button type="submit" name="upload_excel" class="btn" style="margin-top:15px;">Cargar Archivo (Asistencias)</button>
    </form>
</div>

<div class="card">
    <h2>Subir Perfiles (DNI | Nombres | CC | MP)</h2>
    <p style="color:#666; font-size:14px; margin-bottom:15px;">Cargue el archivo con los perfiles de colaboradores</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="perfiles_file" accept=".csv,.xls,.xlsx" required>
        <button type="submit" name="upload_perfiles" class="btn" style="margin-top:15px;">Cargar Perfiles</button>
    </form>
</div>

<div class="card">
    <h2>Subir Cursos</h2>
    <p style="color:#666; font-size:14px; margin-bottom:15px;">Formato: Solo una columna con el nombre del curso</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="cursos_file" accept=".csv,.xls,.xlsx" required>
        <button type="submit" name="upload_cursos" class="btn" style="margin-top:15px;">Cargar Cursos</button>
    </form>
</div>

<div class="card">
    <h2>Subir Proveedores (RUC | Nombre | Razon Social | Linea | Curso | Fecha | Tiempos | Nota)</h2>
    <p style="color:#666; font-size:14px; margin-bottom:15px;">Cargue el archivo con los datos de proveedores</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="proveedores_file" accept=".csv,.xls,.xlsx" required>
        <button type="submit" name="upload_proveedores" class="btn" style="margin-top:15px;">Cargar Proveedores</button>
    </form>
</div>

<div class="card">
    <h2>Personalización</h2>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:30px;">
        <div>
            <h3 style="color:#2e7d32; font-weight:400; margin-bottom:15px;">Logo del Sistema</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="logo" accept=".png,.jpg,.jpeg,.gif,.svg">
                <button type="submit" class="btn btn-secondary" style="margin-top:10px;">Subir Logo</button>
            </form>
            <div style="margin-top:15px; padding:15px; background:#f9f9f9; border-radius:8px; text-align:center;">
                <img src="<?php echo getConfig('logo') ?: 'assets/logo.png'; ?>" style="max-height:60px;">
            </div>
        </div>
        <div>
            <h3 style="color:#2e7d32; font-weight:400; margin-bottom:15px;">Favicon</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="favicon" accept=".ico,.png">
                <button type="submit" class="btn btn-secondary" style="margin-top:10px;">Subir Favicon</button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <h2>Cambiar Contraseña</h2>
    <form method="POST">
        <input type="password" name="current_password" placeholder="Contraseña actual" required>
        <input type="password" name="new_password" placeholder="Nueva contraseña" required>
        <input type="password" name="confirm_password" placeholder="Confirmar nueva contraseña" required>
        <button type="submit" name="change_password" class="btn">Actualizar Contraseña</button>
    </form>
</div>

<div class="card">
    <h2>Gestión de Datos</h2>
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:15px; margin-bottom:15px;">
        <div style="background:#e8f5e9; padding:15px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; color:#1b5e20; font-weight:300;"><?php echo $total_registros; ?></div>
            <div style="font-size:12px; color:#555;">Asistencias</div>
        </div>
        <div style="background:#e3f2fd; padding:15px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; color:#1565c0; font-weight:300;"><?php echo $total_perfiles; ?></div>
            <div style="font-size:12px; color:#555;">Perfiles</div>
        </div>
        <div style="background:#fff3e0; padding:15px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; color:#e65100; font-weight:300;"><?php echo $total_cursos; ?></div>
            <div style="font-size:12px; color:#555;">Cursos</div>
        </div>
        <div style="background:#f3e5f5; padding:15px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; color:#6a1b9a; font-weight:300;"><?php echo $total_inscripciones; ?></div>
            <div style="font-size:12px; color:#555;">Inscripciones</div>
        </div>
        <div style="background:#fce4ec; padding:15px; border-radius:8px; text-align:center;">
            <div style="font-size:24px; color:#c62828; font-weight:300;"><?php echo $total_proveedores; ?></div>
            <div style="font-size:12px; color:#555;">Proveedores</div>
        </div>
    </div>
    <form method="POST" onsubmit="return confirmDelete('¿Está seguro de eliminar TODOS los datos? Esta acción no se puede deshacer.');">
        <button type="submit" name="clear_data" class="btn" style="background:#c62828;">Eliminar Todos los Datos</button>
    </form>
</div>

<?php require 'footer.php'; ?>
