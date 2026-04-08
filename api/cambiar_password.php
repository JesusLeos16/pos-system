<?php
include '../db/auth.php';
include '../db/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$actual = trim($input['password_actual'] ?? '');
$nueva = trim($input['password_nueva'] ?? '');

if (empty($actual) || empty($nueva)) {
    echo json_encode(['success' => false, 'error' => 'Ambas contraseñas son requeridas']);
    exit;
}

try {
    $id = $_SESSION['usuario_id'];
    
    // Verificar pass actual
    $stmt = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($actual, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'La contraseña actual es incorrecta']);
        exit;
    }

    // Actualizar
    $hashed = password_hash($nueva, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE usuarios SET password = :pass WHERE id = :id");
    $upd->execute([':pass' => $hashed, ':id' => $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos']);
}
