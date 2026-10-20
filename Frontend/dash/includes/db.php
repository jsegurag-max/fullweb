<?php
// Conexión PDO compartida del panel escolar
$host = '127.0.0.1';
$port = '3306';
$db   = 'sistema_login';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Lee un valor de $_POST de forma segura
function post($key, $default = '')
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : $default;
}

// Escapa para HTML
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Iniciales para avatares (ej: "Ana López" -> "AL")
function iniciales($nombre, $apellido = '')
{
    $n = $nombre !== '' ? mb_substr(trim($nombre), 0, 1) : '';
    $a = $apellido !== '' ? mb_substr(trim($apellido), 0, 1) : '';
    return mb_strtoupper($n . $a);
}
