<?php
/**
 * Migración: Crear tablas de ventas
 * Ejecutar una sola vez desde el navegador: /pos-system/db/migration_ventas.php
 */
include 'conexion.php';

try {
    // Tabla de ventas (cabecera)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS ventas (
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
        CREATE TABLE IF NOT EXISTS venta_detalle (
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

    echo "<h2 style='color:green'>✅ Tablas creadas exitosamente</h2>";
    echo "<p>- ventas</p>";
    echo "<p>- venta_detalle</p>";
    echo "<br><a href='../Pages/tienda.php'>Ir a la Tienda</a>";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ Error: " . $e->getMessage() . "</h2>";
}
