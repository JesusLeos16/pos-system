<?php
include '../db/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crear.php');
    exit;
}

// Sanitizar datos
$nombre       = trim($_POST['nombre']);
$sku          = trim($_POST['sku']);
$precio       = floatval($_POST['precio']);
$stock        = intval($_POST['stock']);
$stock_minimo = 5;
$id_categoria = !empty($_POST['id_categoria']) ? intval($_POST['id_categoria']) : null;
$imagen = trim($_POST['imagen_url']) ?: null;
// Insertar en DB
try {
    $stmt = $pdo->prepare("
        INSERT INTO productos (nombre, sku, precio, stock, stock_minimo, id_categoria, imagen)
        VALUES (:nombre, :sku, :precio, :stock, :stock_minimo, :id_categoria, :imagen)
    ");

    $stmt->execute([
        ':nombre'       => $nombre,
        ':sku'          => $sku,
        ':precio'       => $precio,
        ':stock'        => $stock,
        ':stock_minimo' => $stock_minimo,
        ':id_categoria' => $id_categoria,
        ':imagen'       => $imagen,
    ]);

    header('Location: crear.php?success=1');
    exit;
} catch (PDOException $e) {
    // SKU duplicado
    header('Location: crear.php?error=sku_duplicado');
    exit;
}
