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
$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

if ($id == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'error' => 'No puedes eliminarte a ti mismo']);
    exit;
}

try {
    // Si queremos preservar historia, en lugar de borrar podríamos hacer un soft-delete (estado = inactivo). 
    // Pero asumiendo q se permite por ahora, con precaucion por ON DELETE restrict.
    // Revisar si el usuario tiene ventas.
    $stmtV = $pdo->prepare("SELECT COUNT(id) FROM ventas WHERE usuario_id = :id");
    $stmtV->execute([':id' => $id]);
    if ($stmtV->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'No puedes eliminar un usuario con ventas registradas']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
}
