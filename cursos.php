<?php
require_once 'functions.php';
checkAuth();

$page_title = 'Cursos';
$pdo = getDB();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_curso'])) {
        $nombre = trim($_POST['nombre']);
        if (empty($nombre)) {
            $error = 'El nombre del curso es obligatorio';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT OR IGNORE INTO cursos (nombre) VALUES (?)");
                $stmt->execute([$nombre]);
                $success = 'Curso agregado correctamente';
            } catch (Exception $e) {
                $error = 'Error al agregar curso: ' . $e->getMessage();
            }
        }
    }

    if (isset($_POST['delete_curso'])) {
        $id = $_POST['curso_id'];
        $pdo->prepare("DELETE FROM cursos WHERE id = ?")->execute([$id]);
        $success = 'Curso eliminado';
    }
}

$cursos = $pdo->query("SELECT * FROM cursos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require 'header.php'; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <h2>Agregar Nuevo Curso</h2>
    <form method="POST" style="display:flex; gap:10px; align-items:flex-end;">
        <div style="flex:1;">
            <label style="font-size:13px; color:#555; display:block; margin-bottom:5px;">Nombre del Curso</label>
            <input type="text" name="nombre" placeholder="Ej: Matematicas" required style="margin:0;">
        </div>
        <button type="submit" name="add_curso" class="btn">Agregar Curso</button>
    </form>
</div>

<div class="card">
    <h2>Lista de Cursos (<?php echo count($cursos); ?>)</h2>
    <?php if (count($cursos) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cursos as $curso): ?>
            <tr>
                <td><?php echo $curso['id']; ?></td>
                <td><strong><?php echo htmlspecialchars($curso['nombre']); ?></strong></td>
                <td><?php echo isset($curso['created_at']) ? formatDate($curso['created_at']) : 'N/A'; ?></td>
                <td>
                    <form method="POST" onsubmit="return confirmDelete('¿Eliminar este curso?');" style="display:inline;">
                        <input type="hidden" name="curso_id" value="<?php echo $curso['id']; ?>">
                        <button type="submit" name="delete_curso" style="background:#c62828; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:12px;">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p style="color:#666; text-align:center; padding:20px;">No hay cursos registrados</p>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
