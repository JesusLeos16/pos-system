<?php
include 'db/conexion.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
    $pdo->exec("DROP TABLE IF EXISTS venta_detalle");
    $pdo->exec("DROP TABLE IF EXISTS ventas");
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

    // Tabla de ventas (cabecera)
    $pdo->exec("
        CREATE TABLE ventas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            numero_orden VARCHAR(20) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            impuesto DECIMAL(10,2) NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') DEFAULT 'efectivo',
            monto_recibido DECIMAL(10,2) DEFAULT NULL,
            cambio DECIMAL(10,2) DEFAULT NULL,
            usuario_id INT NOT NULL,
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Tabla de detalle de ventas (productos por venta)
    $pdo->exec("
        CREATE TABLE venta_detalle (
            id INT AUTO_INCREMENT PRIMARY KEY,
            venta_id INT NOT NULL,
            producto_id INT NOT NULL,
            nombre_producto VARCHAR(255) NOT NULL,
            cantidad INT NOT NULL,
            precio_unitario DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
            FOREIGN KEY (producto_id) REFERENCES productos(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    echo "✅ Tablas recreadas exitosamente\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
