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
                'fecha' => post('fecha') ?: date('Y-m-d'),
                'estado' => post('estado') ?: 'Presente',
                'observacion' => post('observacion'),
            ];
            if ($datos['estudiante_id'] <= 0) {
                throw new RuntimeException('Selecciona un estudiante.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO asistencia (estudiante_id, fecha, estado, observacion)
                               VALUES (:estudiante_id, :fecha, :estado, :observacion)")->execute($datos);
                $mensaje = 'Asistencia registrada correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE asistencia SET estudiante_id = :estudiante_id, fecha = :fecha,
                               estado = :estado, observacion = :observacion WHERE id = :id")->execute($datos);
                $mensaje = 'Asistencia actualizada correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM asistencia WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Registro de asistencia eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM asistencia WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$estudiantes = $pdo->query("SELECT id, nombre, apellido FROM estudiantes ORDER BY apellido, nombre")->fetchAll();
$registros = $pdo->query("
    SELECT a.*, e.nombre AS est_nombre, e.apellido AS est_apellido
    FROM asistencia a
    JOIN estudiantes e ON e.id = a.estudiante_id
    ORDER BY a.fecha DESC, a.id DESC
    LIMIT 200
")->fetchAll();

$paginaActiva = 'asistencia';
$titulo = 'Asistencia';
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
    <h3 class="card-title"><?php echo $editItem ? 'Editar asistencia' : 'Registrar asistencia'; ?></h3>
    <?php if ($editItem): ?>
      <a href="asistencia.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
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
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?php echo e($editItem['fecha'] ?? date('Y-m-d')); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <?php foreach (['Presente', 'Ausente', 'Tarde', 'Excusa'] as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['estado'] ?? 'Presente') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Observación</label>
        <input type="text" name="observacion" class="form-control" value="<?php echo e($editItem['observacion'] ?? ''); ?>">
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
  <div class="card-header"><h3 class="card-title">Últimos registros (<?php echo count($registros); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Estudiante</th><th>Fecha</th><th>Estado</th><th>Observación</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($registros)): ?>
            <tr><td colspan="6" class="empty-state"><i class="bi bi-calendar-check"></i>No hay registros de asistencia.</td></tr>
          <?php else: foreach ($registros as $r): ?>
            <tr>
              <td><?php echo (int) $r['id']; ?></td>
              <td><?php echo e($r['est_apellido'] . ', ' . $r['est_nombre']); ?></td>
              <td><?php echo e(date('d/m/Y', strtotime($r['fecha']))); ?></td>
              <td>
                <?php
                $map = ['Presente' => 'success', 'Tarde' => 'warning', 'Excusa' => 'info', 'Ausente' => 'danger'];
                $cls = $map[$r['estado']] ?? 'secondary';
                ?>
                <span class="badge text-bg-<?php echo $cls; ?> badge-estado"><?php echo e($r['estado']); ?></span>
              </td>
              <td><?php echo e($r['observacion'] ?: '—'); ?></td>
              <td class="text-end">
                <a href="asistencia.php?editar=<?php echo (int) $r['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="asistencia.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este registro?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
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
