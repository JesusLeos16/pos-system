<?php
include '../db/auth.php';
include '../db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Pages/inventario.php');
    exit;
}

$id            = intval($_POST['id']);
$nombre        = trim($_POST['nombre']);
$sku           = trim($_POST['sku']);
$precio        = floatval($_POST['precio']);
$stock         = intval($_POST['stock']);
$stock_minimo  = intval($_POST['stock_minimo']);
$id_categoria  = !empty($_POST['id_categoria']) ? intval($_POST['id_categoria']) : null;
$imagen        = trim($_POST['imagen_url']) ?: null;

try {
    $stmt = $pdo->prepare("
        UPDATE productos 
        SET nombre = :nombre, sku = :sku, precio = :precio, stock = :stock, 
            stock_minimo = :stock_minimo, id_categoria = :id_categoria, imagen = :imagen
        WHERE id = :id
    ");

    $stmt->execute([
        ':id'            => $id,
        ':nombre'        => $nombre,
        ':sku'           => $sku,
        ':precio'        => $precio,
        ':stock'         => $stock,
        ':stock_minimo'  => $stock_minimo,
        ':id_categoria'  => $id_categoria,
        ':imagen'        => $imagen,
    ]);

    header('Location: ../Pages/inventario.php?success=actualizado');
    exit;
} catch (PDOException $e) {
    header('Location: editar.php?id=' . $id . '&error=1');
    exit;
}
