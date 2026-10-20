<?php
// Usar 127.0.0.1 y el puerto 3306 (por defecto de XAMPP)
$host = '127.0.0.1';
$port = '3306';
$db   = 'sistema_login';
$user = 'root';
$pass = ''; // Deja la cadena vacía si root no tiene contraseña en XAMPP

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    // Descomenta la siguiente línea solo para probar si funcionó
    // echo "¡Conexión exitosa!";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>