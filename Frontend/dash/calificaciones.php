<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$mensaje = '';
$tipo = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = post('accion');
    try {
        if ($accion === 'crear' || $accion === 'actualizar') {
            $datos = [
                'estudiante_id' => (int) post('estudiante_id'),
                'curso_id' => post('curso_id') !== '' ? (int) post('curso_id') : null,
                'materia' => post('materia'),
                'periodo' => post('periodo'),
                'nota' => (float) post('nota'),
            ];
            if ($datos['estudiante_id'] <= 0 || $datos['materia'] === '') {
                throw new RuntimeException('Selecciona un estudiante e indica la materia.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO calificaciones (estudiante_id, curso_id, materia, periodo, nota)
                               VALUES (:estudiante_id, :curso_id, :materia, :periodo, :nota)")->execute($datos);
                $mensaje = 'Calificación registrada correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE calificaciones SET estudiante_id = :estudiante_id, curso_id = :curso_id,
                               materia = :materia, periodo = :periodo, nota = :nota WHERE id = :id")->execute($datos);
                $mensaje = 'Calificación actualizada correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM calificaciones WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Calificación eliminada.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM calificaciones WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$estudiantes = $pdo->query("SELECT id, nombre, apellido FROM estudiantes ORDER BY apellido, nombre")->fetchAll();
$cursos = $pdo->query("SELECT id, nombre FROM cursos ORDER BY nombre")->fetchAll();
$calificaciones = $pdo->query("
    SELECT cal.*, e.nombre AS est_nombre, e.apellido AS est_apellido, cu.nombre AS curso
    FROM calificaciones cal
    JOIN estudiantes e ON e.id = cal.estudiante_id
    LEFT JOIN cursos cu ON cu.id = cal.curso_id
    ORDER BY cal.id DESC
")->fetchAll();

$paginaActiva = 'calificaciones';
$titulo = 'Calificaciones';
require __DIR__ . '/includes/header.php';
?>
<?php if ($mensaje): ?>
  <div class="alert alert-<?php echo e($tipo); ?> alert-dismissible fade show">
    <?php echo e($mensaje); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card card-form mb-4">
  <div class="card-header">
    <h3 class="card-title"><?php echo $editItem ? 'Editar calificación' : 'Nueva calificación'; ?></h3>
    <?php if ($editItem): ?>
      <a href="calificaciones.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="<?php echo $editItem ? 'actualizar' : 'crear'; ?>">
      <?php if ($editItem): ?><input type="hidden" name="id" value="<?php echo (int) $editItem['id']; ?>"><?php endif; ?>

      <div class="col-md-4">
        <label class="form-label">Estudiante *</label>
        <select name="estudiante_id" class="form-select" required>
          <option value="">— Selecciona —</option>
          <?php foreach ($estudiantes as $es): ?>
            <option value="<?php echo (int) $es['id']; ?>" <?php echo (int) ($editItem['estudiante_id'] ?? 0) === (int) $es['id'] ? 'selected' : ''; ?>>
              <?php echo e($es['apellido'] . ', ' . $es['nombre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Curso</label>
        <select name="curso_id" class="form-select">
          <option value="">— Sin asignar —</option>
          <?php foreach ($cursos as $c): ?>
            <option value="<?php echo (int) $c['id']; ?>" <?php echo (int) ($editItem['curso_id'] ?? 0) === (int) $c['id'] ? 'selected' : ''; ?>><?php echo e($c['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Materia *</label>
        <input type="text" name="materia" class="form-control" required value="<?php echo e($editItem['materia'] ?? ''); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Nota (0-100)</label>
        <input type="number" name="nota" step="0.01" min="0" max="100" class="form-control" required value="<?php echo e($editItem['nota'] ?? ''); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Período</label>
        <input type="text" name="periodo" class="form-control" placeholder="Ej: II Trimestre" value="<?php echo e($editItem['periodo'] ?? ''); ?>">
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-<?php echo $editItem ? 'save' : 'plus-lg'; ?>"></i>
          <?php echo $editItem ? 'Actualizar' : 'Registrar'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<div class="card card-table">
  <div class="card-header"><h3 class="card-title">Listado (<?php echo count($calificaciones); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Estudiante</th><th>Curso</th><th>Materia</th><th>Período</th><th>Nota</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($calificaciones)): ?>
            <tr><td colspan="7" class="empty-state"><i class="bi bi-clipboard-data"></i>No hay calificaciones registradas.</td></tr>
          <?php else: foreach ($calificaciones as $cal): ?>
            <tr>
              <td><?php echo (int) $cal['id']; ?></td>
              <td><?php echo e($cal['est_apellido'] . ', ' . $cal['est_nombre']); ?></td>
              <td><?php echo e($cal['curso'] ?? '—'); ?></td>
              <td><?php echo e($cal['materia']); ?></td>
              <td><?php echo e($cal['periodo'] ?: '—'); ?></td>
              <td>
                <?php
                $n = (float) $cal['nota'];
                $cls = $n >= 70 ? 'success' : ($n >= 60 ? 'warning' : 'danger');
                ?>
                <span class="badge text-bg-<?php echo $cls; ?> badge-estado"><?php echo number_format($n, 2); ?></span>
              </td>
              <td class="text-end">
                <a href="calificaciones.php?editar=<?php echo (int) $cal['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="calificaciones.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta calificación?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $cal['id']; ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Eliminar"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php
require __DIR__ . '/includes/footer.php';
