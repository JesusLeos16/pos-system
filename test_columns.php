<?php
include 'db/conexion.php';
$stmt = $pdo->query('SHOW COLUMNS FROM ventas');
file_put_contents('test_out.json', json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)));
