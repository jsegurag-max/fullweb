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
                'nombre' => post('nombre'),
                'nivel' => post('nivel') ?: 'Primaria',
                'grado' => post('grado'),
                'anio' => (int) (post('anio') ?: date('Y')),
                'docente_id' => post('docente_id') !== '' ? (int) post('docente_id') : null,
                'capacidad' => (int) (post('capacidad') ?: 30),
            ];
            if ($datos['nombre'] === '' || $datos['grado'] === '') {
                throw new RuntimeException('El nombre y el grado son obligatorios.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO cursos (nombre, nivel, grado, anio, docente_id, capacidad)
                               VALUES (:nombre, :nivel, :grado, :anio, :docente_id, :capacidad)")->execute($datos);
                $mensaje = 'Curso registrado correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE cursos SET nombre = :nombre, nivel = :nivel, grado = :grado, anio = :anio,
                               docente_id = :docente_id, capacidad = :capacidad WHERE id = :id")->execute($datos);
                $mensaje = 'Curso actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM cursos WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Curso eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM cursos WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$docentes = $pdo->query("SELECT id, nombre, apellido FROM docentes ORDER BY nombre")->fetchAll();
$cursos = $pdo->query("
    SELECT c.*, d.nombre AS docente_nombre, d.apellido AS docente_apellido,
           (SELECT COUNT(*) FROM estudiantes e WHERE e.curso_id = c.id) AS total_estudiantes
    FROM cursos c
    LEFT JOIN docentes d ON d.id = c.docente_id
    ORDER BY c.nivel, c.nombre
")->fetchAll();

$paginaActiva = 'cursos';
$titulo = 'Cursos y grados';
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
    <h3 class="card-title"><?php echo $editItem ? 'Editar curso' : 'Nuevo curso'; ?></h3>
    <?php if ($editItem): ?>
      <a href="cursos.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="<?php echo $editItem ? 'actualizar' : 'crear'; ?>">
      <?php if ($editItem): ?><input type="hidden" name="id" value="<?php echo (int) $editItem['id']; ?>"><?php endif; ?>

      <div class="col-md-4">
        <label class="form-label">Nombre del curso *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: 6° A" value="<?php echo e($editItem['nombre'] ?? ''); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Nivel</label>
        <select name="nivel" class="form-select">
          <?php foreach (['Preescolar', 'Primaria', 'Bachillerato'] as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['nivel'] ?? '') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Grado</label>
        <input type="text" name="grado" class="form-control" required placeholder="Ej: 6°" value="<?php echo e($editItem['grado'] ?? ''); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Año</label>
        <input type="number" name="anio" class="form-control" min="2000" max="2100" value="<?php echo e($editItem['anio'] ?? date('Y')); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Capacidad</label>
        <input type="number" name="capacidad" class="form-control" min="1" value="<?php echo e($editItem['capacidad'] ?? 30); ?>">
      </div>

      <div class="col-md-6">
        <label class="form-label">Docente titular</label>
        <select name="docente_id" class="form-select">
          <option value="">— Sin asignar —</option>
          <?php foreach ($docentes as $d): ?>
            <option value="<?php echo (int) $d['id']; ?>" <?php echo (int) ($editItem['docente_id'] ?? 0) === (int) $d['id'] ? 'selected' : ''; ?>>
              <?php echo e($d['nombre'] . ' ' . $d['apellido']); ?>
            </option>
          <?php endforeach; ?>
        </select>
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
  <div class="card-header"><h3 class="card-title">Listado (<?php echo count($cursos); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Curso</th><th>Nivel</th><th>Grado</th><th>Año</th><th>Docente</th><th>Estudiantes</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($cursos)): ?>
            <tr><td colspan="8" class="empty-state"><i class="bi bi-journal-bookmark"></i>No hay cursos registrados.</td></tr>
          <?php else: foreach ($cursos as $c): ?>
            <tr>
              <td><?php echo (int) $c['id']; ?></td>
              <td><?php echo e($c['nombre']); ?></td>
              <td><?php echo e($c['nivel']); ?></td>
              <td><?php echo e($c['grado']); ?></td>
              <td><?php echo e($c['anio']); ?></td>
              <td><?php echo e(trim(($c['docente_nombre'] ?? '') . ' ' . ($c['docente_apellido'] ?? '')) ?: '—'); ?></td>
              <td><span class="badge text-bg-primary"><?php echo (int) $c['total_estudiantes']; ?> / <?php echo (int) $c['capacidad']; ?></span></td>
              <td class="text-end">
                <a href="cursos.php?editar=<?php echo (int) $c['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="cursos.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar el curso <?php echo e($c['nombre']); ?>?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $c['id']; ?>">
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
