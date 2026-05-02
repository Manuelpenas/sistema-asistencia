<?php
require_once 'functions.php';
checkAuth();

$page_title = 'Registros';
$pdo = getDB();

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM asistencias")->fetchColumn();
$pages = ceil($total / $limit);

$stmt = $pdo->prepare("SELECT * FROM asistencias ORDER BY fecha DESC, id DESC LIMIT ? OFFSET ?");
$stmt->execute([$limit, $offset]);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'header.php'; ?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>Registros de Asistencia</h2>
        <span style="color:#666; font-size:14px;">Total: <?php echo $total; ?> registros</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>DNI</th>
                <th>Nombres</th>
                <th>Fecha</th>
                <th>Curso</th>
                <th>CC</th>
                <th>MP</th>
                <th>Nota</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registros as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['dni']); ?></td>
                <td><?php echo htmlspecialchars($row['nombres']); ?></td>
                <td><?php echo formatDate($row['fecha']); ?></td>
                <td><?php echo htmlspecialchars($row['curso']); ?></td>
                <td><?php echo number_format($row['cc'], 1); ?></td>
                <td><?php echo number_format($row['mp'], 1); ?></td>
                <td><strong><?php echo number_format($row['nota'], 1); ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pages > 1): ?>
    <div style="display:flex; justify-content:center; gap:10px; margin-top:20px;">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page-1; ?>" class="btn btn-secondary">« Anterior</a>
        <?php endif; ?>
        <span style="padding:10px; color:#666;">Página <?php echo $page; ?> de <?php echo $pages; ?></span>
        <?php if ($page < $pages): ?>
        <a href="?page=<?php echo $page+1; ?>" class="btn btn-secondary">Siguiente »</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
