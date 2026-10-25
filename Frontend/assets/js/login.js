// ============================================
// LOGIN / REGISTRO - JS (Backend PHP Integration)
// ============================================
(function () {
    const tabs = document.querySelectorAll('.login-tab');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const status = document.getElementById('auth-status');
    const title = document.getElementById('auth-title');
    if (!loginForm || !registerForm) return;

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function setStatus(msg, type = '') {
        status.textContent = msg;
        status.className = 'login-status ' + type;
    }

    function setError(id, msg) {
        const el = document.getElementById(id);
        if (el) el.textContent = msg || '';
    }

    function switchTab(tab) {
        tabs.forEach(t => {
            const active = t.dataset.tab === tab;
            t.classList.toggle('active', active);
            t.setAttribute('aria-selected', active);
        });
        loginForm.classList.toggle('hidden', tab !== 'login');
        registerForm.classList.toggle('hidden', tab !== 'register');
        title.textContent = tab === 'login' ? 'Bienvenido' : 'Crear cuenta';
        setStatus('');
    }

    tabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.tab)));

    if (new URLSearchParams(location.search).get('tab') === 'register') {
        switchTab('register');
    }

    // Mostrar / ocultar contraseña
    document.querySelectorAll('.toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            const icon = btn.querySelector('i');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    });

    // ---- REGISTRO ----
    registerForm.addEventListener('submit', async e => {
        e.preventDefault();
        const name = document.getElementById('reg-name').value.trim();
        const email = document.getElementById('reg-email').value.trim().toLowerCase();
        const pass = document.getElementById('reg-password').value;
        const confirm = document.getElementById('reg-confirm').value;
        let ok = true;

        setError('reg-name-error', '');
        setError('reg-email-error', '');
        setError('reg-password-error', '');
        setError('reg-confirm-error', '');

        if (name.length < 3) { setError('reg-name-error', 'Mínimo 3 caracteres.'); ok = false; }
        if (!emailRe.test(email)) { setError('reg-email-error', 'Email no válido.'); ok = false; }
        if (pass.length < 6) { setError('reg-password-error', 'Mínimo 6 caracteres.'); ok = false; }
        if (confirm !== pass) { setError('reg-confirm-error', 'No coinciden.'); ok = false; }
        if (!ok) return;

        const btn = registerForm.querySelector('button[type="submit"]');
        btn.disabled = true;

        try {
            const formData = new FormData(registerForm);
            const response = await fetch('register_process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                setStatus(data.message, 'success');
                registerForm.reset();
                setTimeout(() => switchTab('login'), 1200);
            } else {
                setStatus(data.message, 'error');
            }
        } catch (error) {
            setStatus('Error de conexión con el servidor.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    // ---- LOGIN ----
    loginForm.addEventListener('submit', async e => {
        e.preventDefault();
        const email = document.getElementById('login-email').value.trim().toLowerCase();
        const pass = document.getElementById('login-password').value;
        let ok = true;

        setError('login-email-error', '');
        setError('login-password-error', '');

        if (!emailRe.test(email)) { setError('login-email-error', 'Email no válido.'); ok = false; }
        if (pass.length < 6) { setError('login-password-error', 'Mínimo 6 caracteres.'); ok = false; }
        if (!ok) return;

        const btn = loginForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span>Entrando...</span><i class="fas fa-spinner fa-spin"></i>';

        try {
            const formData = new FormData(loginForm);
            const response = await fetch('login_process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                setStatus(data.message, 'success');
                setTimeout(() => { location.href = data.redirect || 'dash/index.php?welcome=1'; }, 800);
            } else {
                setStatus(data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<span>Entrar</span><i class="fas fa-sign-in-alt"></i>';
            }
        } catch (error) {
            setStatus('Error de conexión con el servidor.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<span>Entrar</span><i class="fas fa-sign-in-alt"></i>';
        }
    });
})();