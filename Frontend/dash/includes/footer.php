          </div>
        </div>
      </main>
      <!--end::App Main-->

      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Ciclo lectivo 2026</div>
        <strong>Mi Colegio</strong> &copy; 2026. Todos los derechos reservados.
      </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="./js/adminlte.js"></script>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector('.sidebar-wrapper');
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && globalThis.OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: { theme: 'os-theme-light', autoHide: 'leave', clickScroll: true },
          });
        }

        <?php if (!empty($toastBienvenida)): ?>
        const toastEl = document.getElementById('toast-bienvenida');
        if (toastEl && globalThis.bootstrap) {
          new bootstrap.Toast(toastEl, { delay: 5000, autohide: true }).show();
        }
        if (globalThis.history && history.replaceState) {
          history.replaceState(null, '', location.pathname);
        }
        <?php endif; ?>
      });
    </script>

    <?php echo $pageScripts ?? ''; ?>
  </body>
</html>
