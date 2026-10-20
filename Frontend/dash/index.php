<?php
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/auth.php';

$toastBienvenida = isset($_GET['welcome']);

// ---- Métricas ----
$totalEstudiantes = (int) $pdo->query("SELECT COUNT(*) FROM estudiantes")->fetchColumn();
$totalDocentes    = (int) $pdo->query("SELECT COUNT(*) FROM docentes WHERE estado = 'Activo'")->fetchColumn();
$totalCursos      = (int) $pdo->query("SELECT COUNT(*) FROM cursos")->fetchColumn();

$asist = $pdo->query("
    SELECT COUNT(*) AS total,
           SUM(estado IN ('Presente','Tarde')) AS presentes
    FROM asistencia
    WHERE fecha = CURDATE()
")->fetch();
$asistTotal = (int) $asist['total'];
$asistPct = $asistTotal > 0 ? (int) round($asist['presentes'] / $asistTotal * 100) : null;

// ---- Matrícula por mes (año actual) ----
$meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
$matriculaData = array_fill(0, 12, 0);
$stmt = $pdo->query("
    SELECT MONTH(creado_en) AS m, COUNT(*) AS c
    FROM estudiantes
    WHERE YEAR(creado_en) = YEAR(CURDATE())
    GROUP BY MONTH(creado_en)
");
foreach ($stmt as $row) {
    $matriculaData[(int) $row['m'] - 1] = (int) $row['c'];
}

// ---- Distribución por nivel ----
$niveles = ['Preescolar' => 0, 'Primaria' => 0, 'Bachillerato' => 0];
$stmt = $pdo->query("
    SELECT cu.nivel, COUNT(e.id) AS c
    FROM estudiantes e
    JOIN cursos cu ON cu.id = e.curso_id
    GROUP BY cu.nivel
");
foreach ($stmt as $row) {
    if (isset($niveles[$row['nivel']])) {
        $niveles[$row['nivel']] = (int) $row['c'];
    }
}

// ---- Últimas matrículas ----
$ultimas = $pdo->query("
    SELECT e.nombre, e.apellido, e.estado, e.creado_en, cu.nombre AS curso
    FROM estudiantes e
    LEFT JOIN cursos cu ON cu.id = e.curso_id
    ORDER BY e.creado_en DESC
    LIMIT 5
")->fetchAll();

// ---- Avisos recientes ----
$avisos = $pdo->query("
    SELECT titulo, categoria, autor, fecha
    FROM avisos
    ORDER BY fecha DESC, id DESC
    LIMIT 4
")->fetchAll();

$paginaActiva = 'dashboard';
$titulo = 'Panel general';

$chartData = json_encode([
    'meses' => $meses,
    'matricula' => $matriculaData,
    'niveles' => array_keys($niveles),
    'nivelesData' => array_values($niveles),
]);

ob_start();
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
  (() => {
    'use strict';
    const { Chart } = globalThis;
    if (!Chart) return;
    const data = <?php echo $chartData; ?>;
    const cssVar = (name, fallback = '') =>
      getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
    const applyDefaults = () => {
      const d = Chart.defaults;
      d.font.family = "'Inter', sans-serif";
      d.font.size = 12;
      d.color = cssVar('--bs-body-color', '#e0e0f0');
      d.borderColor = 'rgba(120,120,160,0.15)';
      d.maintainAspectRatio = false;
      d.elements.line.borderWidth = 2;
      d.elements.point.radius = 3;
      d.elements.point.hoverRadius = 6;
      d.plugins.legend.labels.usePointStyle = true;
      d.plugins.legend.labels.boxWidth = 8;
      d.plugins.legend.labels.padding = 16;
    };
    applyDefaults();

    const line = document.querySelector('#matricula-chart');
    if (line) {
      new Chart(line, {
        type: 'line',
        data: {
          labels: data.meses,
          datasets: [{
            label: 'Nuevas matrículas',
            data: data.matricula,
            borderColor: '#00f0ff',
            backgroundColor: (context) => {
              const { ctx, chartArea } = context.chart;
              if (!chartArea) return 'rgba(0,240,255,0.2)';
              const g = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
              g.addColorStop(0, 'rgba(0,240,255,0.35)');
              g.addColorStop(1, 'rgba(0,240,255,0)');
              return g;
            },
            pointBackgroundColor: '#00f0ff',
            fill: true,
            tension: 0.4,
          }],
        },
        options: {
          plugins: { legend: { display: false } },
          scales: { x: { grid: { display: false } }, y: { beginAtZero: true, border: { display: false } } },
        },
      });
    }

    const doughnut = document.querySelector('#nivel-chart');
    if (doughnut) {
      new Chart(doughnut, {
        type: 'doughnut',
        data: {
          labels: data.niveles,
          datasets: [{
            data: data.nivelesData,
            backgroundColor: ['#00f0ff', '#b24bf3', '#ff2d95'],
            borderColor: cssVar('--bs-body-bg', '#050510'),
            borderWidth: 2,
          }],
        },
        options: { cutout: '62%', plugins: { legend: { position: 'bottom' } } },
      });
    }

    new MutationObserver(() => {
      applyDefaults();
      for (const chart of Object.values(Chart.instances)) chart.update('none');
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
  })();
</script>
<?php
$pageScripts = ob_get_clean();

require __DIR__ . '/includes/header.php';
?>
<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box text-bg-primary">
      <div class="inner">
        <h3><?php echo number_format($totalEstudiantes, 0, ',', '.'); ?></h3>
        <p>Estudiantes matriculados</p>
      </div>
      <i class="small-box-icon bi bi-people-fill"></i>
      <a href="estudiantes.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        Ver estudiantes <i class="bi bi-link-45deg"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box text-bg-success">
      <div class="inner">
        <h3><?php echo number_format($totalDocentes, 0, ',', '.'); ?></h3>
        <p>Docentes activos</p>
      </div>
      <i class="small-box-icon bi bi-person-workspace"></i>
      <a href="docentes.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        Ver docentes <i class="bi bi-link-45deg"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box text-bg-warning">
      <div class="inner">
        <h3><?php echo number_format($totalCursos, 0, ',', '.'); ?></h3>
        <p>Cursos activos</p>
      </div>
      <i class="small-box-icon bi bi-journal-bookmark-fill"></i>
      <a href="cursos.php" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
        Ver cursos <i class="bi bi-link-45deg"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box text-bg-danger">
      <div class="inner">
        <h3><?php echo $asistPct === null ? '--' : $asistPct; ?><sup class="fs-5">%</sup></h3>
        <p>Asistencia hoy</p>
      </div>
      <i class="small-box-icon bi bi-calendar-check-fill"></i>
      <a href="asistencia.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
        Ver asistencia <i class="bi bi-link-45deg"></i>
      </a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-7">
    <div class="card mb-4">
      <div class="card-header"><h3 class="card-title">Matrícula por mes</h3></div>
      <div class="card-body">
        <div class="position-relative" style="height: 300px">
          <canvas id="matricula-chart" role="img" aria-label="Estudiantes matriculados por mes"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card mb-4">
      <div class="card-header"><h3 class="card-title">Distribución por nivel</h3></div>
      <div class="card-body">
        <div class="position-relative" style="height: 300px">
          <canvas id="nivel-chart" role="img" aria-label="Distribución de estudiantes por nivel"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-7">
    <div class="card card-table mb-4">
      <div class="card-header">
        <h3 class="card-title">Últimas matrículas</h3>
        <a href="estudiantes.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr><th>Estudiante</th><th>Curso</th><th>Fecha</th><th>Estado</th></tr>
            </thead>
            <tbody>
              <?php if (empty($ultimas)): ?>
                <tr><td colspan="4" class="empty-state"><i class="bi bi-inbox"></i>Sin registros.</td></tr>
              <?php else: foreach ($ultimas as $u): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <span class="pf-avatar"><?php echo e(iniciales($u['nombre'], $u['apellido'])); ?></span>
                      <span class="pf-name"><?php echo e($u['nombre'] . ' ' . $u['apellido']); ?></span>
                    </div>
                  </td>
                  <td><?php echo e($u['curso'] ?? '—'); ?></td>
                  <td><?php echo e(date('d/m/Y', strtotime($u['creado_en']))); ?></td>
                  <td>
                    <?php
                    $est = $u['estado'];
                    $cls = $est === 'Activo' ? 'success' : ($est === 'Pendiente' ? 'warning' : 'secondary');
                    ?>
                    <span class="badge text-bg-<?php echo $cls; ?> badge-estado"><?php echo e($est); ?></span>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card card-table mb-4">
      <div class="card-header">
        <h3 class="card-title">Avisos recientes</h3>
        <a href="avisos.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          <?php if (empty($avisos)): ?>
            <li class="list-group-item bg-transparent empty-state"><i class="bi bi-megaphone"></i>Sin avisos.</li>
          <?php else: foreach ($avisos as $a): ?>
            <li class="list-group-item d-flex gap-3 bg-transparent">
              <i class="bi bi-megaphone-fill text-primary fs-4"></i>
              <div>
                <p class="mb-0 fw-semibold"><?php echo e($a['titulo']); ?></p>
                <small class="text-secondary">
                  <?php echo e($a['categoria']); ?> · <?php echo e(date('d/m/Y', strtotime($a['fecha']))); ?>
                </small>
              </div>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php
require __DIR__ . '/includes/footer.php';
