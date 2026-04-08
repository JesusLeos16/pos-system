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

try {
    // Verificar que no haya productos usándola
    $stmtP = $pdo->prepare("SELECT COUNT(id) FROM productos WHERE id_categoria = :id");
    $stmtP->execute([':id' => $id]);
    if ($stmtP->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'No se puede eliminar porque hay productos que usan esta categoría']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar: ' . $e->getMessage()]);
}
