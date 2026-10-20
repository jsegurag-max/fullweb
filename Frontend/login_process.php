<?php
header('Content-Type: application/json');
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Por favor completa todos los campos.']);
    exit;
}

// Consulta de usuario con Sentencias Preparadas (PDO)
$stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    
    echo json_encode([
        'success' => true, 
        'message' => '¡Bienvenido! Redirigiendo...', 
        'redirect' => 'dash/index.php?welcome=1'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
}