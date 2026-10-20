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
                'apellido' => post('apellido'),
                'documento' => post('documento'),
                'email' => post('email'),
                'telefono' => post('telefono'),
                'genero' => post('genero') ?: 'Otro',
                'fecha_nacimiento' => post('fecha_nacimiento') ?: null,
                'curso_id' => post('curso_id') !== '' ? (int) post('curso_id') : null,
                'acudiente' => post('acudiente'),
                'telefono_acudiente' => post('telefono_acudiente'),
                'estado' => post('estado') ?: 'Activo',
            ];

            if ($datos['nombre'] === '' || $datos['apellido'] === '') {
                throw new RuntimeException('El nombre y el apellido son obligatorios.');
            }

            if ($accion === 'crear') {
                $sql = "INSERT INTO estudiantes (nombre, apellido, documento, email, telefono, genero, fecha_nacimiento, curso_id, acudiente, telefono_acudiente, estado)
                        VALUES (:nombre, :apellido, :documento, :email, :telefono, :genero, :fecha_nacimiento, :curso_id, :acudiente, :telefono_acudiente, :estado)";
                $pdo->prepare($sql)->execute($datos);
                $mensaje = 'Estudiante registrado correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $sql = "UPDATE estudiantes SET nombre = :nombre, apellido = :apellido, documento = :documento, email = :email,
                        telefono = :telefono, genero = :genero, fecha_nacimiento = :fecha_nacimiento, curso_id = :curso_id,
                        acudiente = :acudiente, telefono_acudiente = :telefono_acudiente, estado = :estado WHERE id = :id";
                $pdo->prepare($sql)->execute($datos);
                $mensaje = 'Estudiante actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM estudiantes WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Estudiante eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM estudiantes WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$cursos = $pdo->query("SELECT id, nombre FROM cursos ORDER BY nombre")->fetchAll();
$estudiantes = $pdo->query("
    SELECT e.*, cu.nombre AS curso
    FROM estudiantes e
    LEFT JOIN cursos cu ON cu.id = e.curso_id
    ORDER BY e.id DESC
")->fetchAll();

$paginaActiva = 'estudiantes';
$titulo = 'Estudiantes';
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
    <h3 class="card-title"><?php echo $editItem ? 'Editar estudiante' : 'Nuevo estudiante'; ?></h3>
    <?php if ($editItem): ?>
      <a href="estudiantes.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="<?php echo $editItem ? 'actualizar' : 'crear'; ?>">
      <?php if ($editItem): ?>
        <input type="hidden" name="id" value="<?php echo (int) $editItem['id']; ?>">
      <?php endif; ?>

      <div class="col-md-3">
        <label class="form-label">Nombre *</label>
        <input type="text" name="nombre" class="form-control" required value="<?php echo e($editItem['nombre'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Apellido *</label>
        <input type="text" name="apellido" class="form-control" required value="<?php echo e($editItem['apellido'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Documento</label>
        <input type="text" name="documento" class="form-control" value="<?php echo e($editItem['documento'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Género</label>
        <select name="genero" class="form-select">
          <?php foreach (['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $val => $txt): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['genero'] ?? 'Otro') === $val ? 'selected' : ''; ?>><?php echo $txt; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?php echo e($editItem['email'] ?? ''); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control" value="<?php echo e($editItem['telefono'] ?? ''); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control" value="<?php echo e($editItem['fecha_nacimiento'] ?? ''); ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Curso</label>
        <select name="curso_id" class="form-select">
          <option value="">— Sin asignar —</option>
          <?php foreach ($cursos as $c): ?>
            <option value="<?php echo (int) $c['id']; ?>" <?php echo (int) ($editItem['curso_id'] ?? 0) === (int) $c['id'] ? 'selected' : ''; ?>><?php echo e($c['nombre']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Acudiente</label>
        <input type="text" name="acudiente" class="form-control" value="<?php echo e($editItem['acudiente'] ?? ''); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Teléfono del acudiente</label>
        <input type="text" name="telefono_acudiente" class="form-control" value="<?php echo e($editItem['telefono_acudiente'] ?? ''); ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <?php foreach (['Activo', 'Inactivo', 'Pendiente'] as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['estado'] ?? 'Activo') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
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
  <div class="card-header">
    <h3 class="card-title">Listado (<?php echo count($estudiantes); ?>)</h3>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Estudiante</th><th>Curso</th><th>Acudiente</th><th>Estado</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($estudiantes)): ?>
            <tr><td colspan="6" class="empty-state"><i class="bi bi-people"></i>No hay estudiantes registrados.</td></tr>
          <?php else: foreach ($estudiantes as $s): ?>
            <tr>
              <td><?php echo (int) $s['id']; ?></td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <span class="pf-avatar"><?php echo e(iniciales($s['nombre'], $s['apellido'])); ?></span>
                  <div>
                    <div class="pf-name"><?php echo e($s['nombre'] . ' ' . $s['apellido']); ?></div>
                    <?php if ($s['documento']): ?><span class="pf-sub"><?php echo e($s['documento']); ?></span><?php endif; ?>
                  </div>
                </div>
              </td>
              <td><?php echo e($s['curso'] ?? '—'); ?></td>
              <td><?php echo e($s['acudiente'] ?? '—'); ?></td>
              <td>
                <?php
                $cls = $s['estado'] === 'Activo' ? 'success' : ($s['estado'] === 'Pendiente' ? 'warning' : 'secondary');
                ?>
                <span class="badge text-bg-<?php echo $cls; ?> badge-estado"><?php echo e($s['estado']); ?></span>
              </td>
              <td class="text-end">
                <a href="estudiantes.php?editar=<?php echo (int) $s['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="estudiantes.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar a <?php echo e($s['nombre'] . ' ' . $s['apellido']); ?>?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $s['id']; ?>">
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
