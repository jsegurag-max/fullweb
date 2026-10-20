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
                'titulo' => post('titulo'),
                'contenido' => post('contenido'),
                'categoria' => post('categoria') ?: 'General',
                'autor' => post('autor'),
                'fecha' => post('fecha') ?: date('Y-m-d'),
            ];
            if ($datos['titulo'] === '') {
                throw new RuntimeException('El título es obligatorio.');
            }
            if ($accion === 'crear') {
                $pdo->prepare("INSERT INTO avisos (titulo, contenido, categoria, autor, fecha)
                               VALUES (:titulo, :contenido, :categoria, :autor, :fecha)")->execute($datos);
                $mensaje = 'Aviso publicado correctamente.';
            } else {
                $datos['id'] = (int) post('id');
                $pdo->prepare("UPDATE avisos SET titulo = :titulo, contenido = :contenido, categoria = :categoria,
                               autor = :autor, fecha = :fecha WHERE id = :id")->execute($datos);
                $mensaje = 'Aviso actualizado correctamente.';
            }
        } elseif ($accion === 'eliminar') {
            $pdo->prepare("DELETE FROM avisos WHERE id = ?")->execute([(int) post('id')]);
            $mensaje = 'Aviso eliminado.';
        }
    } catch (Throwable $ex) {
        $mensaje = 'Error: ' . $ex->getMessage();
        $tipo = 'danger';
    }
}

$editItem = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM avisos WHERE id = ?");
    $stmt->execute([(int) $_GET['editar']]);
    $editItem = $stmt->fetch() ?: null;
}

$avisos = $pdo->query("SELECT * FROM avisos ORDER BY fecha DESC, id DESC")->fetchAll();

$paginaActiva = 'avisos';
$titulo = 'Avisos y mensajes';
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
    <h3 class="card-title"><?php echo $editItem ? 'Editar aviso' : 'Publicar aviso'; ?></h3>
    <?php if ($editItem): ?>
      <a href="avisos.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Cancelar</a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="accion" value="<?php echo $editItem ? 'actualizar' : 'crear'; ?>">
      <?php if ($editItem): ?><input type="hidden" name="id" value="<?php echo (int) $editItem['id']; ?>"><?php endif; ?>

      <div class="col-md-6">
        <label class="form-label">Título *</label>
        <input type="text" name="titulo" class="form-control" required value="<?php echo e($editItem['titulo'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Categoría</label>
        <select name="categoria" class="form-select">
          <?php foreach (['General', 'Académico', 'Administrativo', 'Eventos'] as $val): ?>
            <option value="<?php echo $val; ?>" <?php echo ($editItem['categoria'] ?? 'General') === $val ? 'selected' : ''; ?>><?php echo $val; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?php echo e($editItem['fecha'] ?? date('Y-m-d')); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Autor</label>
        <input type="text" name="autor" class="form-control" value="<?php echo e($editItem['autor'] ?? $nombreUsuario); ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Contenido</label>
        <textarea name="contenido" rows="3" class="form-control"><?php echo e($editItem['contenido'] ?? ''); ?></textarea>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-<?php echo $editItem ? 'save' : 'megaphone'; ?>"></i>
          <?php echo $editItem ? 'Actualizar' : 'Publicar'; ?>
        </button>
      </div>
    </form>
  </div>
</div>

<div class="card card-table">
  <div class="card-header"><h3 class="card-title">Avisos publicados (<?php echo count($avisos); ?>)</h3></div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Título</th><th>Categoría</th><th>Autor</th><th>Fecha</th><th class="text-end">Acciones</th></tr>
        </thead>
        <tbody>
          <?php if (empty($avisos)): ?>
            <tr><td colspan="6" class="empty-state"><i class="bi bi-megaphone"></i>No hay avisos publicados.</td></tr>
          <?php else: foreach ($avisos as $a): ?>
            <tr>
              <td><?php echo (int) $a['id']; ?></td>
              <td>
                <?php echo e($a['titulo']); ?>
                <?php if ($a['contenido']): ?><br><small class="text-secondary"><?php echo e(mb_strimwidth($a['contenido'], 0, 80, '…')); ?></small><?php endif; ?>
              </td>
              <td><span class="badge text-bg-primary badge-estado"><?php echo e($a['categoria']); ?></span></td>
              <td><?php echo e($a['autor'] ?: '—'); ?></td>
              <td><?php echo e(date('d/m/Y', strtotime($a['fecha']))); ?></td>
              <td class="text-end">
                <a href="avisos.php?editar=<?php echo (int) $a['id']; ?>" class="btn btn-sm btn-outline-primary btn-icon" title="Editar"><i class="bi bi-pencil"></i></a>
                <form action="avisos.php" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este aviso?');">
                  <input type="hidden" name="accion" value="eliminar">
                  <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
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
