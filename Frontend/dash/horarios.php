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
                'curso_id' => (int) post('curso_id'),
                'materia' => post('materia'),
                'dia_semana' => post('dia_semana') ?: 'Lunes',
                'hora_inicio' => post('hora_inicio') ?: '07:00',
                'hora_fin' => post('hora_fin') ?: '08:00',
                'docente_id' => post('docente_id') !== '' ? (int) post('docente_id') : null,
                'aula' => post('aula'),
            ];
            if ($datos['curso_id'] <= 0 || $datos['materia'] === '') {
                throw new RuntimeException('Selecciona un curso e indica la materia.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO horarios (curso_id, materia, dia_semana, hora_inicio, hora_fin, docente_id, aula)
                               VALUES (:curso_id, :materia, :dia_semana, :hora_inicio, :hora_fin, :docente_id, :aula)")->execute($datos);
                $mensaje = 'Horario registrado correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE horarios SET curso_id = :curso_id, materia = :materia, dia_semana = :dia_semana,
                               hora_inicio = :hora_inicio, hora_fin = :hora_fin, docente_id = :docente_id, aula = :aula WHERE id = :id")->execute($datos);
                $mensaje = 'Horario actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM horarios WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Horario eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM horarios WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$cursos = $pdo->query("SELECT id, nombre FROM cursos ORDER BY nombre")->fetchAll();
$docentes = $pdo->query("SELECT id, nombre, apellido FROM docentes ORDER BY nombre")->fetchAll();
$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$horarios = $pdo->query("
    SELECT h.*, c.nombre AS curso, d.nombre AS doc_nombre, d.apellido AS doc_apellido
    FROM horarios h
    JOIN cursos c ON c.id = h.curso_id
    LEFT JOIN docentes d ON d.id = h.docente_id
    ORDER BY FIELD(h.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'), h.hora_inicio
")->fetchAll();

$paginaActiva = 'horarios';
$titulo = 'Horarios';
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
    <h3 class="card-title"><?php echo $editItem ? 'Editar horario' : 'Nuevo horario'; ?></h3>
    <?php if ($editItem): ?>
      <a href="horarios.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="<?php echo $editItem ? 'actualizar' : 'crear'; ?>">
      <?php if ($editItem): ?><input type="hidden" name="id" value="<?php echo (int) $editItem['id']; ?>"><?php endif; ?>

      <div class="col-md-3">
        <label class="form-label">Curso *</label>
        <select name="curso_id" class="form-select" required>
          <option value="">— Selecciona —</option>
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
        <label class="form-label">Día</label>
        <select name="dia_semana" class="form-select">
          <?php foreach ($dias as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['dia_semana'] ?? 'Lunes') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Inicio</label>
        <input type="time" name="hora_inicio" class="form-control" value="<?php echo e(substr($editItem['hora_inicio'] ?? '07:00', 0, 5)); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Fin</label>
        <input type="time" name="hora_fin" class="form-control" value="<?php echo e(substr($editItem['hora_fin'] ?? '08:00', 0, 5)); ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Docente</label>
        <select name="docente_id" class="form-select">
          <option value="">— Sin asignar —</option>
          <?php foreach ($docentes as $d): ?>
            <option value="<?php echo (int) $d['id']; ?>" <?php echo (int) ($editItem['docente_id'] ?? 0) === (int) $d['id'] ? 'selected' : ''; ?>><?php echo e($d['nombre'] . ' ' . $d['apellido']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Aula</label>
        <input type="text" name="aula" class="form-control" value="<?php echo e($editItem['aula'] ?? ''); ?>">
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
  <div class="card-header"><h3 class="card-title">Listado (<?php echo count($horarios); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Curso</th><th>Materia</th><th>Día</th><th>Horario</th><th>Docente</th><th>Aula</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($horarios)): ?>
            <tr><td colspan="8" class="empty-state"><i class="bi bi-calendar3"></i>No hay horarios registrados.</td></tr>
          <?php else: foreach ($horarios as $h): ?>
            <tr>
              <td><?php echo (int) $h['id']; ?></td>
              <td><?php echo e($h['curso']); ?></td>
              <td><?php echo e($h['materia']); ?></td>
              <td><?php echo e($h['dia_semana']); ?></td>
              <td><?php echo e(substr($h['hora_inicio'], 0, 5) . ' - ' . substr($h['hora_fin'], 0, 5)); ?></td>
              <td><?php echo e(trim(($h['doc_nombre'] ?? '') . ' ' . ($h['doc_apellido'] ?? '')) ?: '—'); ?></td>
              <td><?php echo e($h['aula'] ?: '—'); ?></td>
              <td class="text-end">
                <a href="horarios.php?editar=<?php echo (int) $h['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="horarios.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este horario?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $h['id']; ?>">
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
