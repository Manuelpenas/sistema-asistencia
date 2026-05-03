<?php
require_once 'functions.php';
// checkAuth(); // Comentado: acceso público

$page_title = 'Inscripción';
$pdo = getDB();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inscribir'])) {
    $dni = trim($_POST['dni']);
    $curso = trim($_POST['curso']);
    $fecha = $_POST['fecha'];

    if (empty($dni) || empty($curso) || empty($fecha)) {
        $error = 'Todos los campos son obligatorios';
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM inscripciones WHERE dni = ? AND curso = ? AND fecha = ?");
        $check->execute([$dni, $curso, $fecha]);

        if ($check->fetchColumn() > 0) {
            $error = 'Este colaborador ya está inscrito en este curso para la fecha seleccionada';
        } else {
            $perfil = $pdo->prepare("SELECT cc, mp FROM perfiles WHERE dni = ?");
            $perfil->execute([$dni]);
            $data = $perfil->fetch(PDO::FETCH_ASSOC);

            $cc = $data ? $data['cc'] : 0;
            $mp = $data ? $data['mp'] : 0;

            $stmt = $pdo->prepare("INSERT INTO inscripciones (dni, curso, fecha, cc, mp) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$dni, $curso, $fecha, $cc, $mp]);

            $success = 'Inscripción realizada correctamente';
        }
    }
}

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultados = [];
if ($busqueda) {
    $stmt = $pdo->prepare("SELECT dni, nombres, cc, mp FROM perfiles WHERE dni LIKE ? OR nombres LIKE ? LIMIT 10");
    $stmt->execute(["%$busqueda%", "%$busqueda%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$cursos = $pdo->query("SELECT nombre FROM cursos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$inscripciones = $pdo->query("SELECT i.*, p.nombres FROM inscripciones i LEFT JOIN perfiles p ON i.dni = p.dni ORDER BY i.fecha DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'header.php'; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <h2>Nueva Inscripción</h2>
    <form method="GET" class="search-box">
        <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Buscar colaborador por DNI o nombre...">
        <button type="submit" class="btn">Buscar</button>
    </form>

    <?php if ($busqueda && count($resultados) > 0): ?>
    <div style="background:#f1f8e9; padding:15px; border-radius:8px; margin:15px 0;">
        <p style="color:#33691e; margin-bottom:10px;">Seleccione un colaborador:</p>
        <?php foreach ($resultados as $r): ?>
        <div style="padding:10px; border-bottom:1px solid #c8e6c9; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <strong><?php echo htmlspecialchars($r['nombres']); ?></strong><br>
                <span style="color:#666; font-size:13px;">DNI: <?php echo htmlspecialchars($r['dni']); ?> | CC: <?php echo $r['cc']; ?> | MP: <?php echo $r['mp']; ?></span>
            </div>
            <button type="button" onclick="selectColaborador('<?php echo $r['dni']; ?>', '<?php echo htmlspecialchars($r['nombres']); ?>')" class="btn btn-secondary" style="padding:8px 16px; font-size:13px;">Seleccionar</button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php elseif ($busqueda): ?>
    <div class="alert alert-error">No se encontraron colaboradores</div>
    <?php endif; ?>

    <form method="POST" id="formInscripcion" style="margin-top:20px;">
        <input type="hidden" name="dni" id="dni_seleccionado" required>
        <div style="background:#e8f5e9; padding:15px; border-radius:8px; margin-bottom:15px;">
            <strong>Colaborador seleccionado:</strong>
            <span id="nombre_seleccionado" style="color:#1b5e20;">Ninguno</span>
        </div>
        <select name="curso" required style="margin-bottom:15px;">
            <option value="">Seleccione un curso</option>
            <?php foreach ($cursos as $c): ?>
            <option value="<?php echo htmlspecialchars($c['nombre']); ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
        <button type="submit" name="inscribir" class="btn" style="margin-top:15px;">Realizar Inscripción</button>
    </form>
</div>

<div class="card">
    <h2>Últimas Inscripciones</h2>
    <table>
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre</th>
                <th>Curso</th>
                <th>Fecha</th>
                <th>CC</th>
                <th>MP</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscripciones as $ins): ?>
            <tr>
                <td><?php echo htmlspecialchars($ins['dni']); ?></td>
                <td><?php echo htmlspecialchars($ins['nombres'] ?: 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($ins['curso']); ?></td>
                <td><?php echo formatDate($ins['fecha']); ?></td>
                <td><?php echo number_format($ins['cc'], 1); ?></td>
                <td><?php echo number_format($ins['mp'], 1); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function selectColaborador(dni, nombre) {
    document.getElementById('dni_seleccionado').value = dni;
    document.getElementById('nombre_seleccionado').textContent = nombre;
}
</script>

<?php require 'footer.php'; ?>
