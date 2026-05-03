<?php
require_once 'functions.php';
checkAuth();

$page_title = 'Asistencia';
$pdo = getDB();

$success = '';
$error = '';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar_asistencia'])) {
    $curso = $_POST['curso'];
    $fecha_post = $_POST['fecha'];
    $count = 0;

    if (isset($_POST['asistentes']) && is_array($_POST['asistentes'])) {
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM asistencias WHERE dni = ? AND curso = ? AND fecha = ?");
        $insert_stmt = $pdo->prepare("INSERT INTO asistencias (dni, nombres, fecha, curso, cc, mp, nota) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($_POST['asistentes'] as $dni) {
            $check_stmt->execute([$dni, $curso, $fecha_post]);
            if ($check_stmt->fetchColumn() == 0) {
                $perfil = $pdo->prepare("SELECT nombres, cc, mp FROM perfiles WHERE dni = ?");
                $perfil->execute([$dni]);
                $p = $perfil->fetch(PDO::FETCH_ASSOC);

                if ($p) {
                    $nota = ($p['cc'] + $p['mp']) / 2;
                    $insert_stmt->execute([$dni, $p['nombres'], $fecha_post, $curso, $p['cc'], $p['mp'], $nota]);
                    $count++;
                }
            }
        }
        $success = "Se registró la asistencia de $count colaboradores";
    } else {
        $error = 'No se seleccionaron asistentes';
    }
}

$inscritos = $pdo->prepare("
    SELECT i.dni, p.nombres, i.cc, i.mp
    FROM inscripciones i
    LEFT JOIN perfiles p ON i.dni = p.dni
    WHERE i.fecha = ? AND i.curso = ?
");
$asistentes_dni = $pdo->prepare("SELECT dni FROM asistencias WHERE fecha = ? AND curso = ?");
$cursos_disponibles = $pdo->prepare("SELECT DISTINCT curso FROM inscripciones WHERE fecha = ?");

$cursos_disponibles->execute([$fecha]);
$cursos_fecha = $cursos_disponibles->fetchAll(PDO::FETCH_COLUMN);

$curso_sel = isset($_GET['curso']) ? $_GET['curso'] : ($cursos_fecha[0] ?? '');

$inscritos_arr = [];
$asistentes_arr = [];
if ($curso_sel) {
    $inscritos->execute([$fecha, $curso_sel]);
    $inscritos_arr = $inscritos->fetchAll(PDO::FETCH_ASSOC);

    $asistentes_dni->execute([$fecha, $curso_sel]);
    $asistentes_arr = $asistentes_dni->fetchAll(PDO::FETCH_COLUMN);
}
?>
<?php require 'header.php'; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <h2>Registro de Asistencia</h2>
    <form method="GET" class="search-box">
        <input type="date" name="fecha" value="<?php echo $fecha; ?>" onchange="this.form.submit()" style="width:auto;">
        <select name="curso" onchange="this.form.submit()" style="width:300px;">
            <option value="">Seleccione un curso</option>
            <?php foreach ($cursos_fecha as $c): ?>
            <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $curso_sel == $c ? 'selected' : ''; ?>><?php echo htmlspecialchars($c); ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($curso_sel): ?>
        <?php if (count($inscritos_arr) > 0): ?>
            <?php
            $sin_asistencia = [];
            foreach ($inscritos_arr as $i) {
                if (!in_array($i['dni'], $asistentes_arr)) {
                    $sin_asistencia[] = $i;
                }
            }
            ?>
            <?php if (count($asistentes_arr) > 0): ?>
                <div style="background:#e8f5e9; padding:15px; border-radius:8px; margin:15px 0; color:#2e7d32;">
                    Ya se registró asistencia para <?php echo count($asistentes_arr); ?> colaborador(es) en este curso.
                    <a href="consulta.php?q=<?php echo $curso_sel; ?>" class="btn btn-secondary" style="margin-left:15px; padding:8px 16px; font-size:13px;">Ver Resultados</a>
                </div>
            <?php endif; ?>

            <?php if (count($sin_asistencia) > 0): ?>
            <form method="POST">
                <input type="hidden" name="curso" value="<?php echo htmlspecialchars($curso_sel); ?>">
                <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                <p style="color:#666; margin:15px 0;">Marque los asistentes:</p>
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="toggleAll(this)"></th>
                            <th>DNI</th>
                            <th>Nombres</th>
                            <th>CC</th>
                            <th>MP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sin_asistencia as $ins): ?>
                        <tr>
                            <td><input type="checkbox" name="asistentes[]" value="<?php echo htmlspecialchars($ins['dni']); ?>"></td>
                            <td><?php echo htmlspecialchars($ins['dni']); ?></td>
                            <td><?php echo htmlspecialchars($ins['nombres'] ?: 'N/A'); ?></td>
                        <td><?php echo number_format($ins['cc'] ?: 0, 1); ?></td>
                        <td><?php echo number_format($ins['mp'] ?: 0, 1); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="registrar_asistencia" class="btn" style="margin-top:20px;">Registrar Asistencia</button>
            </form>
            <?php else: ?>
            <div class="alert alert-success">Todos los inscritos ya tienen asistencia registrada para esta fecha.</div>
            <?php endif; ?>
        <?php else: ?>
        <div class="alert alert-error">No hay inscritos para este curso en la fecha seleccionada.</div>
        <?php endif; ?>
    <?php elseif (count($cursos_fecha) == 0): ?>
    <div class="alert alert-error">No hay cursos con inscritos para la fecha <?php echo formatDate($fecha); ?></div>
    <?php endif; ?>
</div>

<script>
function toggleAll(source) {
    checkboxes = document.getElementsByName('asistentes[]');
    for(var i=0; i<checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>

<?php require 'footer.php'; ?>
