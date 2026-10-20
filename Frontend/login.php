<?php
session_start();
require 'conexion.php';

// Si ya inició sesión, redirigir al panel
if (isset($_SESSION['usuario_id'])) {
    header("Location: dash/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Iniciar sesión o crear cuenta - Portafolio Jeremy Segura">
    <title>Login | Jeremy Segura</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <main class="login-page">
        <a href="index.html" class="login-back">
            <i class="fas fa-arrow-left"></i> Volver al portafolio
        </a>

        <section class="login-card" aria-labelledby="auth-title">
            <header class="login-header">
                <span class="login-badge"><i class="fas fa-user"></i></span>
                <h1 id="auth-title">Bienvenido</h1>
                <p>Accede a tu cuenta o crea una nueva.</p>
            </header>

            <div class="login-tabs" role="tablist">
                <button class="login-tab active" id="tab-login" data-tab="login" role="tab" aria-selected="true">Iniciar Sesión</button>
                <button class="login-tab" id="tab-register" data-tab="register" role="tab" aria-selected="false">Registrarse</button>
            </div>

            <!-- LOGIN -->
            <form action="login_process.php" method="POST" class="login-form" id="login-form" novalidate autocomplete="on">
                <div class="field">
                    <input type="email" id="login-email" name="email" placeholder=" " required autocomplete="email">
                    <label for="login-email">Correo electrónico</label>
                    <small class="field-error" id="login-email-error"></small>
                </div>
                <div class="field">
                    <input type="password" id="login-password" name="password" placeholder=" " required minlength="6" autocomplete="current-password">
                    <label for="login-password">Contraseña</label>
                    <button type="button" class="toggle-pass" data-target="login-password" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                    <small class="field-error" id="login-password-error"></small>
                </div>
                <label class="remember">
                    <input type="checkbox" id="login-remember" name="remember" checked> Recuérdame
                </label>
                <button type="submit" class="btn-login-submit">
                    <span>Entrar</span><i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            <!-- REGISTRO -->
            <form action="register_process.php" method="POST" class="login-form hidden" id="register-form" novalidate autocomplete="on">
                <div class="field">
                    <input type="text" id="reg-name" name="name" placeholder=" " required minlength="3" autocomplete="name">
                    <label for="reg-name">Nombre completo</label>
                    <small class="field-error" id="reg-name-error"></small>
                </div>
                <div class="field">
                    <input type="email" id="reg-email" name="email" placeholder=" " required autocomplete="email">
                    <label for="reg-email">Correo electrónico</label>
                    <small class="field-error" id="reg-email-error"></small>
                </div>
                <div class="field">
                    <input type="password" id="reg-password" name="password" placeholder=" " required minlength="6" autocomplete="new-password">
                    <label for="reg-password">Contraseña (mín. 6)</label>
                    <button type="button" class="toggle-pass" data-target="reg-password" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                    <small class="field-error" id="reg-password-error"></small>
                </div>
                <div class="field">
                    <input type="password" id="reg-confirm" name="confirm_password" placeholder=" " required autocomplete="new-password">
                    <label for="reg-confirm">Confirmar contraseña</label>
                    <small class="field-error" id="reg-confirm-error"></small>
                </div>
                <button type="submit" class="btn-login-submit">
                    <span>Crear cuenta</span><i class="fas fa-user-plus"></i>
                </button>
            </form>

            <p class="login-status" id="auth-status" role="status"></p>
        </section>
    </main>

    <script src="login.js"></script>
</body>
</html>