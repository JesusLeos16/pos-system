<?php
include 'db/conexion.php';
$stmt = $pdo->query('SELECT COUNT(*) FROM ventas');
$count = $stmt->fetchColumn();
echo "Ventas: $count\n";

$stmt2 = $pdo->query('SHOW TABLES LIKE "venta_detalle"');
if ($stmt2->rowCount() > 0) {
    $stmt3 = $pdo->query('SELECT COUNT(*) FROM venta_detalle');
    echo "Venta Detalle: " . $stmt3->fetchColumn() . "\n";
} else {
    echo "NO existe venta_detalle\n";
}
