<?php
include '../db/auth.php';
include '../db/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leer datos del body JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items'])) {
    echo json_encode(['success' => false, 'error' => 'No hay productos en la orden']);
    exit;
}

$items = $input['items'];
$metodo_pago = $input['metodo_pago'] ?? 'efectivo';
$monto_recibido = isset($input['monto_recibido']) ? floatval($input['monto_recibido']) : null;

try {
    $pdo->beginTransaction();

    // Calcular totales
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += floatval($item['precio']) * intval($item['cantidad']);
    }
    $impuesto = round($subtotal * 0.16, 2);
    $total = round($subtotal + $impuesto, 2);
    $cambio = ($monto_recibido !== null) ? round($monto_recibido - $total, 2) : null;

    // Generar número de orden
    $numero_orden = 'KK-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Insertar venta
    $stmt = $pdo->prepare("
        INSERT INTO ventas (numero_orden, subtotal, impuesto, total, metodo_pago, monto_recibido, cambio, usuario_id)
        VALUES (:numero_orden, :subtotal, :impuesto, :total, :metodo_pago, :monto_recibido, :cambio, :usuario_id)
    ");
    $stmt->execute([
        ':numero_orden'   => $numero_orden,
        ':subtotal'       => $subtotal,
        ':impuesto'       => $impuesto,
        ':total'          => $total,
        ':metodo_pago'    => $metodo_pago,
        ':monto_recibido' => $monto_recibido,
        ':cambio'         => $cambio,
        ':usuario_id'     => $_SESSION['usuario_id'],
    ]);

    $venta_id = $pdo->lastInsertId();

    // Insertar detalle y descontar stock
    $stmtDetalle = $pdo->prepare("
        INSERT INTO venta_detalle (venta_id, producto_id, nombre_producto, cantidad, precio_unitario, subtotal)
        VALUES (:venta_id, :producto_id, :nombre_producto, :cantidad, :precio_unitario, :subtotal)
    ");

    $stmtStock = $pdo->prepare("
        UPDATE productos SET stock = stock - :cantidad WHERE id = :id AND stock >= :cantidad
    ");

    foreach ($items as $item) {
        $cantidad = intval($item['cantidad']);
        $precio = floatval($item['precio']);
        $itemSubtotal = round($precio * $cantidad, 2);

        // Insertar detalle
        $stmtDetalle->execute([
            ':venta_id'        => $venta_id,
            ':producto_id'     => intval($item['id']),
            ':nombre_producto' => $item['nombre'],
            ':cantidad'        => $cantidad,
            ':precio_unitario' => $precio,
            ':subtotal'        => $itemSubtotal,
        ]);

        // Descontar stock
        $stmtStock->execute([
            ':cantidad' => $cantidad,
            ':id'       => intval($item['id']),
        ]);

        // Verificar que se descontó
        if ($stmtStock->rowCount() === 0) {
            throw new Exception("Stock insuficiente para: " . $item['nombre']);
        }
    }

    $pdo->commit();

    echo json_encode([
        'success'       => true,
        'venta_id'      => $venta_id,
        'numero_orden'  => $numero_orden,
        'subtotal'      => $subtotal,
        'impuesto'      => $impuesto,
        'total'         => $total,
        'cambio'        => $cambio,
        'metodo_pago'   => $metodo_pago,
        'fecha'         => date('d/m/Y H:i'),
        'cajero'        => $_SESSION['nombre'],
        'items'         => $items,
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
