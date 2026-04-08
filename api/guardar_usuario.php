<?php
include '../db/auth.php';
include '../db/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'No tienes permisos de administrador']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$nombre = trim($input['nombre'] ?? '');
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$rol = trim($input['rol'] ?? 'cajero');

// Update or Create
$id = intval($input['id'] ?? 0);

if (empty($nombre) || empty($username)) {
    echo json_encode(['success' => false, 'error' => 'Nombre y Username son requeridos']);
    exit;
}

try {
    if ($id > 0) {
        // Update user
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=:nombre, username=:username, password=:password, rol=:rol WHERE id=:id");
            $stmt->execute([':nombre' => $nombre, ':username' => $username, ':password' => $hashed, ':rol' => $rol, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=:nombre, username=:username, rol=:rol WHERE id=:id");
            $stmt->execute([':nombre' => $nombre, ':username' => $username, ':rol' => $rol, ':id' => $id]);
        }
        echo json_encode(['success' => true]);
    } else {
        // Create user
        if (empty($password)) {
            echo json_encode(['success' => false, 'error' => 'La contraseña es requerida para usuarios nuevos']);
            exit;
        }

        // Ver si ya existe el usr
        $stmtU = $pdo->prepare("SELECT id FROM usuarios WHERE username = :username");
        $stmtU->execute([':username' => $username]);
        if ($stmtU->fetch()) {
            echo json_encode(['success' => false, 'error' => 'El username ya está en uso']);
            exit;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, username, password, rol) VALUES (:nombre, :username, :password, :rol)");
        $stmt->execute([':nombre' => $nombre, ':username' => $username, ':password' => $hashed, ':rol' => $rol]);

        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}
