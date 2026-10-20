<?php
header('Content-Type: application/json');
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (strlen($name) < 3 || !$email || strlen($password) < 6 || $password !== $confirm) {
    echo json_encode(['success' => false, 'message' => 'Por favor valida la información ingresada.']);
    exit;
}

// Verificar si el correo ya existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado.']);
    exit;
}

// Cifrado seguro de contraseña
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
if ($stmt->execute([$name, $email, $passwordHash])) {
    echo json_encode(['success' => true, 'message' => 'Cuenta creada con éxito. Ya puedes iniciar sesión.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ocurrió un error al registrar el usuario.']);
}