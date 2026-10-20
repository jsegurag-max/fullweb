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
                'especialidad' => post('especialidad'),
                'estado' => post('estado') ?: 'Activo',
            ];
            if ($datos['nombre'] === '' || $datos['apellido'] === '') {
                throw new RuntimeException('El nombre y el apellido son obligatorios.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO docentes (nombre, apellido, documento, email, telefono, especialidad, estado)
                               VALUES (:nombre, :apellido, :documento, :email, :telefono, :especialidad, :estado)")->execute($datos);
                $mensaje = 'Docente registrado correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE docentes SET nombre = :nombre, apellido = :apellido, documento = :documento,
                               email = :email, telefono = :telefono, especialidad = :especialidad, estado = :estado WHERE id = :id")->execute($datos);
                $mensaje = 'Docente actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM docentes WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Docente eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM docentes WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$docentes = $pdo->query("SELECT * FROM docentes ORDER BY id DESC")->fetchAll();

$paginaActiva = 'docentes';
$titulo = 'Docentes';
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
    <h3 class="card-title"><?php echo $editItem ? 'Editar docente' : 'Nuevo docente'; ?></h3>
    <?php if ($editItem): ?>
      <a href="docentes.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="<?php echo $editItem ? 'actualizar' : 'crear'; ?>">
      <?php if ($editItem): ?><input type="hidden" name="id" value="<?php echo (int) $editItem['id']; ?>"><?php endif; ?>

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
        <label class="form-label">Especialidad</label>
        <input type="text" name="especialidad" class="form-control" value="<?php echo e($editItem['especialidad'] ?? ''); ?>">
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
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <?php foreach (['Activo', 'Inactivo'] as $val): ?>
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
  <div class="card-header"><h3 class="card-title">Listado (<?php echo count($docentes); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Docente</th><th>Especialidad</th><th>Contacto</th><th>Estado</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($docentes)): ?>
            <tr><td colspan="6" class="empty-state"><i class="bi bi-person-workspace"></i>No hay docentes registrados.</td></tr>
          <?php else: foreach ($docentes as $d): ?>
            <tr>
              <td><?php echo (int) $d['id']; ?></td>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <span class="pf-avatar pf-avatar-accent"><?php echo e(iniciales($d['nombre'], $d['apellido'])); ?></span>
                  <div>
                    <div class="pf-name"><?php echo e($d['nombre'] . ' ' . $d['apellido']); ?></div>
                    <?php if ($d['documento']): ?><span class="pf-sub"><?php echo e($d['documento']); ?></span><?php endif; ?>
                  </div>
                </div>
              </td>
              <td><?php echo e($d['especialidad'] ?: '—'); ?></td>
              <td>
                <?php echo e($d['email'] ?: '—'); ?><br>
                <small class="text-secondary"><?php echo e($d['telefono'] ?: ''); ?></small>
              </td>
              <td>
                <span class="badge text-bg-<?php echo $d['estado'] === 'Activo' ? 'success' : 'secondary'; ?> badge-estado"><?php echo e($d['estado']); ?></span>
              </td>
              <td class="text-end">
                <a href="docentes.php?editar=<?php echo (int) $d['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="docentes.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar a <?php echo e($d['nombre'] . ' ' . $d['apellido']); ?>?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $d['id']; ?>">
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
