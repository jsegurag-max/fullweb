<?php
session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
</head>
<body>
    <h2>¡Bienvenido, <?= htmlspecialchars($_SESSION['usuario_email']) ?>!</h2>
    <p>Has iniciado sesión correctamente en el sistema.</p>
    
    <hr>
    <a href="logout.php">Cerrar Sesión</a>
</body>
</html>