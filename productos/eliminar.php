<?php
include '../db/auth.php';
include '../db/conexion.php';

if (!isset($_GET['id'])) {
    header('Location: ../Pages/inventario.php');
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: ../Pages/inventario.php?success=eliminado');
    exit;
} catch (PDOException $e) {
    header('Location: ../Pages/inventario.php?error=eliminar');
    exit;
}
