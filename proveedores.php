<?php
require_once 'functions.php';
checkAuth();

$page_title = 'Proveedores';
$pdo = getDB();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_proveedores'])) {
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
        }
    }
}

$proveedores = $pdo->query("SELECT * FROM proveedores ORDER BY fecha DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
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
    <h2>Cargar Proveedores</h2>
    <p style="color:#666; font-size:14px; margin-bottom:15px;">Formato: RUC | Nombre | Razon Social | Linea | Curso | Fecha | Tiempos | Nota</p>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="proveedores_file" accept=".csv,.xls,.xlsx" required>
        <button type="submit" name="upload_proveedores" class="btn" style="margin-top:15px;">Cargar Proveedores</button>
    </form>
</div>

<div class="card">
    <h2>Lista de Proveedores (<?php echo $total_proveedores; ?> registros)</h2>
    <table>
        <thead>
            <tr>
                <th>RUC</th>
                <th>Nombre</th>
                <th>Razón Social</th>
                <th>Línea</th>
                <th>Curso</th>
                <th>Fecha</th>
                <th>Tiempos</th>
                <th>Nota</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proveedores as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['ruc']); ?></td>
                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                <td><?php echo htmlspecialchars($p['razon_social']); ?></td>
                <td><?php echo htmlspecialchars($p['linea']); ?></td>
                <td><?php echo htmlspecialchars($p['curso']); ?></td>
                <td><?php echo formatDate($p['fecha']); ?></td>
                <td><?php echo number_format($p['tiempos'] ?: 0, 1); ?></td>
                <td><strong><?php echo number_format($p['nota'] ?: 0, 1); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require 'footer.php'; ?>
