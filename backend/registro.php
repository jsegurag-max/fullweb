<?php
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Encriptar la contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Prevenir inyección SQL
        $stmt = $pdo->prepare("INSERT INTO usuarios (email, password) VALUES (?, ?)");
        
        try {
            $stmt->execute([$email, $password_hashed]);
            $mensaje = "Usuario registrado con éxito. <a href='login.php'>Iniciar sesión</a>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensaje = "El correo ya está registrado.";
            } else {
                $mensaje = "Error al registrar el usuario: " . $e->getMessage();
            }
        }
    } else {
        $mensaje = "Por favor completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
</head>
<body>
    <h2>Registro</h2>
    <?php if ($mensaje): ?>
        <p><?= $mensaje ?></p>
    <?php endif; ?>

    <form method="POST" action="registro.php">
        <label>Correo Electrónico:</label><br>
        <input type="email" name="email" required><br><br>
        
        <label>Contraseña:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Registrarse</button>
    </form>
    <br>
    <a href="login.php">¿Ya tienes cuenta? Inicia sesión aquí</a>
</body>
</html>