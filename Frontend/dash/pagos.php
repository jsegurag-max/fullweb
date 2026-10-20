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
                'concepto' => post('concepto'),
                'monto' => (float) post('monto'),
                'fecha' => post('fecha') ?: date('Y-m-d'),
                'metodo' => post('metodo') ?: 'Efectivo',
                'estado' => post('estado') ?: 'Pendiente',
            ];
            if ($datos['estudiante_id'] <= 0 || $datos['concepto'] === '') {
                throw new RuntimeException('Selecciona un estudiante e indica el concepto.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO pagos (estudiante_id, concepto, monto, fecha, metodo, estado)
                               VALUES (:estudiante_id, :concepto, :monto, :fecha, :metodo, :estado)")->execute($datos);
                $mensaje = 'Pago registrado correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE pagos SET estudiante_id = :estudiante_id, concepto = :concepto, monto = :monto,
                               fecha = :fecha, metodo = :metodo, estado = :estado WHERE id = :id")->execute($datos);
                $mensaje = 'Pago actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM pagos WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Pago eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM pagos WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$estudiantes = $pdo->query("SELECT id, nombre, apellido FROM estudiantes ORDER BY apellido, nombre")->fetchAll();
$pagos = $pdo->query("
    SELECT p.*, e.nombre AS est_nombre, e.apellido AS est_apellido
    FROM pagos p
    JOIN estudiantes e ON e.id = p.estudiante_id
    ORDER BY p.fecha DESC, p.id DESC
")->fetchAll();
$totalPagado = (float) $pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado = 'Pagado'")->fetchColumn();
$totalPendiente = (float) $pdo->query("SELECT COALESCE(SUM(monto),0) FROM pagos WHERE estado <> 'Pagado'")->fetchColumn();

$paginaActiva = 'pagos';
$titulo = 'Pagos y matrícula';
require __DIR__ . '/includes/header.php';
?>
<?php if ($mensaje): ?>
  <div class="alert alert-<?php echo e($tipo); ?> alert-dismissible fade show">
    <?php echo e($mensaje); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row mb-4">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <p class="text-secondary mb-1">Total pagado</p>
          <div class="stat-mini">$<?php echo number_format($totalPagado, 2); ?></div>
        </div>
        <i class="bi bi-cash-stack fs-1 text-success"></i>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <p class="text-secondary mb-1">Pendiente / vencido</p>
          <div class="stat-mini">$<?php echo number_format($totalPendiente, 2); ?></div>
        </div>
        <i class="bi bi-hourglass-split fs-1 text-warning"></i>
      </div>
    </div>
  </div>
</div>

<div class="card card-form mb-4">
  <div class="card-header">
    <h3 class="card-title"><?php echo $editItem ? 'Editar pago' : 'Nuevo pago'; ?></h3>
    <?php if ($editItem): ?>
      <a href="pagos.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
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
      <div class="col-md-4">
        <label class="form-label">Concepto *</label>
        <input type="text" name="concepto" class="form-control" required placeholder="Ej: Matrícula 2026" value="<?php echo e($editItem['concepto'] ?? ''); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Monto</label>
        <input type="number" name="monto" step="0.01" min="0" class="form-control" required value="<?php echo e($editItem['monto'] ?? ''); ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?php echo e($editItem['fecha'] ?? date('Y-m-d')); ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Método</label>
        <select name="metodo" class="form-select">
          <?php foreach (['Efectivo', 'Transferencia', 'Tarjeta'] as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['metodo'] ?? 'Efectivo') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <?php foreach (['Pagado', 'Pendiente', 'Vencido'] as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['estado'] ?? 'Pendiente') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
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
  <div class="card-header"><h3 class="card-title">Listado (<?php echo count($pagos); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Estudiante</th><th>Concepto</th><th>Monto</th><th>Fecha</th><th>Método</th><th>Estado</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($pagos)): ?>
            <tr><td colspan="8" class="empty-state"><i class="bi bi-cash-coin"></i>No hay pagos registrados.</td></tr>
          <?php else: foreach ($pagos as $p): ?>
            <tr>
              <td><?php echo (int) $p['id']; ?></td>
              <td><?php echo e($p['est_apellido'] . ', ' . $p['est_nombre']); ?></td>
              <td><?php echo e($p['concepto']); ?></td>
              <td>$<?php echo number_format((float) $p['monto'], 2); ?></td>
              <td><?php echo e(date('d/m/Y', strtotime($p['fecha']))); ?></td>
              <td><?php echo e($p['metodo']); ?></td>
              <td>
                <?php
                $map = ['Pagado' => 'success', 'Pendiente' => 'warning', 'Vencido' => 'danger'];
                $cls = $map[$p['estado']] ?? 'secondary';
                ?>
                <span class="badge text-bg-<?php echo $cls; ?> badge-estado"><?php echo e($p['estado']); ?></span>
              </td>
              <td class="text-end">
                <a href="pagos.php?editar=<?php echo (int) $p['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="pagos.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este pago?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $p['id']; ?>">
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
