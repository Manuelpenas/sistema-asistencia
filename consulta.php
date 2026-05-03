<?php
require_once 'functions.php';
// checkAuth(); // Comentado: acceso público

$page_title = 'Consulta';

$pdo = getDB();
$resultados = [];
$busqueda = '';

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $busqueda = trim($_GET['q']);
    $stmt = $pdo->prepare("SELECT * FROM asistencias WHERE dni LIKE ? OR nombres LIKE ? ORDER BY fecha DESC");
    $stmt->execute(["%$busqueda%", "%$busqueda%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php require 'header.php'; ?>

<div class="card">
    <h2>Consulta de Asistencia</h2>
    <p style="color:#666; margin-bottom:20px; font-size:14px;">Busque por DNI o Nombres para ver los cursos y notas</p>

    <form method="GET" class="search-box">
        <input type="text" name="q" value="<?php echo htmlspecialchars($busqueda); ?>" placeholder="Ingrese DNI o Nombres..." autofocus>
        <button type="submit" class="btn">Buscar</button>
    </form>

    <?php if ($busqueda): ?>
        <?php if (count($resultados) > 0): ?>
            <?php
            $alumno = $resultados[0];
            $total_cursos = count($resultados);
            $promedio = array_sum(array_column($resultados, 'nota')) / $total_cursos;
            ?>
            <div style="background:#e8f5e9; padding:20px; border-radius:8px; margin:20px 0;">
                <h3 style="color:#1b5e20; font-weight:400; margin-bottom:10px;">
                    <?php echo htmlspecialchars($alumno['nombres']); ?>
                </h3>
                <div style="display:flex; gap:30px; flex-wrap:wrap; font-size:14px; color:#555;">
                    <span><strong>DNI:</strong> <?php echo htmlspecialchars($alumno['dni']); ?></span>
                    <span><strong>Cursos:</strong> <?php echo $total_cursos; ?></span>
                    <span><strong>Promedio:</strong> <?php echo number_format($promedio ?: 0, 1); ?></span>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Fecha</th>
                        <th>CC</th>
                        <th>MP</th>
                        <th>Nota</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['curso']); ?></td>
                        <td><?php echo formatDate($row['fecha']); ?></td>
                        <td><?php echo number_format($row['cc'] ?: 0, 1); ?></td>                        
                        <td><?php echo number_format($row['mp'] ?: 0, 1); ?></td>                        
                        <td><strong><?php echo number_format($row['nota'] ?: 0, 1); ?></strong></td>
                        <td>
                            <?php if ($row['nota'] >= 11): ?>
                                <span style="color:#2e7d32;">Aprobado</span>
                            <?php else: ?>
                                <span style="color:#c62828;">Desaprobado</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-error">No se encontraron registros para "<?php echo htmlspecialchars($busqueda); ?>"</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
