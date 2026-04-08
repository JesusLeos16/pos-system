<?php
include 'db/conexion.php';
$stmtU = $pdo->query('SHOW COLUMNS FROM usuarios');
$users = $stmtU->fetchAll(PDO::FETCH_ASSOC);

$stmtC = $pdo->query('SHOW COLUMNS FROM categorias');
$cats = $stmtC->fetchAll(PDO::FETCH_ASSOC);

file_put_contents('test_schema.json', json_encode(['usuarios' => $users, 'categorias' => $cats], JSON_PRETTY_PRINT));
