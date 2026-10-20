<?php
// Variables esperadas: $paginaActiva, $titulo
$paginaActiva = $paginaActiva ?? '';
$titulo = $titulo ?? 'Panel';
$toastBienvenida = $toastBienvenida ?? false;

function navActive($key, $actual)
{
    return $key === $actual ? ' active' : '';
}
?>
<!doctype html>
<html lang="es" data-bs-theme="dark">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo e($titulo); ?> | Mi Colegio</title>

    <!--begin::Theme Init-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try { stored = localStorage.getItem(STORAGE_KEY); } catch {}
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'dark';
        if (stored === 'dark' || stored === 'light') resolved = stored;
        else if (authored === 'dark' || authored === 'light') resolved = authored;
        else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) resolved = 'dark';
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
      })();
    </script>
    <!--end::Theme Init-->

    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#00f0ff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#050510" media="(prefers-color-scheme: dark)" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="./css/adminlte.css" />
    <link rel="stylesheet" href="./css/portfolio-theme.css" />
    <link rel="stylesheet" href="./css/crud.css" />
  </head>

  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <?php if ($toastBienvenida): ?>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1080">
      <div id="toast-bienvenida" class="toast pf-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body d-flex align-items-center gap-3">
            <span class="pf-toast-icon"><i class="bi bi-check-lg"></i></span>
            <div>
              <strong class="d-block">¡Inicio de sesión exitoso!</strong>
              Bienvenido, <?php echo e($nombreUsuario); ?>.
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="app-wrapper">
      <!--begin::Header-->
      <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Mostrar/ocultar menú">
                <i class="bi bi-list"></i>
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="./index.php" class="nav-link">
                <i class="bi bi-grid-1x2 me-1" aria-hidden="true"></i>
                Panel general
              </a>
            </li>
            <li class="nav-item d-none d-md-block">
              <a href="../index.html" class="nav-link">
                <i class="bi bi-house-door me-1" aria-hidden="true"></i>
                Volver al portafolio
              </a>
            </li>
          </ul>

          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Pantalla completa">
                <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link" href="#" id="bd-theme" aria-label="Cambiar tema" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
                <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
                <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme" style="--bs-dropdown-min-width: 8rem">
                <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false"><i class="bi bi-sun-fill me-2"></i> Claro <i class="bi bi-check-lg ms-auto d-none"></i></button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="dark" aria-pressed="true"><i class="bi bi-moon-fill me-2"></i> Oscuro <i class="bi bi-check-lg ms-auto d-none"></i></button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false"><i class="bi bi-circle-half me-2"></i> Auto <i class="bi bi-check-lg ms-auto d-none"></i></button></li>
              </ul>
            </li>
            <li class="nav-item dropdown user-menu">
              <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <span class="user-image rounded-circle shadow text-bg-primary d-inline-flex align-items-center justify-content-center">
                  <i class="bi bi-person-fill"></i>
                </span>
                <span class="d-none d-xl-inline"><?php echo e($nombreUsuario); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                <li class="user-header text-bg-primary">
                  <span class="rounded-circle shadow text-bg-dark d-inline-flex align-items-center justify-content-center" style="width: 90px; height: 90px; font-size: 2.5rem">
                    <i class="bi bi-person-fill"></i>
                  </span>
                  <p><?php echo e($nombreUsuario); ?><small>Sesión activa</small></p>
                </li>
                <li class="user-footer">
                  <a href="../index.html" class="btn btn-outline-secondary">Portafolio</a>
                  <a href="../logout.php" class="btn btn-outline-danger float-end">Cerrar sesión</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!--end::Header-->

      <!--begin::Sidebar-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
          <a href="./index.php" class="brand-link">
            <span class="brand-image opacity-75 shadow d-inline-flex align-items-center justify-content-center rounded-circle text-bg-primary">
              <i class="bi bi-mortarboard-fill"></i>
            </span>
            <span class="brand-text fw-light">Mi Colegio</span>
          </a>
        </div>

        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Navegación principal">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
              <li class="nav-item">
                <a href="./index.php" class="nav-link<?php echo navActive('dashboard', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-speedometer2"></i>
                  <p>Panel general</p>
                </a>
              </li>

              <li class="nav-header">GESTIÓN ACADÉMICA</li>
              <li class="nav-item">
                <a href="./estudiantes.php" class="nav-link<?php echo navActive('estudiantes', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-people-fill"></i>
                  <p>Estudiantes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./docentes.php" class="nav-link<?php echo navActive('docentes', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-person-workspace"></i>
                  <p>Docentes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./cursos.php" class="nav-link<?php echo navActive('cursos', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-journal-bookmark-fill"></i>
                  <p>Cursos y grados</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./calificaciones.php" class="nav-link<?php echo navActive('calificaciones', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-clipboard-data-fill"></i>
                  <p>Calificaciones</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./asistencia.php" class="nav-link<?php echo navActive('asistencia', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-calendar-check-fill"></i>
                  <p>Asistencia</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./horarios.php" class="nav-link<?php echo navActive('horarios', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-calendar3"></i>
                  <p>Horarios</p>
                </a>
              </li>

              <li class="nav-header">ADMINISTRACIÓN</li>
              <li class="nav-item">
                <a href="./pagos.php" class="nav-link<?php echo navActive('pagos', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-cash-coin"></i>
                  <p>Pagos y matrícula</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./avisos.php" class="nav-link<?php echo navActive('avisos', $paginaActiva); ?>">
                  <i class="nav-icon bi bi-megaphone-fill"></i>
                  <p>Avisos y mensajes</p>
                </a>
              </li>
            </ul>

            <div class="p-3 mt-3 border-top border-secondary border-opacity-25">
              <a href="../index.html" class="btn btn-sm btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-house-door" aria-hidden="true"></i>
                Volver al portafolio
              </a>
            </div>
          </nav>
        </div>
      </aside>
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3"><?php echo e($titulo); ?></h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="./index.php">Panel</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo e($titulo); ?></li>
                  </ol>
                </nav>
              </div>
            </div>
          </div>
        </div>

        <div class="app-content">
          <div class="container-fluid">
