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

if (empty($nombre)) {
    echo json_encode(['success' => false, 'error' => 'El nombre de la categoría es requerido']);
    exit;
}

try {
    // Ver si ya existe
    $stmtC = $pdo->prepare("SELECT id FROM categorias WHERE nombre = :nombre");
    $stmtC->execute([':nombre' => $nombre]);
    if ($stmtC->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Esta categoría ya existe']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
    $stmt->execute([':nombre' => $nombre]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'nombre' => htmlspecialchars($nombre)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}
