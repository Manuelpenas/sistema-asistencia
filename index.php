<?php
require_once 'functions.php';
$page_title = 'Dashboard';

$stats = [];
$pdo = getDB();

$stats['total'] = $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
$stats['perfiles'] = $pdo->query("SELECT COUNT(*) FROM perfiles")->fetchColumn();
$stats['cursos_activos'] = $pdo->query("SELECT COUNT(DISTINCT curso) FROM asistencias")->fetchColumn();
$stats['promedio'] = $pdo->query("SELECT AVG(nota) FROM asistencias")->fetchColumn();
$stats['inscripciones_hoy'] = $pdo->query("SELECT COUNT(*) FROM inscripciones WHERE fecha = '" . date('Y-m-d') . "'")->fetchColumn();
$stats['proveedores'] = $pdo->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
$stats['promedio_proveedores'] = $pdo->query("SELECT AVG(nota) FROM proveedores")->fetchColumn();

$cursos_data = $pdo->query("SELECT curso, COUNT(*) as total, AVG(nota) as promedio FROM asistencias GROUP BY curso ORDER BY total DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$ultimos = $pdo->query("SELECT dni, nombres, curso, nota, fecha FROM asistencias ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$notas_dist = $pdo->query("SELECT
    SUM(CASE WHEN nota >= 14 THEN 1 ELSE 0 END) as excelente,
    SUM(CASE WHEN nota >= 11 AND nota < 14 THEN 1 ELSE 0 END) as regular,
    SUM(CASE WHEN nota < 11 THEN 1 ELSE 0 END) as bajo
    FROM asistencias")->fetch(PDO::FETCH_ASSOC);
?>
<?php require 'header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="number"><?php echo $stats['total']; ?></div>
        <div class="label">Asistencias</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo $stats['perfiles']; ?></div>
        <div class="label">Perfiles</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo $stats['cursos_activos']; ?></div>
        <div class="label">Cursos</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo number_format($stats['promedio'], 1); ?></div>
        <div class="label">Promedio General</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo $stats['inscripciones_hoy']; ?></div>
        <div class="label">Inscripciones Hoy</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo $stats['proveedores']; ?></div>
        <div class="label">Proveedores</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo number_format($stats['promedio_proveedores'], 1); ?></div>
        <div class="label">Promedio Proveedores</div>
    </div>
</div>

<div class="card">
    <h2>Distribución de Notas</h2>
    <div style="display:flex; gap:20px; margin:20px 0;">
        <div style="flex:1; text-align:center;">
            <div style="background:#c8e6c9; height:<?php echo max(20, $notas_dist['excelente']*5); ?>px; border-radius:6px; display:flex; align-items:end; justify-content:center; padding-bottom:10px; color:#1b5e20; font-weight:600;">
                <?php echo $notas_dist['excelente']; ?>
            </div>
            <div style="margin-top:8px; font-size:12px; color:#2e7d32;">Excelente (14+)</div>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="background:#fff9c4; height:<?php echo max(20, $notas_dist['regular']*5); ?>px; border-radius:6px; display:flex; align-items:end; justify-content:center; padding-bottom:10px; color:#f57f17; font-weight:600;">
                <?php echo $notas_dist['regular']; ?>
            </div>
            <div style="margin-top:8px; font-size:12px; color:#f57c00;">Regular (11-13)</div>
        </div>
        <div style="flex:1; text-align:center;">
            <div style="background:#ffcdd2; height:<?php echo max(20, $notas_dist['bajo']*5); ?>px; border-radius:6px; display:flex; align-items:end; justify-content:center; padding-bottom:10px; color:#b71c1c; font-weight:600;">
                <?php echo $notas_dist['bajo']; ?>
            </div>
            <div style="margin-top:8px; font-size:12px; color:#c62828;">Bajo (&lt;11)</div>
        </div>
    </div>
</div>

<div class="card">
    <h2>Rendimiento por Curso</h2>
    <table>
        <thead>
            <tr>
                <th>Curso</th>
                <th>Registros</th>
                <th>Promedio</th>
                <th>Gráfico</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cursos_data as $curso): ?>
            <tr>
                <td><?php echo htmlspecialchars($curso['curso']); ?></td>
                <td><?php echo $curso['total']; ?></td>
                <td><?php echo number_format($curso['promedio'], 1); ?></td>
                <td>
                    <div style="background:#e0e0e0; width:100px; height:8px; border-radius:4px; overflow:hidden;">
                        <div style="background:#4caf50; width:<?php echo min(100, ($curso['promedio']/20)*100); ?>%; height:100%;"></div>
                    </div>
                </td>
                <td>
                    <?php if ($curso['promedio'] >= 14): ?>
                        <span style="color:#2e7d32;">● Excelente</span>
                    <?php elseif ($curso['promedio'] >= 11): ?>
                        <span style="color:#f57c00;">● Regular</span>
                    <?php else: ?>
                        <span style="color:#c62828;">● Bajo</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Últimos Registros</h2>
    <table>
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombres</th>
                <th>Curso</th>
                <th>Nota</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ultimos as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['dni']); ?></td>
                <td><?php echo htmlspecialchars($row['nombres']); ?></td>
                <td><?php echo htmlspecialchars($row['curso']); ?></td>
                <td><strong><?php echo number_format($row['nota'], 1); ?></strong></td>
                <td><?php echo formatDate($row['fecha']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require 'footer.php'; ?>
